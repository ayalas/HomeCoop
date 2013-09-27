<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//when adding/deleting producers, all its products are also deleted/added in the coop order
class CoopOrderProducer extends CoopOrderSubRecordBase {
  const PROPERTY_DELIVERY_PERCENT = "DeliveryPercent";
  const PROPERTY_FIXED_DELIVERY = "FixedDelivery";
  const PROPERTY_DELIVERY_PERCENT_MIN = "MinDelivery";
  const PROPERTY_DELIVERY_PERCENT_MAX = "MaxDelivery";
  const PROPERTY_TOTAL_DELIVERY = "TotalDelivery";
  const PROPERTY_PRODUCER_TOTAL = "ProducerTotal";
  const PROPERTY_COOP_TOTAL = "CoopTotal";
  const PROPERTY_MAX_PRODUCER_ORDER = "MaxProducerOrder";
  const PROPERTY_PRODUCER_ID = "ProducerID";
  const PROPERTY_TOTAL_BURDEN = "TotalBurden";
  const PROPERTY_MAX_BURDEN = "MaxBurden";
  const PROPERTY_PRODUCER_COORDINATING_GROUP_ID = "ProducerCoordinatingGroupID";
  const PROPERTY_ORIGINAL_PRODUCER_ID = "OriginalProducerID";
  
  public function __construct()
  {
    $this->m_aDefaultData = array( 
                            self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_PRODUCER_ID => 0,
                            self::PROPERTY_PRODUCER_TOTAL => 0,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_DELIVERY_PERCENT => NULL,                    
                            self::PROPERTY_FIXED_DELIVERY => NULL,
                            self::PROPERTY_DELIVERY_PERCENT_MIN => NULL,
                            self::PROPERTY_DELIVERY_PERCENT_MAX => NULL,
                            self::PROPERTY_TOTAL_DELIVERY => 0,
                            self::PROPERTY_MAX_PRODUCER_ORDER => NULL,
                            Producer::PROPERTY_PRODUCER_NAME => NULL,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_IS_EXISTING_RECORD => FALSE,
                            self::PROPERTY_TOTAL_BURDEN => 0,
                            self::PROPERTY_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            self::PROPERTY_COOP_ORDER_STORAGE_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN => NULL,
                            self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID => 0
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
  
  public function __get( $name ) {
    switch ($name)
    {
       case self::PROPERTY_ORIGINAL_PRODUCER_ID:
        return $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID];
      default:
        return parent::__get($name);
    }
  }
  
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_TOTAL_DELIVERY:
      case Producer::PROPERTY_PRODUCER_NAME:
      case self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID:
        $trace = debug_backtrace();
        trigger_error(
          'Undefined property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
      default:
        parent::__set( $name, $value );
    }
  }
  
  public function LoadRecord()
  {
    if (!$this->LoadCoopOrderData())
      return FALSE;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }

    if ($this->m_aData[self::PROPERTY_PRODUCER_ID] > 0)
    {
      $sSQL =   " SELECT COP.mMaxProducerOrder, COP.mProducerTotal, IfNUll(COP.mCoopTotal,0) mCoopTotal, P.CoordinatingGroupID, " . 
                " COP.fDelivery, COP.mDelivery, COP.mMinDelivery, IfNull(COP.fBurden,0) fBurden, COP.fMaxBurden, " . 
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
                " , COP.mMaxDelivery, COP.mTotalDelivery FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
                " WHERE COP.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND COP.ProducerKeyID = " . $this->m_aData[self::PROPERTY_PRODUCER_ID];

      $this->RunSQL( $sSQL );

      $rec = $this->fetch();

      if (!is_array($rec) || count($rec) == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }
      
      if ($rec["CoordinatingGroupID"] != NULL)
        $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
      
      if (!$this->SetRecordGroupID(self::PERMISSION_EDIT, $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE) &&
          !$this->SetRecordGroupID(self::PERMISSION_VIEW, $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
      }
      
      $this->m_aData[self::PROPERTY_MAX_PRODUCER_ORDER] = $rec["mMaxProducerOrder"];
      $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $rec["mProducerTotal"];
      $this->m_aData[self::PROPERTY_COOP_TOTAL] = $rec["mCoopTotal"];
      
      $this->m_aData[self::PROPERTY_DELIVERY_PERCENT] = $rec["fDelivery"];
      $this->m_aData[self::PROPERTY_FIXED_DELIVERY] = $rec["mDelivery"];
      $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MIN] = $rec["mMinDelivery"];
      $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MAX] = $rec["mMaxDelivery"];
      $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = $rec["mTotalDelivery"];
      
      $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = Rounding::Round($rec["fBurden"], ROUND_SETTING_BURDEN);
      $this->m_aData[self::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
      
      $this->m_aData[Producer::PROPERTY_PRODUCER_NAME] = $rec["sProducer"];
      
      $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
    }
    
    $this->m_aOriginalData = $this->m_aData;
    
    return TRUE;
  }
  
  public function Add()
  {
    //general permission check
    if ( !$this->VerifyAction() )
      return FALSE;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            0, 
            TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = self::GetDeliveryTotal($this->m_aData[self::PROPERTY_DELIVERY_PERCENT], 
                $this->m_aData[self::PROPERTY_FIXED_DELIVERY], 
                $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MIN], 
                $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MAX], 
                $this->m_aData[self::PROPERTY_PRODUCER_TOTAL]);
    
    try
    {
      $this->BeginTransaction();

      //insert the record
      $sSQL =  " INSERT INTO T_CoopOrderProducer( CoopOrderKeyID, ProducerKeyID, mTotalDelivery " . 
              $this->ConcatColIfNotNull(self::PROPERTY_MAX_PRODUCER_ORDER, "mMaxProducerOrder") . 
              $this->ConcatColIfNotNull(self::PROPERTY_MAX_BURDEN, "fMaxBurden") .
              $this->ConcatColIfNotNull(self::PROPERTY_DELIVERY_PERCENT, "fDelivery") . 
              $this->ConcatColIfNotNull(self::PROPERTY_FIXED_DELIVERY, "mDelivery") . 
              $this->ConcatColIfNotNull(self::PROPERTY_DELIVERY_PERCENT_MIN, "mMinDelivery") . 
              $this->ConcatColIfNotNull(self::PROPERTY_DELIVERY_PERCENT_MAX, "mMaxDelivery") ;

      $sSQL .= ") VALUES ( " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .   ", "  . $this->m_aData[self::PROPERTY_PRODUCER_ID] 
              .   ", ? "  . 
              $this->ConcatValIfNotNull(self::PROPERTY_MAX_PRODUCER_ORDER) .
              $this->ConcatValIfNotNull(self::PROPERTY_MAX_BURDEN) .
              $this->ConcatValIfNotNull(self::PROPERTY_DELIVERY_PERCENT) .
              $this->ConcatValIfNotNull(self::PROPERTY_FIXED_DELIVERY) .
              $this->ConcatValIfNotNull(self::PROPERTY_DELIVERY_PERCENT_MIN) .
              $this->ConcatValIfNotNull(self::PROPERTY_DELIVERY_PERCENT_MAX) .
              " )";

      $this->RunSQLWithParams($sSQL, array( $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] ));

      $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
      $this->m_aOriginalData = $this->m_aData;

      $this->AddProducerProducts();
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }

    return TRUE;
  }
  
  public function Edit()
  {
    //general permission check
    if ( !$this->VerifyAction())
      return FALSE;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aOriginalData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], 
            FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //save original values
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL];
    $this->m_aData[self::PROPERTY_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
    
    $this->m_aData[self::PROPERTY_PRODUCER_ID] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID];
    $this->m_aData[Producer::PROPERTY_PRODUCER_NAME] = $this->m_aOriginalData[Producer::PROPERTY_PRODUCER_NAME];
 
    $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
    
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = self::GetDeliveryTotal($this->m_aData[self::PROPERTY_DELIVERY_PERCENT], 
                $this->m_aData[self::PROPERTY_FIXED_DELIVERY], 
                $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MIN], 
                $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MAX], 
                $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL]);
    
    try
    {

      $this->BeginTransaction();

      $sSQL =   " UPDATE T_CoopOrderProducer " .
                " SET mTotalDelivery =  ?, " . 
                " mMaxProducerOrder = ? ," .
                " fMaxBurden = ? ," .
                " fDelivery = ? ," .
                " mDelivery = ? ," .
                " mMinDelivery = ? ," .
                " mMaxDelivery = ? " .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND ProducerKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID] . ";";

      $this->RunSQLWithParams( $sSQL, array(  $this->m_aData[self::PROPERTY_TOTAL_DELIVERY],
                                              $this->m_aData[self::PROPERTY_MAX_PRODUCER_ORDER],
                                              $this->m_aData[self::PROPERTY_MAX_BURDEN] ,
                                              $this->m_aData[self::PROPERTY_DELIVERY_PERCENT],
                                              $this->m_aData[self::PROPERTY_FIXED_DELIVERY],
                                              $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MIN],
                                              $this->m_aData[self::PROPERTY_DELIVERY_PERCENT_MAX]
          ) );
      
      //if delivery was changed, recalculate totals for coop order
      if ($this->m_aData[self::PROPERTY_TOTAL_DELIVERY] != $this->m_aOriginalData[self::PROPERTY_TOTAL_DELIVERY])
      {
        $oCalc = new CoopOrderCalculate($this->m_aData[self::PROPERTY_COOP_ORDER_ID]);

        $oCalc->CalculateCoopOrder();
      }

      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    
    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  public function Delete()
  {
    global $g_oError;

    //general permission check
    if ( !$this->VerifyAction())
      return FALSE;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aOriginalData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], 
            FALSE) || $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] > 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL] > 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      $g_oError->AddError('There are already orders against this producer in this cooperative orders. Producer cannot be deleted in such a case. Specific products can be deleted though.');
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();
    
      $this->DeleteProducerProducts();

      $sSQL =   " DELETE FROM T_CoopOrderProducer " .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND ProducerKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID] . ";";

      $this->RunSQL($sSQL);
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    //preserve coop order data
    $this->PreserveCoopOrderData();
    
    return TRUE;
  }
  
  public function Validate()
  {
    global $g_oError;
    
    $bValid = TRUE;
    
    if ($this->m_aData[self::PROPERTY_PRODUCER_ID] <= 0)
    {
       $g_oError->AddError( sprintf('Must select %s', 'Producer'));
       $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_PRODUCER_ORDER] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_PRODUCER_ORDER]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Max. Producer Order'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_PRODUCER_ORDER] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Max. Producer Order'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_BURDEN] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_BURDEN]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Delivery Capacity'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_BURDEN] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Delivery Capacity'));
        $bValid = FALSE;
      }
    }
    
    return $bValid;
  }  
  
  protected function AddProducerProducts()
  {
    //add producer products
    $sSQL =  " INSERT INTO T_CoopOrderProduct( CoopOrderKeyID, ProductKeyID, mProducerPrice, mCoopPrice, fMaxUserOrder, fBurden) " .
             " SELECT " .  $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . 
             " , PRD.ProductKeyID, PRD.mProducerPrice, PRD.mCoopPrice, PRD.fMaxUserOrder, PRD.fBurden " .
             " FROM T_Product PRD WHERE PRD.ProducerKeyID = " . $this->m_aData[self::PROPERTY_PRODUCER_ID] . 
             " AND PRD.bDisabled = 0;";
    
    $this->RunSQL($sSQL);
    
    //add default storage areas for the products
    $sSQL = " INSERT INTO T_CoopOrderProductStorage (CoopOrderKeyID, ProductKeyID, PickupLocationKeyID, StorageAreaKeyID) " .
        " SELECT COSA.CoopOrderKeyID , PRD.ProductKeyID, PLSA.PickupLocationKeyID , PLSA.StorageAreaKeyID " .
        " FROM T_Product PRD CROSS JOIN T_CoopOrderStorageArea COSA " .
        " INNER JOIN T_PickupLocationStorageArea PLSA ON PLSA.StorageAreaKeyID = COSA.StorageAreaKeyID " .
        " WHERE COSA.CoopOrderKeyID = ". $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . 
        " AND PRD.ProducerKeyID = " . $this->m_aData[self::PROPERTY_PRODUCER_ID] . 
        " AND PRD.bDisabled = 0 AND PLSA.bDefault = 1;";
    
    $this->RunSQL($sSQL);
  }
  
  protected function DeleteProducerProducts()
  {
    //delete producer products
    $sSQL =  " DELETE COPRD FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON COPRD.ProductKeyID = PRD.ProductKeyID " .
             " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . " AND PRD.ProducerKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCER_ID]  . ";";
    
    $this->RunSQL($sSQL);
  }
  
  public static function GetDeliveryTotal($fDelivery, $mDelivery, $mMinDelivery, $mMaxDelivery, $mTotal)
  {
    if ($mDelivery != NULL && $mDelivery != 0)
      return $mDelivery;
    if ($fDelivery == NULL || $fDelivery == 0 || $mTotal == NULL || $mTotal == 0)
      return $mMinDelivery;
    
    $mDelivery = ($fDelivery/100) * $mTotal;
    
    if ($mMaxDelivery != NULL && $mMaxDelivery != 0 && $mDelivery >= $mMaxDelivery)
      return $mMaxDelivery;
    
    if ($mMinDelivery != NULL && $mMinDelivery != 0 && $mDelivery <= $mMinDelivery)
      return $mMinDelivery;

    return Rounding::Round($mDelivery, ROUND_SETTING_DELIVERY_TOTAL);
  }
  
}

?>
