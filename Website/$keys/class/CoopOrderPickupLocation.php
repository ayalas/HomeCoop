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
  const PROPERTY_STORAGE_AREAS = "StorageAreas";
  
  const PROPERTY_MAX_STOARGE_BURDEN = "MaxStorageBurden";
  const PROPERTY_STOARGE_BURDEN = "StorageBurden";

  const CTL_STORAGE_AREA_DISABLED = 'selStorageAreaDisabled_';
  const CTL_STORAGE_AREA_MAX_BURDEN = 'txtMaxBurden_';

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
                            self::PROPERTY_PICKUP_LOCATION_NAME => NULL,
                            self::PROPERTY_STORAGE_AREAS => array(),
                            self::PROPERTY_MAX_STOARGE_BURDEN => NULL,
                            self::PROPERTY_STOARGE_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_STORAGE_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN => NULL,
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
   
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_STORAGE_AREAS:
        $this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS] = $value;
        $this->m_aData[self::PROPERTY_STORAGE_AREAS] = $value;
        break;
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
                " IfNull(COPL.mCoopTotal,0) mCoopTotal, PL.CoordinatingGroupID, COPL.fMaxStorageBurden, COPL.fStorageBurden, " . 
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
      $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] = $rec["fMaxStorageBurden"];
      $this->m_aData[self::PROPERTY_STOARGE_BURDEN] = $rec["fStorageBurden"];
      
      $this->m_aData[self::PROPERTY_PICKUP_LOCATION_NAME] = $rec["sPickupLocation"];
      
      $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD] = TRUE;
      
      //load storage areas
      $this->LoadStorageAreas();
    }
    
    $this->m_aOriginalData = $this->m_aData;
    
    return TRUE;
  }
  
  public function LoadStorageAreas()
  {
    if (!$this->AddCoordinatorPermissionBridges())
      return;
    
    $this->m_aData[self::PROPERTY_STORAGE_AREAS] = array();
    
    //load storage areas
    $sSQL =     " SELECT PLSA.StorageAreaKeyID, COSA.StorageAreaKeyID ExistingID, " . 
                " COSA.fMaxBurden, PLSA.fMaxBurden fMaxBurdenDefault, COSA.fBurden, " . 
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_STORAGE_AREAS, 'sStorageArea') .
                " FROM T_PickupLocationStorageArea PLSA LEFT JOIN T_CoopOrderStorageArea COSA " . 
                " ON COSA.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PLSA.StorageAreaKeyID = COSA.StorageAreaKeyID " .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_STORAGE_AREAS) .
                " WHERE PLSA.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .
                " AND (PLSA.bDisabled = 0 OR COSA.StorageAreaKeyID IS NOT NULL) " .
                " ORDER BY PLSA.StorageAreaKeyID;";

    $this->RunSQL( $sSQL );
    
    $SAID = NULL;

    //key records by id
    while($rec = $this->fetch())
    {
      $SAID = $rec['StorageAreaKeyID'];
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['StorageAreaKeyID'] = $SAID;
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['fBurden'] = $rec['fBurden'];
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['sStorageArea'] = $rec['sStorageArea'];
      
      if ($rec['ExistingID'] == NULL)
      {
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['bDisabled'] = TRUE;
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['fMaxBurden'] = $rec['fMaxBurdenDefault'];
      }
      else
      {
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['bDisabled'] = FALSE;
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$SAID]['fMaxBurden'] = $rec['fMaxBurden'];
      }
    }
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
    
    $this->CollectStoragePostData();

    if (!$this->Validate(TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();
      
      //must be first, because gets fMaxStorageBurden
      $this->SaveStorageAreas(TRUE);
    
      //insert the record
      $sSQL =  " INSERT INTO T_CoopOrderPickupLocation( CoopOrderKeyID, PickupLocationKeyID " . 
              $this->ConcatColIfNotNull(self::PROPERTY_MAX_STOARGE_BURDEN, "fMaxStorageBurden") .
              $this->ConcatColIfNotNull(self::PROPERTY_MAX_BURDEN, "fMaxBurden") .
              $this->ConcatColIfNotNull(self::PROPERTY_MAX_COOP_TOTAL, "mMaxCoopTotal");

      $sSQL .= ") VALUES ( " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .   
                           ", "  . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .
              $this->ConcatValIfNotNull(self::PROPERTY_MAX_STOARGE_BURDEN) .
              $this->ConcatValIfNotNull(self::PROPERTY_MAX_BURDEN) . 
              $this->ConcatValIfNotNull(self::PROPERTY_MAX_COOP_TOTAL) . " )";

      $this->RunSQL($sSQL);
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    } 
   
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
    
    $this->CollectStoragePostData();
        
    if (!$this->Validate(FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    try
    {
    
      $this->BeginTransaction();
      
      //must be first, because gets fMaxStorageBurden
      $this->SaveStorageAreas(FALSE);

      $sSQL =   " UPDATE T_CoopOrderPickupLocation " .
                " SET fMaxBurden =  :MaxBurden, mMaxCoopTotal = :MaxCoopTotal, fMaxStorageBurden = :MaxStorageBurden, " .
                " PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";

      $this->RunSQLWithParams( $sSQL, array( 
              'MaxBurden' => $this->m_aData[self::PROPERTY_MAX_BURDEN], 
              'MaxCoopTotal' => $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL],
              'MaxStorageBurden' => $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN],
            ) 
          );
      
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
    
    try {
      $this->BeginTransaction();

      $sSQL =   " DELETE FROM T_CoopOrderPickupLocation " .
                " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";

      $this->RunSQL($sSQL);
      
      //delete coop order product data related to this pickup location
      $sSQL = " DELETE COPS FROM T_CoopOrderProductStorage COPS WHERE COPS.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND COPS.PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";
      
      $this->RunSQL($sSQL);

      //delete data for pickup location's coop order storage areas
      $sSQL =   " DELETE COSA FROM T_CoopOrderStorageArea COSA INNER JOIN T_PickupLocationStorageArea PLSA " .
                " ON COSA.StorageAreaKeyID = PLSA.StorageAreaKeyID " .
                " WHERE COSA.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
                " AND PLSA.PickupLocationKeyID = " . $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] . ";";


      $this->RunSQL($sSQL);
      
      $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] = 0;
      
      $this->CalculateMaxStorageBurden();
      
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
  
  public function Validate($bNewRecord)
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
    
    if (!$this->ValidateStorageAreas($bNewRecord))
      $bValid = FALSE;
    
    return $bValid;
  } 
  
  public function PreserveUnsavedData()
  {
    $this->m_aData[self::PROPERTY_IS_EXISTING_RECORD]  = $this->m_aOriginalData[self::PROPERTY_IS_EXISTING_RECORD];
    $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN]  = $this->m_aOriginalData[self::PROPERTY_MAX_STOARGE_BURDEN];
    $this->m_aData[self::PROPERTY_STOARGE_BURDEN]  = $this->m_aOriginalData[self::PROPERTY_STOARGE_BURDEN];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
    $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_MAX_COOP_TOTAL];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_COORDINATING_GROUP_ID];
    $this->m_aData[self::PROPERTY_STORAGE_AREAS] = $this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS];
  }
  
  protected function AddCoordinatorPermissionBridges()
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
    
    return TRUE;
  }
  
  //process post data and return an unkeyed array with each element in this format
  // StorageAreaKeyID => id
  // sStorageArea => from original loaded record
  // Disabled => TRUE/FALSE, entered value
  // fMaxBurden => entered value
  protected function CollectStoragePostData()
  {
    global $_POST;
    $nStorageAreaKeyID = 0;
    $nDisabledPrefixLen = strlen(self::CTL_STORAGE_AREA_DISABLED);
    $nMaxBurdenPrefixLen = strlen(self::CTL_STORAGE_AREA_MAX_BURDEN);
    
    $this->m_aData[self::PROPERTY_STORAGE_AREAS] = array();

    foreach($_POST as $key => $value)
    {
      //if found in position 0
      if (strpos($key, self::CTL_STORAGE_AREA_DISABLED) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nDisabledPrefixLen);

        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'] = (intval($value) == 1);
      }
      elseif (strpos($key, self::CTL_STORAGE_AREA_MAX_BURDEN) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nMaxBurdenPrefixLen);

        if (!empty($value)) //allow null, do not allow 0
          $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['fMaxBurden'] = 0 + $value;
      }
    }
  }
  
  protected function InitStoragePostElement($key, $nPrefixLen)
  {
    $nStorageAreaKeyID = 0 + substr($key, $nPrefixLen );

    return $this->InitStoragePostElementFromId($key, $nStorageAreaKeyID);
  }

  protected function InitStoragePostElementFromId($key, $nStorageAreaKeyID)
  {
    if ($nStorageAreaKeyID == 0)
      throw new Exception('Error in CoopOrderPickupLocation.InitStoragePostElementFromId: StorageAreaKeyID 0 for post key ' . $key);

    if (!array_key_exists($nStorageAreaKeyID, $this->m_aData[self::PROPERTY_STORAGE_AREAS]))
    {
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID] = array(
        'StorageAreaKeyID' => $nStorageAreaKeyID,
        'sStorageArea' => $this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['sStorageArea'],
        'fBurden' =>  $this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['fBurden'],
        'fMaxBurden' => NULL,
        'bDisabled' => FALSE,
      );
    }

    return $nStorageAreaKeyID;
  }
  
  protected function ValidateStorageAreas($bNewRecord)
  {
    global $g_oError;
    $bValid = TRUE;
    $nCount = 0;
    
    foreach($this->m_aData[self::PROPERTY_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if (!$this->ValidateStorageArea($nStorageAreaKeyID, $aStorageArea, $bNewRecord))
          $bValid = FALSE;
      
      if (!$aStorageArea['bDisabled'])
        $nCount++;
    }
    
    if ($nCount == 0)
    {
      $bValid = FALSE;
      $g_oError->AddError('<!$PICKUP_LOCATION_MUST_HAVE_AT_LEAST_ONE_STORAGE_AREA$!>');
    }
    
    return $bValid;
  }
  
  protected function ValidateStorageArea($nStorageAreaKeyID, $aStorageArea, $bNewRecord)
  {
    $bValid = TRUE;
    global $g_oError;
    
    if (!$bNewRecord)
    {
      //Is disabled
      if ($aStorageArea['bDisabled'] && !$this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'])
      {
        //get products associated with this storage area
        $sSQL =   " SELECT COPRDS.ProductKeyID, COPRD.fTotalCoopOrder, " . 
              $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
              " , COPRD.ProductKeyID FROM T_CoopOrderStorageArea COSA INNER JOIN T_CoopOrderProductStorage COPRDS " .
              " ON COSA.CoopOrderKeyID = COPRDS.CoopOrderKeyID AND COSA.StorageAreaKeyID = COPRDS.StorageAreaKeyID " .
              " INNER JOIN T_CoopOrderProduct COPRD ON COPRD.CoopOrderKeyID = COPRDS.CoopOrderKeyID " . 
              " AND COPRDS.ProductKeyID = COPRD.ProductKeyID " . 
              " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
              $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
              " WHERE COSA.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND COSA.StorageAreaKeyID = " . $nStorageAreaKeyID . ';';

        $this->RunSQL($sSQL);
        
        $sOrderedProducts = '';
        $sNonOrderedProducts = '';
        
        while($rec = $this->fetch())
        {
          if ($rec['fTotalCoopOrder'] > 0)
          {
            $bValid = FALSE;
            if ($sOrderedProducts != '')
              $sOrderedProducts .= ', ';
            
            $sOrderedProducts .= $rec['sProduct'];
          }
          else
          {
            if ($sNonOrderedProducts != '')
              $sNonOrderedProducts .= ', ';
            
            $sNonOrderedProducts .= $rec['sProduct'];
          }
        }
        
        if ($sOrderedProducts != '')
          $g_oError->AddError(sprintf('<!$CANNOT_DISABLE_STORAGE_AREA_ASSOCIATED_WITH_ORDERED_PRODUCTS$!>',
                $aStorageArea['sStorageArea'], $sOrderedProducts));
        if ($sNonOrderedProducts != '')
          $g_oError->AddError(sprintf('<!$WARN_ON_DISABLE_STORAGE_AREA_ASSOCIATED_WITH_PRODUCTS$!>',
                $aStorageArea['sStorageArea'], $sNonOrderedProducts), 'warning');
      }
    }
    
    return $bValid;
  }
  
  //save storage data
  protected function SaveStorageAreas($bNewRecord)
  {
    $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] = 0;
    
    foreach($this->m_aData[self::PROPERTY_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if (!$aStorageArea['bDisabled'] && ($bNewRecord || $this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled']))
        $this->InsertStorageArea($aStorageArea);
      elseif (!$aStorageArea['bDisabled'] && !$this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'])
        $this->UpdateStorageArea($aStorageArea);
      elseif ($aStorageArea['bDisabled'] && !$this->m_aOriginalData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'])
        $this->DeleteStorageArea($aStorageArea);
    }
    
    $this->CalculateMaxStorageBurden();
    
    if ($this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] == 0)
      $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] = NULL; //do not allow setting value to 0
  }
    
  protected function DeleteStorageArea($aStorageArea)
  {
    $sSQL =   " DELETE FROM T_CoopOrderStorageArea " .
              " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . ';';

    $this->RunSQL($sSQL);
    
    //delete storage areas for the products
    $sSQL = " DELETE FROM T_CoopOrderProductStorage " .
        " WHERE CoopOrderKeyID = ". $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . 
        " AND StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . ';';
    
    $this->RunSQL($sSQL);
  }
  
  protected function UpdateStorageArea($aStorageArea)
  {
    $aParams = array('MaxBurden' => $aStorageArea['fMaxBurden']);
    
    $sSQL =   " UPDATE T_CoopOrderStorageArea " .
              " SET fMaxBurden = :MaxBurden " .
              " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
              " AND StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . ';';

    $this->RunSQLWithParams($sSQL, $aParams);
    
    //collect entire pickup location sum
    $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] += $aStorageArea['fMaxBurden'];
  }
  
  protected function InsertStorageArea($aStorageArea)
  {        
    $aParams = array(
                      'CoopOrderKeyID' => $this->m_aData[self::PROPERTY_COOP_ORDER_ID],
                      'StorageAreaKeyID' => $aStorageArea['StorageAreaKeyID'],
                      'MaxBurden' => $aStorageArea['fMaxBurden']);
    
    $sSQL =   " INSERT INTO T_CoopOrderStorageArea (CoopOrderKeyID, StorageAreaKeyID, fMaxBurden) " .
              " VALUES(:CoopOrderKeyID, :StorageAreaKeyID, :MaxBurden);";
    
    $this->RunSQLWithParams($sSQL, $aParams);
    
    //add default storage areas for the products
    $sSQL = " INSERT INTO T_CoopOrderProductStorage (CoopOrderKeyID, ProductKeyID, PickupLocationKeyID, StorageAreaKeyID) " .
        " SELECT COSA.CoopOrderKeyID , COPRD.ProductKeyID, PLSA.PickupLocationKeyID , COSA.StorageAreaKeyID " .
        " FROM T_CoopOrderProduct COPRD INNER JOIN T_CoopOrderStorageArea COSA ON COPRD.CoopOrderKeyID = COSA.CoopOrderKeyID " .
        " INNER JOIN T_PickupLocationStorageArea PLSA ON PLSA.StorageAreaKeyID = COSA.StorageAreaKeyID " .
        " LEFT JOIN T_CoopOrderProductStorage COPS ON COSA.CoopOrderKeyID = COPS.CoopOrderKeyID " .
        " AND COPRD.ProductKeyID = COPS.ProductKeyID AND PLSA.PickupLocationKeyID = COPS.PickupLocationKeyID " .
        " WHERE COSA.CoopOrderKeyID = ". $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . 
        " AND PLSA.StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . 
        " AND PLSA.bDefault = 1 AND COPS.CoopOrderKeyID IS NULL;";
    
    $this->RunSQL($sSQL);
    
    //collect entire pickup location sum
    $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] += $aStorageArea['fMaxBurden'];
  }

  //calculate max storage burden for the coop order (by reducing prev amount)
  protected function CalculateMaxStorageBurden()
  {
    if ($this->m_aOriginalData[self::PROPERTY_MAX_STOARGE_BURDEN] != $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN])
    {
      $fAdd = $this->m_aData[self::PROPERTY_MAX_STOARGE_BURDEN] - $this->m_aOriginalData[self::PROPERTY_MAX_STOARGE_BURDEN];
      
      $sSQL = " UPDATE T_CoopOrder SET fMaxStorageBurden = Nullif(IfNull(fMaxStorageBurden,0) + (" . $fAdd . "),0) " .
          " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . ';';
      
      $this->RunSQL($sSQL);
      
      //also update internal variable for display
      $this->m_aData[self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN] += $fAdd;
    }
  }
}

?>
