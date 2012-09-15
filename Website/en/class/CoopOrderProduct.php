<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class CoopOrderProduct extends CoopOrderSubRecordBase {
  
  const POST_ACTION_SELECT_PRODUCER = 11;
  const POST_ACTION_SELECT_PRODUCT = 12;
  
  const PERMISSION_COOP_ORDER_PRODUCT_EDIT = 11;
  const PERMISSION_COOP_ORDER_PRODUCT_VIEW = 12;
  
  
  const JOIN_STATUS_NONE = 0;
  const JOIN_STATUS_JOINED = 1;
  const JOIN_STATUS_JOINED_BY = 2;
  
  const PROPERTY_PRODUCT_ID = "ProductID";
  const PROPERTY_PRODUCER_PRICE = "ProducerPrice";
  const PROPERTY_COOP_PRICE = "CoopPrice";
  const PROPERTY_MAX_USER_ORDER = "MaxUserOrder";
  const PROPERTY_MAX_COOP_ORDER = "MaxCoopOrder";
  const PROPERTY_JOINED_STATUS = "JoinedStatus";
  const PROPERTY_BURDEN = "Burden";
  const PROPERTY_ORIGINAL_PRODUCT_ID = "OriginalProductID";
  const PROPERTY_UNIT_ABBREV = "UnitAbbrev";
  const PROPERTY_ITEM_UNIT_ABBREV = "ItemUnitAbbrev";
  const PROPERTY_TOTAL_COOP_ORDER = "TotalCoopOrder";
  const PROPERTY_PRODUCER_TOTAL = "ProducerTotal";
  const PROPERTY_COOP_TOTAL = "ProductCoopTotal";
  const PROPERTY_PRODUCER_COORDINATING_GROUP_ID = "ProducerCoordinatingGroupID";
  
  public function __construct()
  {
    $this->m_aDefaultData = array( 
                            self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_PRODUCT_ID => 0,
                            Producer::PROPERTY_PRODUCER_ID => 0,
                            self::PROPERTY_PRODUCER_PRICE => 0,
                            self::PROPERTY_COOP_PRICE => NULL,                    
                            self::PROPERTY_MAX_USER_ORDER => NULL,
                            self::PROPERTY_MAX_COOP_ORDER => NULL,
                            self::PROPERTY_BURDEN => NULL,
                            Producer::PROPERTY_PRODUCER_NAME => NULL,
                            Product::PROPERTY_PRODUCT_NAME => NULL,          
                            Product::PROPERTY_QUANTITY => Product::DEFAULT_QUANTITY,                    
                            Product::PROPERTY_ITEMS_IN_PACKAGE => Product::DEFAULT_ITEMS_IN_PACKAGE,
                            Product::PROPERTY_ITEM_QUANTITY => NULL,
                            Product::PROPERTY_PACKAGE_SIZE => NULL,
                            Product::PROPERTY_UNIT_INTERVAL => Product::DEFAULT_UNIT_INTERVAL,
                            self::PROPERTY_ITEM_UNIT_ABBREV => NULL,
                            self::PROPERTY_UNIT_ABBREV => NULL,
                            self::PROPERTY_TOTAL_COOP_ORDER => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_IS_EXISTING_RECORD => FALSE,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            self::PROPERTY_PRODUCER_TOTAL => 0,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_JOINED_STATUS => self::JOIN_STATUS_NONE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID => 0,
                            CoopOrder::PROPERTY_COOP_FEE => 0,
                            CoopOrder::PROPERTY_SMALL_ORDER => 0,
                            CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE => 0,
                            CoopOrder::PROPERTY_COOP_FEE_PERCENT => 0
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  } 
  
  public function __get( $name ) {
    switch ($name)
    {
      case self::PROPERTY_ORIGINAL_PRODUCT_ID:
        return $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID];
      default:
        return parent::__get($name);
    }
  }
  
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case Product::PROPERTY_PRODUCT_NAME:
      case Producer::PROPERTY_PRODUCER_NAME:
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
  
  //used in member's accessible product page
  public function LoadRecordForViewOnly()
  {
    global $g_oMemberSession;
    
    //this is the only permission check needed here (has any permission)
    if (!$g_oMemberSession->IsLoggedIn)
    {
        $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
        return FALSE; 
    }
    
    $sSQL =   " SELECT COPRD.ProductKeyID, PRD.fQuantity, PRD.nItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, P.ProducerKeyID , " . 
              " PRD.sImage1FileName, PRD.sImage2FileName,PRD.UnitKeyID, " . 
              " COPRD.mProducerPrice, COPRD.mCoopPrice, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_SPECIFICATION, 'sSpec') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNITS, 'sUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNITS, 'sItemUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .              
          " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
          " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_SPECIFICATION, Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ITEM_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
          " AND COPRD.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID];

      $this->RunSQL( $sSQL );

      return $this->fetch();
  }
  
  public function LoadRecord()
  {    
    if (!$this->LoadCoopOrderData())
      return FALSE;  
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }

    if ($this->m_aData[self::PROPERTY_PRODUCT_ID] > 0)
    {
      $sSQL =   " SELECT COPRD.ProductKeyID, PRD.fQuantity, PRD.nItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, P.ProducerKeyID , " . 
              " COPRD.fMaxCoopOrder, IfNull(COPRD.fBurden,0) fBurden, COPRD.nJoinedStatus, IfNUll(COPRD.mProducerTotal,0) mProducerTotal, " . 
              " IfNUll(COPRD.mCoopTotal,0) mCoopTotal, P.CoordinatingGroupID, " . 
              " IfNull(COPRD.fTotalCoopOrder,0) fTotalCoopOrder, COPRD.mProducerPrice, COPRD.mCoopPrice, COPRD.fMaxUserOrder, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_SPECIFICATION, 'sSpec') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNITS, 'sUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNITS, 'sItemUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .              
          " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
          " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_SPECIFICATION, Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ITEM_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
          " AND COPRD.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID];

      $this->RunSQL( $sSQL );

      $rec = $this->fetch();

      if (!is_array($rec) || count($rec) == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }
      
      if ($rec["CoordinatingGroupID"] != NULL)
        $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
      
       if (!$this->SetRecordGroupID(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE) &&
          !$this->SetRecordGroupID(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW, $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
      }

      $this->m_aData[self::PROPERTY_PRODUCT_ID] = $rec["ProductKeyID"];
      $this->m_aData[Producer::PROPERTY_PRODUCER_ID] = $rec["ProducerKeyID"];
      $this->m_aData[self::PROPERTY_PRODUCER_PRICE] = $rec["mProducerPrice"];
      $this->m_aData[self::PROPERTY_COOP_PRICE] = $rec["mCoopPrice"];
      $this->m_aData[self::PROPERTY_MAX_USER_ORDER] = $rec["fMaxUserOrder"];
      $this->m_aData[self::PROPERTY_MAX_COOP_ORDER] = $rec["fMaxCoopOrder"];
      $this->m_aData[self::PROPERTY_BURDEN] = Rounding::Round($rec["fBurden"], ROUND_SETTING_BURDEN);
      $this->m_aData[Producer::PROPERTY_PRODUCER_NAME] = $rec["sProducer"];
      $this->m_aData[Product::PROPERTY_PRODUCT_NAME] = $rec["sProduct"];
      $this->m_aData[Product::PROPERTY_QUANTITY] = $rec["fQuantity"];
      $this->m_aData[Product::PROPERTY_ITEMS_IN_PACKAGE] = $rec["nItems"];
      $this->m_aData[Product::PROPERTY_ITEM_QUANTITY] = $rec["fItemQuantity"];
      $this->m_aData[Product::PROPERTY_PACKAGE_SIZE] = $rec["fPackageSize"];
      $this->m_aData[Product::PROPERTY_UNIT_INTERVAL] = $rec["fUnitInterval"];
      $this->m_aData[self::PROPERTY_ITEM_UNIT_ABBREV] = $rec["sItemUnitAbbrev"];
      $this->m_aData[self::PROPERTY_UNIT_ABBREV] = $rec["sUnitAbbrev"];
      $this->m_aData[self::PROPERTY_TOTAL_COOP_ORDER] = $rec["fTotalCoopOrder"];
      $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $rec["mProducerTotal"];
      $this->m_aData[self::PROPERTY_COOP_TOTAL] = $rec["mCoopTotal"];
      $this->m_aData[self::PROPERTY_JOINED_STATUS] = $rec["nJoinedStatus"];
      
      $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
    }
    
    $this->m_aOriginalData = $this->m_aData;
    
    return TRUE;
  }
  
  public function CheckPermission()
  {
    if ($this->HasPermission(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT))
         return TRUE;
    
    return $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
  
  public function Add()
  {
    //general permission check
    if ( !$this->VerifyAction() )
      return FALSE;
       
    if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    

    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }

    //insert the record
    $sSQL =  " INSERT INTO T_CoopOrderProduct( CoopOrderKeyID, ProductKeyID, mProducerPrice, mCoopPrice " . 
            $this->ConcatColIfNotNull(self::PROPERTY_MAX_USER_ORDER, "fMaxUserOrder") . 
            $this->ConcatColIfNotNull(self::PROPERTY_MAX_COOP_ORDER, "fMaxCoopOrder") . 
            $this->ConcatColIfNotNull(self::PROPERTY_BURDEN, "fBurden");

    $sSQL .= ") VALUES ( " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .   ", "  . $this->m_aData[self::PROPERTY_PRODUCT_ID] 
            .   ", "  . $this->m_aData[self::PROPERTY_PRODUCER_PRICE]  .   ", "  . $this->m_aData[self::PROPERTY_COOP_PRICE]  .
            $this->ConcatValIfNotNull(self::PROPERTY_MAX_USER_ORDER) .
            $this->ConcatValIfNotNull(self::PROPERTY_MAX_COOP_ORDER) .
            $this->ConcatValIfNotNull(self::PROPERTY_BURDEN) .
            " )";

    $this->RunSQL($sSQL);
    
    $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
    $this->m_aOriginalData = $this->m_aData;
    
    return TRUE;
  }
  
  public function Edit()
  {
    //general permission check
    if ( !$this->VerifyAction())
      return FALSE;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
          $this->m_aOriginalData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE))
    {
      $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
        
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $sSQL =   " UPDATE T_CoopOrderProduct " .
              " SET mProducerPrice =  ?, " . 
              " mCoopPrice = ? ," .
              " fMaxUserOrder = ? ," .
              " fMaxCoopOrder = ? ," .
              " fBurden = ? " .
              " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ";";

    $this->RunSQLWithParams( $sSQL, array(  $this->m_aData[self::PROPERTY_PRODUCER_PRICE],
                                            $this->m_aData[self::PROPERTY_COOP_PRICE],
                                            $this->m_aData[self::PROPERTY_MAX_USER_ORDER],
                                            $this->m_aData[self::PROPERTY_MAX_COOP_ORDER],
                                            $this->m_aData[self::PROPERTY_BURDEN]
        ) );

    //preserve data after postback
    $this->m_aData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID];    
    $this->m_aData[Producer::PROPERTY_PRODUCER_ID] = $this->m_aOriginalData[Producer::PROPERTY_PRODUCER_ID];
    $this->m_aData[self::PROPERTY_PRODUCT_ID] = $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID];
    $this->m_aData[Producer::PROPERTY_PRODUCER_NAME] = $this->m_aOriginalData[Producer::PROPERTY_PRODUCER_NAME];
    $this->m_aData[Product::PROPERTY_PRODUCT_NAME] = $this->m_aOriginalData[Product::PROPERTY_PRODUCT_NAME];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL];
    $this->m_aData[self::PROPERTY_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
    
    $this->m_aData[self::PROPERTY_TOTAL_COOP_ORDER] = $this->m_aOriginalData[self::PROPERTY_TOTAL_COOP_ORDER];
    $this->m_aData[self::PROPERTY_JOINED_STATUS] = $this->m_aOriginalData[self::PROPERTY_JOINED_STATUS];
 
    $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  public function Delete()
  {
    global $g_oError;

    //general permission check
    if ( !$this->VerifyAction())
      return FALSE;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
          $this->m_aOriginalData[self::PROPERTY_PRODUCER_COORDINATING_GROUP_ID], FALSE))
    {
      $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();
      
      $oCalc = new CoopOrderCalculate($this->m_aData[self::PROPERTY_COOP_ORDER_ID]);
      
      //get order list
      $sSQL =   " SELECT DISTINCT O.OrderID " .
                " FROM T_Order O INNER JOIN T_OrderItem OI ON O.OrderID = OI.OrderID " .
                " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND OI.ProductKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID] . ";";
      
      $this->RunSQL($sSQL);
      
      $arrOrders = $this->fetchAllOneColumn();
      
      $oCalc->OrdersListToCalculate = implode(",", $arrOrders);
      
      
      //delete the order items of this product
      $sSQL =   " DELETE OI " .
                " FROM T_Order O INNER JOIN T_OrderItem OI ON O.OrderID = OI.OrderID " .
                " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND OI.ProductKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID] . ";";

      $this->RunSQL($sSQL);

      $sSQL =   " DELETE FROM T_CoopOrderProduct " .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND ProductKeyID = " . $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID] . ";";

      $this->RunSQL($sSQL);
      
      //recalculate coop order and orders data
      
      $oCalc->ProductsListToCalculate = $this->m_aOriginalData[self::PROPERTY_PRODUCT_ID]; 
      $oCalc->CoopFee = $this->CoopFee;
      $oCalc->SmallOrder = $this->SmallOrder;
      $oCalc->SmallOrderCoopFee = $this->SmallOrderCoopFee;
      $oCalc->CoopFeePercent = $this->CoopFeePercent; 
    
      $oCalc->Run();

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
  
  protected function Validate()
  {
    global $g_oError;
    
    $bValid = TRUE;  
    
    if ($this->m_aData[self::PROPERTY_PRODUCT_ID] == 0)
    {
      $g_oError->AddError( sprintf('%s is required.', 'Product'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_PRODUCER_PRICE] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_PRODUCER_PRICE]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Producer Price'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_PRODUCER_PRICE] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Producer Price'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_COOP_PRICE] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_COOP_PRICE]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Coop. Price'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_COOP_PRICE] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Coop. Price'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_USER_ORDER] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_USER_ORDER]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Max. Member Order'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_USER_ORDER] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Max. Member Order'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_COOP_ORDER] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_COOP_ORDER]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Max. Coop Order'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_COOP_ORDER] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Max. Coop Order'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_BURDEN] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_BURDEN]))
      {
        $g_oError->AddError( sprintf('%s must be numeric.', 'Burden'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_BURDEN] < 0)
      {
        $g_oError->AddError( sprintf('%s cannot have a negative value', 'Burden'));
        $bValid = FALSE;
      }
    }
    
    //block editing prices after orders were filled- not supported for now (requires calculations of entire order, including items)
    if ($this->m_aOriginalData[self::PROPERTY_TOTAL_COOP_ORDER] > 0 )
    {
      if ($this->m_aData[self::PROPERTY_COOP_PRICE] != $this->m_aOriginalData[self::PROPERTY_COOP_PRICE] 
        || $this->m_aData[self::PROPERTY_PRODUCER_PRICE] != $this->m_aOriginalData[self::PROPERTY_PRODUCER_PRICE])
        $g_oError->AddError( 'There are already orders against this product in this cooperative orders. Prices cannot be changed in such a case. All orders must be removed from the product first.');
        $bValid = FALSE;
    }
    
    return $bValid;
  }  

          
}

?>
