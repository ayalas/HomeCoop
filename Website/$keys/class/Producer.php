<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faclitates coord/producer.php - producer's view/edit page
class Producer extends SQLBase
{
  const PROPERTY_PRODUCER_ID = "ProducerID";
  const PROPERTY_PRODUCER_NAME = "ProducerName";
  const PROPERTY_PRODUCER_NAMES = "ProducerNames";
  const PROPERTY_IS_DISABLED = "IsDisabled";
  
  
  const PROPERTY_EXPORT_FILE_NAME = "ExportFileName";
  
  const MAX_LENGTH_EXPORT_FILE_NAME = 40;

  public function __construct()
  {
    $this->m_aDefaultData = array(
      self::PROPERTY_PRODUCER_ID => NULL, 
      self::PROPERTY_PRODUCER_NAME => NULL,
      self::PROPERTY_PRODUCER_NAMES => NULL,
      self::PROPERTY_IS_DISABLED => FALSE,
      self::PROPERTY_COORDINATING_GROUP_ID => 0,
      self::PROPERTY_EXPORT_FILE_NAME => NULL
      );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData; 
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
    
  public function __get( $name ) {
    global $g_sLangDir;
    switch ($name)
    {
      case self::PROPERTY_PRODUCER_NAME:
        if ($g_sLangDir == '')
          return $this->m_aData[self::PROPERTY_PRODUCER_NAMES][0];
        else
          return $this->GetLangPropertyVal(self::PROPERTY_PRODUCER_NAMES,$g_sLangDir);
     
      default:
        return parent::__get($name);
    }
  }

    public function CheckAccess()
    {
       $bEdit = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
        
       $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
        
       return ($bEdit || $bView);
    }
    
    public function LoadRecord( $nProducerId )
    {
      global $g_oMemberSession;

      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

      //general permission check
      if ( !$this->CheckAccess() )
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
      }
      
      $sSQL =          " SELECT P.CoordinatingGroupID, P.bDisabled, P.sExportFileName " .
                       " FROM T_Producer P " . 
                       " WHERE P.ProducerKeyID = " . $nProducerId . ';';
            
      $this->RunSQL($sSQL);
      $rec = $this->fetch();
      if (!$rec || count($rec) == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }
      
      $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
      if ($rec["CoordinatingGroupID"])
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
    
      //coordinating group permission check
      if ( !$this->AddPermissionBridgeGroupID(self::PERMISSION_COORD, FALSE) &&
           !$this->AddPermissionBridgeGroupID(self::PERMISSION_VIEW, FALSE) )
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
        return FALSE;
      }
      
      //load data
      $this->m_aData[self::PROPERTY_PRODUCER_ID] = $nProducerId;
      $this->m_aData[self::PROPERTY_PRODUCER_NAMES] = $this->GetKeyStrings( $this->m_aData[self::PROPERTY_PRODUCER_ID] );
      $this->m_aData[self::PROPERTY_IS_DISABLED] = $rec["bDisabled"];
      $this->m_aData[self::PROPERTY_EXPORT_FILE_NAME] = $rec["sExportFileName"];
      
      //save original data
      $this->m_aOriginalData = $this->m_aData;
      
      return TRUE;
    }

    public function Add()
    {
        global $g_oMemberSession;
        global $g_sLangDir;
        
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

        //general permission check
        if ( !$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) 
            || 
            !$this->AddPermissionBridge(self::PERMISSION_ADD, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_ADD, 
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
          $this->m_bUseClassConnection = TRUE;
          
          $this->BeginTransaction();

          //create new string key
          $nKeyID = $this->NewKey();

          //insert strings
          $this->InsertStrings($this->m_aData[self::PROPERTY_PRODUCER_NAMES], $nKeyID);

          $sSQL = NULL;

          //insert the record
          $sSQL =  " INSERT INTO T_Producer( ProducerKeyID, bDisabled " .
                  $this->ConcatColIfNotNull(self::PROPERTY_EXPORT_FILE_NAME, "sExportFileName");
          if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
              $sSQL .= ", CoordinatingGroupID ";

          $sSQL .= " ) VALUES (" . $nKeyID . "," . intval($this->m_aData[self::PROPERTY_IS_DISABLED]);

          if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
             $sSQL .= ", " . $g_oMemberSession->CoordinatingGroupID;

          
          if ( $this->m_aData[self::PROPERTY_EXPORT_FILE_NAME] != NULL)
          {
             $sSQL .= ", :ExportFileName );"; 

             $this->RunSQLWithParams($sSQL, array("ExportFileName" => 
                 CoopOrderExport::remove_filename_special_char($this->m_aData[self::PROPERTY_EXPORT_FILE_NAME])));
          }
          else
          {
             $sSQL .= " ); ";

             $this->RunSQL($sSQL);
          }

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
        
        $this->m_aData[self::PROPERTY_PRODUCER_ID]  = $nKeyID;
        if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
           $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $g_oMemberSession->CoordinatingGroupID;
        
        $this->m_aOriginalData = $this->m_aData;

        return TRUE;
    }

    public function Edit()
    {
        global $g_oMemberSession;
        global $g_sLangDir;
        
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

        //permission check
        if (! $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE))
        
        if (!$this->Validate())
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
          return FALSE;
        }
        
        try
        {
          $this->BeginTransaction();

          $this->UpdateStrings(self::PROPERTY_PRODUCER_NAMES, $this->m_aData[self::PROPERTY_PRODUCER_ID]);

          //update record
          $sSQL = " UPDATE T_Producer SET sExportFileName = :ExportFileName, bDisabled = " . intval($this->m_aData[self::PROPERTY_IS_DISABLED]) .
                " WHERE ProducerKeyID = " . $this->m_aData[self::PROPERTY_PRODUCER_ID ] . " ;";
          $this->RunSQLWithParams( $sSQL, 
              array(  "ExportFileName" => CoopOrderExport::remove_filename_special_char($this->m_aData[self::PROPERTY_EXPORT_FILE_NAME]) ) 
           );
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
    
     public function CheckDeletePermission()
    {
       if ($this->HasPermission(self::PERMISSION_DELETE))
           return TRUE;

       return $this->AddPermissionBridge(self::PERMISSION_DELETE, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_DELETE, 
            Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
    }
    
    public function Delete()
    {
      global $g_oMemberSession;

      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
      if ($this->m_aData[self::PROPERTY_PRODUCER_ID ] == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
      }

      //permission check
      if (! $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) ||
              !$this->CheckDeletePermission())
      {
         $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
         return FALSE;
      }
      
      try
      {
        $this->BeginTransaction();

        //delete record
        $sSQL = " DELETE FROM T_Producer WHERE ProducerKeyID = ? ;";
        $this->RunSQLWithParams( $sSQL, array( $this->m_aData[self::PROPERTY_PRODUCER_ID ] ) );

        //delete key (foreign key cascading deletes strings)
        $this->DeleteKey( $this->m_aData[self::PROPERTY_PRODUCER_ID ] );
        
        $this->CommitTransaction();
      }
      catch(Exception $e)
      {
        $this->RollbackTransaction();
        throw $e;
      }
      
      $this->m_aData[self::PROPERTY_PRODUCER_ID ] = 0;

      return TRUE;
    }
    
    public function Validate()
    {
      global $g_oError;

      $bValid = TRUE;

      if (!$this->ValidateRequiredNames(self::PROPERTY_PRODUCER_NAMES, '<!$FIELD_PRODUCER_NAME$!>'))
        $bValid = FALSE;

      return $bValid;
    }
}
?>
