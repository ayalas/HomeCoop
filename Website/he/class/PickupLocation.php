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
                            self::PROPERTY_CACHIER_DATE => NULL
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
    global $g_oMemberSession;
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
          
          $this->CommitTransaction();
        }
        catch(Exception $e)
        {
          $this->RollbackTransaction();
          $this->CloseConnection();
          $this->m_bUseClassConnection = FALSE;
          throw $e;
        }
        $this->CloseConnection();
        $this->m_bUseClassConnection = FALSE;
        
        $this->m_aData[self::PROPERTY_ID] = $nKeyID;
        
        if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $g_oMemberSession->CoordinatingGroupID;
        
        $this->m_aOriginalData = $this->m_aData;

        return TRUE;
    }
  
  
  public function Edit()
  {
    global $g_oMemberSession;
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
        
    return TRUE;
  }
  
}

?>
