<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class CoopOrderPickupLocation extends CoopOrderSubRecordBase {
  
   const PERMISSION_SUMS = 10;
   
   const POST_ACTION_SELECT_LOCATION = 11;
  
   const PROPERTY_PICKUP_LOCATION_ID = "PickupLocationID";
   const PROPERTY_PICKUP_LOCATION_NAME = "PickupLocationName";
   const PROPERTY_TOTAL_BURDEN = "TotalBurden";
   const PROPERTY_MAX_BURDEN = "MaxBurden";
   const PROPERTY_MAX_COOP_TOTAL = "MaxCoopTotal";
   const PROPERTY_COOP_TOTAL = "CoopTotal";
   const PROPERTY_FOR_MEMBER = "ForMember";
   const PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID = "PickupLocationCoordinatingGroupID";
   
  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_PICKUP_LOCATION_ID => 0,
                            self::PROPERTY_TOTAL_BURDEN => 0,
                            self::PROPERTY_MAX_BURDEN => NULL,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_IS_EXISTING_RECORD => FALSE,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_FOR_MEMBER => FALSE,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_PICKUP_LOCATION_NAME => NULL
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
   
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_TOTAL_BURDEN:
      case self::PROPERTY_COOP_TOTAL:
      case self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID:
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
  
  //runs also for new page, without loading any record
  public function LoadRecord()
  {
    if ($this->m_aData[self::PROPERTY_FOR_MEMBER]) //member
    {
      if ( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] <=0 || $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0 )
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
      }
      if (!$this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
      }
    }
    else //coordinator
    {
      if (!$this->LoadCoopOrderData())
        return FALSE;
      
      $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
      $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      if (!$bEdit && !$bView)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
      }
      
      //check sums permission
      $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS,
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
      
      //copy any coord existing permission as the page access permisison
      if ($bView)
        $this->CopyPermission (self::PERMISSION_VIEW, self::PERMISSION_PAGE_ACCESS);
      else
        $this->CopyPermission (self::PERMISSION_EDIT, self::PERMISSION_PAGE_ACCESS);
      
    }

    //should load a record?
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] > 0)
    {
      $sSQL =   " SELECT COPL.fMaxBurden, IfNull(COPL.fBurden,0) fBurden, COPL.mMaxCoopTotal ,  " . 
                " IfNull(COPL.mCoopTotal,0) mCoopTotal, PL.CoordinatingGroupID, " . 
              $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
                " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_PickupLocation PL " . 
                " ON COPL.PickupLocationKeyID = PL.PickupLocationKeyID " .
              $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
                " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND COPL.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID];

      $this->RunSQL( $sSQL );

      $rec = $this->fetch();

      if (!is_array($rec) || count($rec) == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }
      
      if (!$this->m_aData[self::PROPERTY_FOR_MEMBER])
      {
        if (!$this->SetRecordGroupID(self::PERMISSION_EDIT, $rec["CoordinatingGroupID"], FALSE) &&
            !$this->SetRecordGroupID(self::PERMISSION_VIEW, $rec["CoordinatingGroupID"], FALSE)
         )
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
        }
      }
      
      $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
      $this->m_aData[self::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
      $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = Rounding::Round($rec["fBurden"], ROUND_SETTING_BURDEN);
      $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] = $rec["mMaxCoopTotal"];
      $this->m_aData[self::PROPERTY_COOP_TOTAL] = $rec["mCoopTotal"];
      $this->m_aData[self::PROPERTY_PICKUP_LOCATION_NAME] = $rec["sPickupLocation"];
      
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
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }

    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    //insert the record
    $sSQL =  " INSERT INTO T_CoopOrderPickupLocation( CoopOrderKeyID, PickupLocationKeyID " . 
            $this->ConcatColIfNotNull(self::PROPERTY_MAX_BURDEN, "fMaxBurden") .
            $this->ConcatColIfNotNull(self::PROPERTY_MAX_COOP_TOTAL, "mMaxCoopTotal");

    $sSQL .= ") VALUES ( " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .   ", "  . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .  
            $this->ConcatValIfNotNull(self::PROPERTY_MAX_BURDEN) . 
            $this->ConcatValIfNotNull(self::PROPERTY_MAX_COOP_TOTAL) . " )";

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
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID], 
            FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0 ||
         $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
        
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    try
    {
    
      $this->BeginTransaction();

      $sSQL =   " UPDATE T_CoopOrderPickupLocation " .
                " SET fMaxBurden =  ?, mMaxCoopTotal = ?, " . 
                " PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";

      $this->RunSQLWithParams( $sSQL, array( $this->m_aData[self::PROPERTY_MAX_BURDEN], $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] ) );
      
      //update orders pickup location, if changed
      if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] != $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID])
      {
        $sSQL = " UPDATE T_Order " . 
                " SET PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";
        $this->RunSQL($sSQL);
      }
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
 
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
    
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID], 
            FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $sSQL =   " DELETE FROM T_CoopOrderPickupLocation " .
              " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";
    
    $this->RunSQL($sSQL);
    
    //preserve coop order data
    $this->PreserveCoopOrderData();
    
    return TRUE;
  }
  
  public function Validate()
  {
    global $g_oError;
    
    $bValid = TRUE;
    
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0)
    {
      $g_oError->AddError(sprintf('<!$FIELD_SELECT_REQUIRED$!>', '<!$FIELD_PICKUP_LOCATION_NAME$!>'));
      $bValid = FALSE;
    }
        
    if ($this->m_aData[self::PROPERTY_MAX_BURDEN] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_BURDEN]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_MAX_BURDEN$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_BURDEN] < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_COOP_ORDER_MAX_BURDEN$!>'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_MAX_COOP_TOTAL$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_COOP_ORDER_MAX_COOP_TOTAL$!>'));
        $bValid = FALSE;
      }
    }
    
    return $bValid;
  } 
  
  public function PreserveUnsavedData()
  {
    $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD]  = $this->m_aOriginalData[self::PROPERTY_IS_EXISTING_RECORD];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
    $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_MAX_COOP_TOTAL];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID];
  }
  
  public function AddCoordinatorPermissionBridges()
  {
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
          Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);

    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }

    //check sums permission
    $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS,
          Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    //copy any coord existing permission as the page access permisison
    if ($bView)
      $this->CopyPermission (self::PERMISSION_VIEW, self::PERMISSION_PAGE_ACCESS);
    else
      $this->CopyPermission (self::PERMISSION_EDIT, self::PERMISSION_PAGE_ACCESS);
  }
}

?>
