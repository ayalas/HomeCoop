<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitate the coord/pickuploc.php coordinator's page, editing/viewing a pickup location
class PickupLocation extends SQLBase {
  const PROPERTY_NAME = "Name";
  const PROPERTY_NAMES = "Names";
  const PROPERTY_ADDRESS_STR_ID = "AddressStringID";
  const PROPERTY_ADDRESS_STRINGS = "AddressStrings";
  const PROPERTY_PUBLISHED_STR_ID = "PublishedCommentsID";
  const PROPERTY_PUBLISHED_STRINGS = "PublishedComments";
  const PROPERTY_ADMIN_STR_ID = "AdminCommentsID";
  const PROPERTY_ADMIN_STRINGS = "AdminComments";
  const PROPERTY_IS_DISABLED = "IsDisabled";
  const PROPERTY_MAX_BURDEN = "MaxBurden";
  const PROPERTY_ROTATION_ORDER = "RotationOrder";
  const PROPERTY_EXPORT_FILE_NAME = "ExportFileName";
  const PROPERTY_CACHIER = "Cachier";
  const PROPERTY_PREV_CACHIER = "PrevCachier";
  const PROPERTY_CACHIER_DATE = "CachierDate";
  const PROPERTY_STORAGE_AREAS = "StorageAreas";
  const PROPERTY_NEW_STORAGE_AREAS = "NewStorageAreas";
  const PROPERTY_TRANSACTION = "Transaction";
  
  const MAX_LENGTH_EXPORT_FILE_NAME = 40;
  
  
  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_ID => 0,
                            self::PROPERTY_NAMES => NULL,
                            self::PROPERTY_ADDRESS_STR_ID => 0,
                            self::PROPERTY_ADDRESS_STRINGS => NULL,
                            self::PROPERTY_PUBLISHED_STR_ID => 0,
                            self::PROPERTY_PUBLISHED_STRINGS => NULL,
                            self::PROPERTY_ADMIN_STR_ID => 0,
                            self::PROPERTY_ADMIN_STRINGS => NULL,
                            self::PROPERTY_IS_DISABLED => FALSE,
                            self::PROPERTY_MAX_BURDEN => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_ROTATION_ORDER => NULL,
                            self::PROPERTY_EXPORT_FILE_NAME => NULL,
                            self::PROPERTY_CACHIER => NULL,
                            self::PROPERTY_PREV_CACHIER => NULL,
                            self::PROPERTY_CACHIER_DATE => NULL,
                            self::PROPERTY_STORAGE_AREAS => array(),
                            self::PROPERTY_NEW_STORAGE_AREAS => array(),
                            self::PROPERTY_TRANSACTION => NULL,
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData; 
  }
  
  public function __get( $name ) {
    global $g_sLangDir;
    switch ($name)
    {
      case self::PROPERTY_NAME:
        if ($g_sLangDir == '')
          return $this->m_aData[self::PROPERTY_NAMES][0];
        else
          return $this->GetLangPropertyVal(self::PROPERTY_NAMES,$g_sLangDir);
      default:
        return parent::__get($name);
    }
  }
  
  //limit properties that can be set
  public function __set( $name, $value ) {
    global $g_sLangDir;
    switch ($name)
    {
      case self::PROPERTY_COORDINATING_GROUP_ID:
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
  
  public function CheckAccess()
  {
     return $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
  
  public function CheckDeletePermission()
  {
     if ($this->HasPermission(self::PERMISSION_DELETE))
         return TRUE;
    
     return $this->AddPermissionBridge(self::PERMISSION_DELETE, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_DELETE, 
          Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
  }
  
  public function LoadRecord($nID)
  {
    global $g_oTimeZone;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //general permission check
    if ( !$this->CheckAccess() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_ID] = $nID;
    
    $sSQL =   " SELECT PL.PickupLocationKeyID, PL.AddressStringKeyID, PL.PublishedCommentsStringKeyID, PL.fMaxBurden, PL.AdminCommentsStringKeyID, PL.CoordinatingGroupID, " .
              " PL.bDisabled, PL.nRotationOrder, PL.sExportFileName, PL.mCachier, PL.mPrevCachier, PL.dCachierUpdate " .
              " FROM T_PickupLocation PL " . 
              " WHERE PL.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
    if ($rec["CoordinatingGroupID"])
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];

    //coordinating group permission check
    if ( !$this->AddPermissionBridgeGroupID(self::PERMISSION_PAGE_ACCESS, FALSE) )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }
       
    //populate record properties
    $this->m_aData[self::PROPERTY_ADMIN_STR_ID] = $rec["AdminCommentsStringKeyID"];
    $this->m_aData[self::PROPERTY_PUBLISHED_STR_ID] = $rec["PublishedCommentsStringKeyID"];
    $this->m_aData[self::PROPERTY_ADDRESS_STR_ID] = $rec["AddressStringKeyID"];
    $this->m_aData[self::PROPERTY_IS_DISABLED] = $rec["bDisabled"];
    $this->m_aData[self::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
    $this->m_aData[self::PROPERTY_ROTATION_ORDER] = $rec["nRotationOrder"];
    
    $this->m_aData[self::PROPERTY_CACHIER] = $rec["mCachier"];
    $this->m_aData[self::PROPERTY_PREV_CACHIER] = $rec["mPrevCachier"];
    if ($rec["dCachierUpdate"] != NULL)
      $this->m_aData[self::PROPERTY_CACHIER_DATE] = new DateTime($rec["dCachierUpdate"], $g_oTimeZone);
        
    $this->m_aData[self::PROPERTY_EXPORT_FILE_NAME] = $rec["sExportFileName"];
    
    $this->m_aData[self::PROPERTY_NAMES] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_ID]);
    $this->m_aData[self::PROPERTY_ADDRESS_STRINGS] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_ADDRESS_STR_ID]);
    $this->m_aData[self::PROPERTY_PUBLISHED_STRINGS] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_PUBLISHED_STR_ID]);
    $this->m_aData[self::PROPERTY_ADMIN_STRINGS] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_ADMIN_STR_ID]);
    
    //load storage areas
    $sSQL =     " SELECT PLSA.StorageAreaKeyID, PLSA.fMaxBurden, PLSA.bDisabled, PLSA.bDefault  " .
                " FROM T_PickupLocationStorageArea PLSA " . 
                " WHERE PLSA.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] .
                " ORDER BY PLSA.bDisabled, PLSA.StorageAreaKeyID;";

    $this->RunSQL( $sSQL );

    while($rec = $this->fetch())
    {
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$rec["StorageAreaKeyID"]] = $rec;
      
      $this->m_bUseSecondSqlPreparedStmt = true;
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$rec["StorageAreaKeyID"]][self::PROPERTY_NAMES] = 
          $this->GetKeyStrings($rec["StorageAreaKeyID"]);
      
      $this->m_bUseSecondSqlPreparedStmt = false;
    }
    
    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  public function Add()
    {
        global $g_oMemberSession;
        global $g_sLangDir;
        global $g_dNow;
        
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

        //general permission check
        if ( !$this->CheckAccess() || !$this->AddPermissionBridge(self::PERMISSION_ADD, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_ADD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
        {
            $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
            return FALSE;
        }
        
        $this->CollectStoragePostData();

        if (!$this->Validate())
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
          return FALSE;
        }
            
        try
        {
          //counting on last inserted id, so better to use private connection
          $this->m_bUseClassConnection = TRUE;
          $this->BeginTransaction();
        
          //create new string key for the new record
          $nKeyID = $this->NewKey();

          //insert names     
          $this->InsertStrings($this->m_aData[self::PROPERTY_NAMES], $nKeyID);

          $this->m_aData[self::PROPERTY_ADDRESS_STR_ID] = $this->NewKey();
          $this->InsertStrings($this->m_aData[self::PROPERTY_ADDRESS_STRINGS], $this->m_aData[self::PROPERTY_ADDRESS_STR_ID]);

          $this->m_aData[self::PROPERTY_PUBLISHED_STR_ID] = $this->NewKey();
          $this->InsertStrings($this->m_aData[self::PROPERTY_PUBLISHED_STRINGS], $this->m_aData[self::PROPERTY_PUBLISHED_STR_ID]);

          $this->m_aData[self::PROPERTY_ADMIN_STR_ID] = $this->NewKey();
          $this->InsertStrings($this->m_aData[self::PROPERTY_ADMIN_STRINGS], $this->m_aData[self::PROPERTY_ADMIN_STR_ID]);
          
          $arrParams = array("MaxBurden" => $this->m_aData[self::PROPERTY_MAX_BURDEN]);

          //insert the record
          $sSQL =  " INSERT INTO T_PickupLocation( PickupLocationKeyID, AddressStringKeyID, PublishedCommentsStringKeyID, AdminCommentsStringKeyID, bDisabled" .
                  $this->ConcatColIfNotNull(self::PROPERTY_ROTATION_ORDER, "nRotationOrder") .
                  $this->ConcatColIfNotNull(self::PROPERTY_EXPORT_FILE_NAME, "sExportFileName");
          
          if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
              $sSQL .= ", CoordinatingGroupID ";

          $sSQL .= ", fMaxBurden "; 
          
          if ( $this->m_aData[self::PROPERTY_CACHIER] != NULL)
            $sSQL .= ", mCachier, dCachierUpdate " ;
          
          $sSQL .= "  ) VALUES (" . $nKeyID . "," . $this->m_aData[self::PROPERTY_ADDRESS_STR_ID] . "," . $this->m_aData[self::PROPERTY_PUBLISHED_STR_ID] . 
                  "," . $this->m_aData[self::PROPERTY_ADMIN_STR_ID] . "," . intval($this->m_aData[self::PROPERTY_IS_DISABLED]) .
                  $this->ConcatValIfNotNull(self::PROPERTY_ROTATION_ORDER);

          if ( $this->m_aData[self::PROPERTY_EXPORT_FILE_NAME] != NULL)
          {
             $sSQL .= ", :ExportFileName ";
             $arrParams["ExportFileName"] = CoopOrderExport::remove_filename_special_char($this->m_aData[self::PROPERTY_EXPORT_FILE_NAME]);
          }
           
          //group
          if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
            $sSQL .= ", " . $g_oMemberSession->CoordinatingGroupID;

          $sSQL .= ", :MaxBurden "; 
          
          if ( $this->m_aData[self::PROPERTY_CACHIER] != NULL)
          {
            $sSQL .= ", :Cachier, :CachierUpdate "; 
            $arrParams["Cachier"] = $this->m_aData[self::PROPERTY_CACHIER];
            $this->m_aData[self::PROPERTY_CACHIER_DATE] = $g_dNow;
            $arrParams["CachierUpdate"] = $this->m_aData[self::PROPERTY_CACHIER_DATE]->format(DATABASE_DATE_FORMAT);
          }
          
          $sSQL .= " );";

          $this->RunSQLWithParams( $sSQL, $arrParams );
          
          $this->m_aData[self::PROPERTY_ID] = $nKeyID; //needed in SaveStorageAreas
          
          $this->SaveStorageAreas();
          
          if ( $this->m_aData[self::PROPERTY_CACHIER] != NULL)
          {
            $this->InsertTransaction($this->m_aData[self::PROPERTY_CACHIER]);
          }
          
          $this->CommitTransaction();
          
          $this->m_aData[self::PROPERTY_TRANSACTION] = NULL;
          
          $this->ApplySaveStorageAreas();
        }
        catch(Exception $e)
        {
          $this->m_aData[self::PROPERTY_ID] = 0;
          $this->RollbackTransaction();
          $this->CloseConnection();
          $this->m_bUseClassConnection = FALSE;
          throw $e;
        }
        $this->CloseConnection();
        $this->m_bUseClassConnection = FALSE;
        
        if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $g_oMemberSession->CoordinatingGroupID;
        
        $this->m_aOriginalData = $this->m_aData;

        return TRUE;
    }
  
  
  public function Edit()
  {
    global $g_sLangDir;
    global $g_dNow;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //permission check    
    if (! $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE))
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $this->CollectStoragePostData();
        
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $arrParams = array(
            "ExportFileName" => CoopOrderExport::remove_filename_special_char($this->m_aData[self::PROPERTY_EXPORT_FILE_NAME]),
            "RotationOrder" => $this->m_aData[self::PROPERTY_ROTATION_ORDER], 
            "MaxBurden" => $this->m_aData[self::PROPERTY_MAX_BURDEN]
          );
    
    try
    {
      $this->BeginTransaction();
    
      $sSQL =   " UPDATE T_PickupLocation " .
                " SET sExportFileName = :ExportFileName, nRotationOrder = :RotationOrder, fMaxBurden = :MaxBurden, bDisabled = " . intval($this->m_aData[self::PROPERTY_IS_DISABLED]);
      
      if ($this->m_aData[self::PROPERTY_CACHIER] != $this->m_aOriginalData[self::PROPERTY_CACHIER])
      {
        $sSQL .=  ", mCachier = :Cachier, dCachierUpdate = :CachierUpdate, mPrevCachier = :PrevCachier ";
        
        $this->m_aData[self::PROPERTY_PREV_CACHIER] = $this->m_aOriginalData[self::PROPERTY_CACHIER];
        $this->m_aData[self::PROPERTY_CACHIER_DATE] = $g_dNow;
        
        $arrParams["Cachier"] = $this->m_aData[self::PROPERTY_CACHIER];
        $arrParams["CachierUpdate"] = $this->m_aData[self::PROPERTY_CACHIER_DATE]->format(DATABASE_DATE_FORMAT);
        $arrParams["PrevCachier"] = $this->m_aData[self::PROPERTY_PREV_CACHIER];
      }

      $sSQL .=  " WHERE PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

      
      $this->RunSQLWithParams( $sSQL, $arrParams );


      $this->UpdateStrings(self::PROPERTY_NAMES, $this->m_aData[self::PROPERTY_ID]);

      $this->UpdateStrings(self::PROPERTY_ADDRESS_STRINGS, $this->m_aOriginalData[self::PROPERTY_ADDRESS_STR_ID]);
      $this->UpdateStrings(self::PROPERTY_PUBLISHED_STRINGS, $this->m_aOriginalData[self::PROPERTY_PUBLISHED_STR_ID]);
      $this->UpdateStrings(self::PROPERTY_ADMIN_STRINGS, $this->m_aOriginalData[self::PROPERTY_ADMIN_STR_ID]);
      
      $this->SaveStorageAreas();
      
      if ($this->m_aData[self::PROPERTY_CACHIER] != $this->m_aOriginalData[self::PROPERTY_CACHIER])
      {
        $this->InsertTransaction($this->m_aData[self::PROPERTY_CACHIER] - $this->m_aOriginalData[self::PROPERTY_CACHIER]);
      }
      
      $this->CommitTransaction();
      
      $this->m_aData[self::PROPERTY_TRANSACTION] = NULL;
      
      $this->ApplySaveStorageAreas();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  public function PreserveFormValues()
  {
    $this->m_aData[self::PROPERTY_ADDRESS_STR_ID] = $this->m_aOriginalData[self::PROPERTY_ADDRESS_STR_ID];
    $this->m_aData[self::PROPERTY_PUBLISHED_STR_ID] = $this->m_aOriginalData[self::PROPERTY_PUBLISHED_STR_ID];
    $this->m_aData[self::PROPERTY_ADMIN_STR_ID] = $this->m_aOriginalData[self::PROPERTY_ADMIN_STR_ID];
    $this->m_aData[self::PROPERTY_PREV_CACHIER] = $this->m_aOriginalData[self::PROPERTY_PREV_CACHIER];
    $this->m_aData[self::PROPERTY_CACHIER_DATE] = $this->m_aOriginalData[self::PROPERTY_CACHIER_DATE]; 
  }
  
  public function Delete()
  {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //permission check
    if (! $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) || 
            !$this->CheckDeletePermission())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
     try
    {
      $this->BeginTransaction();
    
      $sSQL =   " DELETE FROM T_PickupLocation " .
                 " WHERE PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

      $this->RunSQL($sSQL);

      $this->DeleteKey($this->m_aData[self::PROPERTY_ID]); //deletes all associated strings
      $this->DeleteKey($this->m_aOriginalData[self::PROPERTY_ADDRESS_STR_ID]); //deletes all associated strings
      $this->DeleteKey($this->m_aOriginalData[self::PROPERTY_PUBLISHED_STR_ID]); //deletes all associated strings
      $this->DeleteKey($this->m_aOriginalData[self::PROPERTY_ADMIN_STR_ID]); //deletes all associated strings
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }

    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
    
    return TRUE;
  }
  
  
  public function Validate()
  {
    global $g_oError;
    global $g_sLangDir;
    
    $bValid = TRUE;
    
    if (!$this->ValidateRequiredNames(self::PROPERTY_NAMES, 'מקום האיסוף'))
      $bValid = FALSE;
    
    if (!$this->ValidateRequiredNames(self::PROPERTY_ADDRESS_STRINGS, 'כתובת'))
      $bValid = FALSE;
    
    if (!$this->ValidateStorageAreas())
      $bValid = FALSE;
    
    return $bValid;
  }
  
  public function LoadCOPickupLocationDefaults($nID)
  {   
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //permission check: must be able to add coop order product for this specific producer
    if (!$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
         Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
     
    $this->m_aData[self::PROPERTY_ID] = $nID;
    
    $sSQL =   " SELECT PL.fMaxBurden, PL.CoordinatingGroupID, " .
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
              " FROM T_PickupLocation PL " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
              " WHERE PL.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
    if ($rec["CoordinatingGroupID"])
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];

    //coordinating group permission check
    if (!$this->AddPermissionBridgeGroupID(self::PERMISSION_EDIT, FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
    $this->m_aData[self::PROPERTY_NAME] = $rec["sPickupLocation"];
    
    //load storage areas
    $sSQL =     " SELECT PLSA.StorageAreaKeyID, PLSA.fMaxBurden, PLSA.bDisabled, PLSA.bDefault, " .
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_STORAGE_AREAS, 'sStorageArea') .
                " FROM T_PickupLocationStorageArea PLSA " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_STORAGE_AREAS) .
                " WHERE PLSA.PickupLocationKeyID = " . $this->m_aData[self::PROPERTY_ID] .
                " AND PLSA.bDisabled = 0 " .
                " ORDER BY PLSA.bDisabled, PLSA.StorageAreaKeyID;";

    $this->RunSQL( $sSQL );

    //key records by id
    while($rec = $this->fetch())
    {
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$rec["StorageAreaKeyID"]] = $rec;
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$rec["StorageAreaKeyID"]]['bDisabled'] = (intval($rec['bDisabled']) == 1);
      $this->m_aData[self::PROPERTY_STORAGE_AREAS][$rec["StorageAreaKeyID"]]['bDefault'] = (intval($rec['bDefault']) == 1);
    }
        
    return TRUE;
  }
  
  protected function ValidateStorageAreas()
  {
    global $g_oError;

    $bValid = TRUE;
    $nCount = 0;
    $nIndex = 0;
    $nDefaultCount = 0;
    
    // %%%%%%%%%%%%%%%%%%% loop through existing storage areas %%%%%%%%%%%%%%%%%%%%%%%%%%%%
    foreach($this->m_aData[self::PROPERTY_STORAGE_AREAS] as $StorageAreaKeyID => $aStorageArea)
    {
      $nIndex++;
      if ($aStorageArea['Delete'])
      {
        //transform deleted storages to inactive ones, if cannot delete them, issue warning
        if (!$this->StorageAreaDependencyCheck($StorageAreaKeyID))
        {
          $this->m_aData[self::PROPERTY_STORAGE_AREAS][$StorageAreaKeyID]['Delete'] = $aStorageArea['Delete'] = FALSE;
          $this->m_aData[self::PROPERTY_STORAGE_AREAS][$StorageAreaKeyID]['bDisabled'] = TRUE;
        }
      }
      
      if (!$aStorageArea['Delete'])
      {
        if (!$this->m_aData[self::PROPERTY_STORAGE_AREAS][$StorageAreaKeyID]['bDisabled'])
        {
          $nCount++;
        
          if ($aStorageArea['bDefault'])
            $nDefaultCount++;
        }

        if (!$this->ValidateStorageAreaName($StorageAreaKeyID, $nIndex, $aStorageArea, FALSE))
            $bValid = FALSE;
      }
    }
    
    // %%%%%%%%%%%%%%%%%%% NEW STORAGE AREAS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    $nIndex = 0;
    foreach($this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS] as $StorageAreaKeyID => $aStorageArea)
    {
        $nIndex++;
        if (!$this->ValidateStorageAreaName($StorageAreaKeyID, $nIndex, $aStorageArea, TRUE))
            $bValid = FALSE;
        
        //previous validation may mark some empty rows as ignored - so do not count them
        if (!$this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$StorageAreaKeyID]['Delete'] &&
            !$this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$StorageAreaKeyID]['bDisabled'])
        {
          $nCount++;
          if ($aStorageArea['bDefault'])
            $nDefaultCount++;
        }
        
    }
    
    // %%%%%%%%%%%%% validate that there is at least one storage area left %%%%%%%%%%%%%%%
    if ($nCount == 0)
    {
      $bValid = FALSE;
      $g_oError->AddError('מקום איסוף חייב לכלול מקום אחסון אחד לפחות.');
    }
    elseif ($nDefaultCount != 1)
    {
      $bValid = FALSE;
      $g_oError->AddError('מקום איסוף חייב לכלול מקום אחסון אחד של ברירת מחדל.');
    }
    
    return $bValid;
  }
  
  //save storage data
  protected function SaveStorageAreas()
  {
    //first, process existing ones: DELETE, UPDATE
    foreach($this->m_aData[self::PROPERTY_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if ($aStorageArea['Delete'])
        $this->DeleteStorageArea($aStorageArea);
      else 
        $this->UpdateStorageArea($aStorageArea);
    }
    //second, process new ones: INSERT
    foreach($this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if (!$aStorageArea['Delete'])
        $this->InsertStorageArea($aStorageArea);
    }
  }
  
  //save storage data
  protected function ApplySaveStorageAreas()
  {
    //first, process existing ones: DELETE, UPDATE
    foreach($this->m_aData[self::PROPERTY_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if ($aStorageArea['Delete'])
        unset($this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]);
    }
    //second, process new ones: INSERT
    foreach($this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS] as $nStorageAreaKeyID => $aStorageArea)
    {
      if (!$aStorageArea['Delete'])
      {
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$aStorageArea['NewStorageAreaKeyID']] = $aStorageArea;
        //replace the temp NewStorageAreaKeyID - with StorageAreaKeyID
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$aStorageArea['NewStorageAreaKeyID']]['StorageAreaKeyID'] =
            $aStorageArea['NewStorageAreaKeyID'];
        unset($this->m_aData[self::PROPERTY_STORAGE_AREAS][$aStorageArea['NewStorageAreaKeyID']]['NewStorageAreaKeyID']);
      }
    }
    
    $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS] = array();
  }
  
  protected function DeleteStorageArea($aStorageArea)
  {
    $sSQL =   " DELETE FROM T_PickupLocationStorageArea " .
                 " WHERE StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . ';';

    $this->RunSQL($sSQL);

    $this->DeleteKey($aStorageArea['StorageAreaKeyID']); //deletes all associated strings
  }
  
  protected function UpdateStorageArea($aStorageArea)
  {
    $aParams = array('Disabled' => intval($aStorageArea['bDisabled']), 
                     'Default' => intval($aStorageArea['bDefault']), 
                     'MaxBurden' => $aStorageArea['fMaxBurden']);
    
    $sSQL =   " UPDATE T_PickupLocationStorageArea " .
              " SET bDisabled = :Disabled, bDefault = :Default, fMaxBurden = :MaxBurden " .
              " WHERE StorageAreaKeyID = " . $aStorageArea['StorageAreaKeyID'] . ';';

    $this->RunSQLWithParams($sSQL, $aParams);
    
    $this->UpdateStringsFromArray($aStorageArea[self::PROPERTY_NAMES], $aStorageArea['StorageAreaKeyID']);
  }
  
  protected function InsertStorageArea($aStorageArea)
  {
    //create new key for the storage area
    $nStorageAreaKeyID = $this->NewKey();
    
    //insert names
    $this->InsertStrings($aStorageArea[self::PROPERTY_NAMES], $nStorageAreaKeyID);
    
    $aParams = array(
                      'PickupLocationKeyID' => $this->m_aData[self::PROPERTY_ID],
                      'StorageAreaKeyID' => $nStorageAreaKeyID,
                      'Disabled' => intval($aStorageArea['bDisabled']), 
                      'Default' => intval($aStorageArea['bDefault']), 
                      'MaxBurden' => $aStorageArea['fMaxBurden']);
    
    $sSQL =   " INSERT INTO T_PickupLocationStorageArea (PickupLocationKeyID, StorageAreaKeyID, bDisabled, bDefault, fMaxBurden) " .
              " VALUES(:PickupLocationKeyID, :StorageAreaKeyID, :Disabled, :Default, :MaxBurden);";
    
    $this->RunSQLWithParams($sSQL, $aParams);
    
    //save real key (used in $this->ApplySaveStorageAreas())
    $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$aStorageArea['StorageAreaKeyID']]['NewStorageAreaKeyID'] = $nStorageAreaKeyID;
  }
  
  
  //process post data and return an unkeyed array with each element in this format
  // StorageAreaKeyID => id
  // Names => return value from ComplexPostData::GetNames
  // Disabled => TRUE/FALSE
  // Delete => TRUE/FALSE
  protected function CollectStoragePostData()
  {
    global $_POST;
    $nStorageAreaKeyID = 0;
    $nNamePrefixLen = strlen(HtmlStorageArea::CTL_NAME_PREFIX);
    $nDisabledPrefixLen = strlen(HtmlStorageArea::CTL_DISABLED_PREFIX);
    $nMaxBurdenPrefixLen = strlen(HtmlStorageArea::CTL_MAX_BURDEN_PREFIX);
    $nDeletePrefixLen = strlen(HtmlStorageArea::CTL_DELETE_PREFIX);
    $nNewNamePrefixLen = strlen(HtmlStorageArea::CTL_NEW_NAME_PREFIX);
    $nNewDisabledPrefixLen = strlen(HtmlStorageArea::CTL_NEW_DISABLED_PREFIX);
    $nNewMaxBurdenPrefixLen = strlen(HtmlStorageArea::CTL_NEW_MAX_BURDEN_PREFIX);
    $sBaseCtlName = NULL;
    
    

    foreach($_POST as $key => $value)
    {
      //if found in position 0
      if (strpos($key, HtmlStorageArea::CTL_NAME_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStorageMultiLangPostElement($key, $nNamePrefixLen, $sBaseCtlName,
            self::PROPERTY_STORAGE_AREAS);

        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID][self::PROPERTY_NAMES] = ComplexPostData::GetNames($sBaseCtlName);
      }
      elseif (strpos($key, HtmlStorageArea::CTL_DISABLED_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nDisabledPrefixLen,
            self::PROPERTY_STORAGE_AREAS);

        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'] = (intval($value) == 1);
      }
      elseif (strpos($key, HtmlStorageArea::CTL_MAX_BURDEN_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nMaxBurdenPrefixLen,
            self::PROPERTY_STORAGE_AREAS);

        if (!empty($value)) //allow null, do not allow 0
          $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['fMaxBurden'] = 0 + $value;
      }
      elseif (strpos($key, HtmlStorageArea::CTL_DELETE_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nDeletePrefixLen,
            self::PROPERTY_STORAGE_AREAS);

        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nStorageAreaKeyID]['Delete'] = (intval($value) == 1);
      }
      elseif (strpos($key, HtmlStorageArea::CTL_NEW_NAME_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStorageMultiLangPostElement($key, $nNewNamePrefixLen, $sBaseCtlName,
            self::PROPERTY_NEW_STORAGE_AREAS);

        $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$nStorageAreaKeyID][self::PROPERTY_NAMES] = ComplexPostData::GetNames($sBaseCtlName);
      }
      elseif (strpos($key, HtmlStorageArea::CTL_NEW_DISABLED_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nNewDisabledPrefixLen,
            self::PROPERTY_NEW_STORAGE_AREAS);

        $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$nStorageAreaKeyID]['bDisabled'] = (intval($value) == 1);
      }
      elseif (strpos($key, HtmlStorageArea::CTL_NEW_MAX_BURDEN_PREFIX) === 0)
      {
        $nStorageAreaKeyID = $this->InitStoragePostElement($key, $nNewMaxBurdenPrefixLen,
            self::PROPERTY_NEW_STORAGE_AREAS);

        if (!empty($value)) //allow null, do not allow 0
          $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$nStorageAreaKeyID]['fMaxBurden'] = 0 + $value;
      }
    }
    
    //set the Default property
    //get default value
    if (isset($_POST[HtmlStorageArea::CTL_DEFAULT_GROUP]))
    {
      $nDefaultStorageAreaID = intval($_POST[HtmlStorageArea::CTL_DEFAULT_GROUP]);
      if ($nDefaultStorageAreaID >= HtmlStorageArea::MIN_NEW_CONTROLS_NUM)
      {
        $nDefaultStorageAreaID -= HtmlStorageArea::MIN_NEW_CONTROLS_NUM;
        if (isset($this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$nDefaultStorageAreaID]))
          $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$nDefaultStorageAreaID]['bDefault'] = TRUE;
      }
      elseif (isset($this->m_aData[self::PROPERTY_STORAGE_AREAS][$nDefaultStorageAreaID]))
        $this->m_aData[self::PROPERTY_STORAGE_AREAS][$nDefaultStorageAreaID]['bDefault'] = TRUE;
    }
    
  }

  protected function InitStorageMultiLangPostElement($key, $nPrefixLen, &$sBaseCtlName, $sStorageArrKey)
  {
    global $g_nCountLanguages;
    $sIDPlusLang = mb_substr($key, $nPrefixLen );

    if ($g_nCountLanguages > 0)
    {
      $nPos = strpos($sIDPlusLang, HtmlTextEditMultiLang::ID_LINK);
      if ($nPos > 0)
      {
        $nStorageAreaKeyID = 0 + mb_substr($sIDPlusLang, 0, $nPos );
        $sBaseCtlName = mb_substr($key, 0, $nPrefixLen + $nPos );
      }
    }
    else
    {
      $nStorageAreaKeyID = 0 + $sIDPlusLang;
      $sBaseCtlName = $key;
    }

    return $this->InitStoragePostElementFromId($key, $nStorageAreaKeyID, $sStorageArrKey);
  }

  protected function InitStoragePostElement($key, $nPrefixLen, $sStorageArrKey)
  {
    $nStorageAreaKeyID = 0 + mb_substr($key, $nPrefixLen );;

    return $this->InitStoragePostElementFromId($key, $nStorageAreaKeyID, $sStorageArrKey);
  }

  protected function InitStoragePostElementFromId($key, $nStorageAreaKeyID, $sStorageArrKey)
  {
    if ($nStorageAreaKeyID == 0)
      throw new Exception('Error in PickupLocation.InitStoragePostElementFromId: StorageAreaKeyID 0 for post key ' . $key);

    if (!array_key_exists($nStorageAreaKeyID, $this->m_aData[$sStorageArrKey]))
    {
      $this->m_aData[$sStorageArrKey][$nStorageAreaKeyID] = array(
        'StorageAreaKeyID' => $nStorageAreaKeyID,
        self::PROPERTY_NAMES => NULL,
        'fMaxBurden' => NULL,
        'bDisabled' => FALSE,
        'Delete' => FALSE,
        'bDefault' => FALSE,
      );
    }

    return $nStorageAreaKeyID;
  }
  
  protected function ValidateStorageAreaName($StorageAreaKeyID, $nIndex, $aStorageArea, $bNew)
  {
    global $g_aSupportedLanguages;
    global $g_sLangDir;
    global $g_oError;
    
    $bValid = TRUE;
    
    $nRes = $this->ValidateNames($aStorageArea[self::PROPERTY_NAMES]);
        
    switch($nRes)
    {
      case self::VALIDATE_NAMES_EMPTY_ARRAY:
      case self::VALIDATE_NAMES_ALL_LANGUAGES_EMPTY:
        if ($bNew) //new record
        {
          //ignore new storage when empty
          $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$StorageAreaKeyID]['Delete'] = TRUE;
        }
        else //existing record
        {
          //return error that name must be filled, or the storage location must be marked for deletion
          $bValid = FALSE;
          $g_oError->AddError(sprintf('מקום אחסון %s: יש למלא שם, או לסמן את מקום האחסון למחיקה.', $nIndex));
        }
        break;
      //partial empty: fill missing values
      case self::VALIDATE_NAMES_CURRENT_LANGUAGE_EMPTY:
      case self::VALIDATE_NAMES_OTHER_LANGUAGE_EMPTY:
        $sNonEmptyName = '';

        if ($nRes == self::VALIDATE_NAMES_OTHER_LANGUAGE_EMPTY)
          $sNonEmptyName = $aStorageArea[self::PROPERTY_NAMES][$g_sLangDir]; //get non-empty value from current language
        else
        {
          //get first non-empty value
          foreach($g_aSupportedLanguages as $Lkey => $aLang)
          {
            if ($aStorageArea[self::PROPERTY_NAMES][$Lkey] != NULL)
            {
              $sNonEmptyName = $aStorageArea[self::PROPERTY_NAMES][$Lkey];
              break;
            }
          }
        }
        //fill empty languages with non-empty value
        foreach($g_aSupportedLanguages as $Lkey => $aLang)
        {
          if ($aStorageArea[self::PROPERTY_NAMES][$Lkey] == NULL)
          {
            if ($bNew)
              $this->m_aData[self::PROPERTY_NEW_STORAGE_AREAS][$StorageAreaKeyID][self::PROPERTY_NAMES][$Lkey] = $sNonEmptyName;
            else
              $this->m_aData[self::PROPERTY_STORAGE_AREAS][$StorageAreaKeyID][self::PROPERTY_NAMES][$Lkey] = $sNonEmptyName;
          }
        }
        break;
    }
    
    return $bValid;
  }
  
  protected function StorageAreaDependencyCheck($StorageAreaKeyID)
  {
    $sSQLQuery = 'SELECT COUNT(1) as nCount FROM T_CoopOrderStorageArea WHERE StorageAreaKeyID = ' . $StorageAreaKeyID . ';';
    $this->RunSQL( $sSQLQuery );
    $res = $this->fetch();
    return (!isset($res) || $res["nCount"] == 0);
  }
  
  protected function InsertTransaction($mAmount)
  {
    global $g_oMemberSession;
    global $g_dNow;
    $sSQL = " INSERT INTO T_Transaction (PickupLocationKeyID, ModifiedByMemberID, mAmount, dDate, sTransaction) " .
          " VALUES(:pickuplocid, :modifier, :amount, :date, " ; 
    $arrParams = array(
              'pickuplocid' => $this->m_aData[self::PROPERTY_ID],
              'modifier' => $g_oMemberSession->MemberID,
            'amount' => $mAmount,
            'date' => $g_dNow->format(DATABASE_DATE_FORMAT),
          );
    
    if ($this->m_aData[self::PROPERTY_TRANSACTION] != NULL)
    {
      $sSQL .= ' :desc';
      $arrParams['desc'] = $this->m_aData[self::PROPERTY_TRANSACTION];
    }
    else
      $sSQL .= ' NULL';
    
    $sSQL .= ");";

    $this->RunSQLWithParams($sSQL, $arrParams);    
    
  }
  
}

?>
