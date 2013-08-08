<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//the base class for any class that accesses the db
//adds transaction support to db operations (required, due to use of AUTOCOMMIT = FALSE in DBAccess connection create) 
//wraps PermissionBridgeSet
//provides SQL building helper functions
//includes basic data functions such as creation of a new key
//includes basic functions such as serialization of values for postback 
//includes function that are used across pages, to ease their access (just callling the parent class' methods)
abstract class SQLBase
{
    const POST_ACTION_NEW = 1;
    const POST_ACTION_SAVE = 2;
    const POST_ACTION_DELETE = 3;
    const POST_ACTION_SORT = 4;
    
    const PERMISSION_PAGE_ACCESS = 1;
    const PERMISSION_COORD = 2;
    const PERMISSION_DELETE = 3;
    const PERMISSION_COPY = 4;
    const PERMISSION_VIEW = 5;
    const PERMISSION_EDIT = 6;
    const PERMISSION_ADD = 7;
    const PERMISSION_COORD_SET = 8;

    const PROPERTY_ORIGINAL_VALUES = "OriginalValues";
    const PROPERTY_LAST_OPERATION_STATUS = "LastOperationStatus";
    const PROPERTY_USE_CLASS_CONNECTION = "UseClassConnection";
    const PROPERTY_COORDINATING_GROUP_ID = "CoordinatingGroupID";
    
    const PROPERTY_SORT_FIELD = "SortField";   
    const PROPERTY_SORT_ORDER = "SortOrder";
    const IND_SORT_FIELD_NAME = 1;
    const IND_SORT_FIELD_ORDER = 2;
    
    const PROPERTY_ID = "ID";
    const OPERATION_STATUS_NONE = 0x0;
    const OPERATION_STATUS_NO_PERMISSION = 0x1;
    const OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED = 0x2;
    const OPERATION_STATUS_LOAD_RECORD_FAILED = 0x4;
    const OPERATION_STATUS_CREATE_STRING_KEY_FAILED = 0x8;
    const OPERATION_STATUS_NO_CHANGES = 0x10;
    const OPERATION_STATUS_RECORD_UPDATED = 0x20;
    const OPERATION_STATUS_CURRENT_LANG_STRINGS_UPDATED = 0x40;
    const OPERATION_STATUS_OTHER_LANG_STRINGS_UPDATED = 0x80;
    const OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED = 0x100;
    const OPERATION_STATUS_PARAMETER_INCONSISTENT_WITH_DATA = 0x200;
    const OPERATION_STATUS_REQUIRED_FIELD_MISSING = 0x400;
    const OPERATION_STATUS_NO_LIST_ITEM_SELECTED = 0x800;
    const OPERATION_STATUS_PERMISSION_AREA_NOT_LOADED = 0x1000;
    const OPERATION_STATUS_CANT_REMOVE_OWN_PERMISSION = 0x2000;
    const OPERATION_STATUS_VALIDATION_FAILED = 0x4000;

    protected $m_nLastOperationStatus = 0;
  
    protected $m_oSqlPreparedStmt = NULL;
    protected $m_oSqlPreparedStmt2 = NULL;
    
    protected $m_aDefaultData = NULL;
    protected $m_aData = NULL;
    protected $m_aOriginalData = NULL;
    
    protected $m_bUseClassConnection = FALSE; 
    protected $m_bUseSecondSqlPreparedStmt = FALSE;
    protected $m_bAutoCommitTransaction = TRUE; //flag for transaction support. False whenever begining a transaction (which is always)
    protected $m_oDBAccess = NULL;
    
    protected $m_oPermissionBridgeSet = NULL;
    
    protected $m_aSortFields = NULL;

    public function __get( $name ) {
      switch ($name)
      {
        case self::PROPERTY_LAST_OPERATION_STATUS:
          return $this->m_nLastOperationStatus;
        case self::PROPERTY_ORIGINAL_VALUES:
          return $this->m_aOriginalData;
        default:
          if ( array_key_exists( $name, $this->m_aData) )
            return $this->m_aData[$name];
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __get(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
          break;
      }
    }
    
    public function __set( $name, $value ) {        
      if ($name == self::PROPERTY_USE_CLASS_CONNECTION)
      {
        $this->m_bUseClassConnection = $value;
        return;
      }
      else if ($name == self::PROPERTY_ORIGINAL_VALUES)
      {
          $this->m_aOriginalData = $value;
          return;
      }
      else if (array_key_exists( $name, $this->m_aData))
      {
          $this->m_aData[$name] = $value;
           return;
      }
      $trace = debug_backtrace();
      trigger_error(
          'Undefined property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
    }
    
    public function GetSerializedData()
    {
      if ($this->m_aData == NULL)
        return '';
      return base64_encode(serialize($this->m_aData));
    }
    
    public function SetSerializedData($sSerializedArray)
    {
      $this->m_aData = unserialize(base64_decode($sSerializedArray));
    }
    
    public function GetSerializedOriginalData()
    {
      if ($this->m_aOriginalData == NULL)
        return '';
      return base64_encode(serialize($this->m_aOriginalData));
    }
    
    public function SetSerializedOriginalData($sSerializedArray)
    {
      $this->m_aOriginalData = unserialize(base64_decode($sSerializedArray));
    }
    
    protected function InitPermissionBridgeSet()
    {
      if ($this->m_oPermissionBridgeSet == NULL)
      {
        $this->m_oPermissionBridgeSet = new PermissionBridgeSet;
        return TRUE;
      }
      return FALSE;
    }
    
    //$nScopes - must be the scopes that are to check
    //$nRecordGroupID - can be 0 if not checking real group permission, but only access
    protected function AddPermissionBridge($nID, $nArea, $nType, $nScopes, $nRecordGroupID, $bAllowNoGroup)
    {
      $bUseKey = ($this->m_aData != NULL && array_key_exists(self::PROPERTY_COORDINATING_GROUP_ID, $this->m_aData));
      
      if ($nRecordGroupID > 0 && $bUseKey && $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] == 0)
        $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $nRecordGroupID;
      
      if (!$this->InitPermissionBridgeSet() && $this->m_oPermissionBridgeSet->HasPermission($nID))
      {
        if ($bUseKey)
          return $this->AddPermissionBridgeGroupID($nID, $bAllowNoGroup);
        else
          return $this->m_oPermissionBridgeSet->SetRecordGroupID($nID,$nRecordGroupID, $bAllowNoGroup);
      }
      
      return $this->m_oPermissionBridgeSet->DefinePermissionBridge($nID, $nArea, $nType, $nScopes, $nRecordGroupID, $bAllowNoGroup);
    }
    
    protected function AddPermissionBridgeGroupID($nID, $bAllowNoGroup)
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        throw new Exception (
          sprintf('Error in AddPermissionBridgeGroupID: PermissionBridgeSet is not set. ID: %d',$nID));
      
      //coordinating group permission check
      if ($this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID] > 0 && $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] == 0)
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID]  = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];

      return $this->m_oPermissionBridgeSet->SetRecordGroupID($nID,$this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], $bAllowNoGroup);
    }
    
    public function SetRecordGroupID($nID, $nRecordGroupID, $bAllowNoGroup)
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        throw new Exception (
          sprintf('Error in SetRecordGroupID: PermissionBridgeSet is not set. ID: %d. Group: %d',$nID, $nRecordGroupID));
      
      return $this->m_oPermissionBridgeSet->SetRecordGroupID($nID, $nRecordGroupID, $bAllowNoGroup);
    }
    
    public function GetPermissionScope($nID)
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        throw new Exception (
          sprintf('Error in GetPermissionScope: PermissionBridgeSet is not set. ID: %d',$nID));
      
      return $this->m_oPermissionBridgeSet->GetPermissionScope($nID);
    }
    
    public function HasPermission($nID)
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        return FALSE;
      
      return $this->m_oPermissionBridgeSet->HasPermission($nID);
    }
    
    public function HasAnyPermission()
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        return FALSE;
      
      return $this->m_oPermissionBridgeSet->HasAnyPermission();
    }
    
    public function HasPermissions($arrIDs)
    {
      if ($this->m_oPermissionBridgeSet == NULL)
        return FALSE;
      
      return $this->m_oPermissionBridgeSet->HasPermissions($arrIDs);
    }
    
    public function CopyPermission($nSourceID, $nDestID)
    {
       if ($this->m_oPermissionBridgeSet == NULL)
        throw new Exception ('Error in CopyPermission: PermissionBridgeSet is not set');
      
      return $this->m_oPermissionBridgeSet->CopyPermission($nSourceID, $nDestID);
      
    }
    
    protected function OpenConnection()
    {
      if ($this->m_oDBAccess == NULL)
      {
        $this->m_oDBAccess = new DBAccess;
        $this->m_oDBAccess->ConnectNonPersist();
      }
    }
    
    public function CloseConnection()
    {
      if ($this->m_oDBAccess != NULL)
        $this->m_oDBAccess->Close();
      $this->m_oDBAccess = NULL;
    }
    
    public function BeginTransaction()
    {
      global $g_oDBAccess;
      
      $this->m_bAutoCommitTransaction = FALSE;
      
      if ($this->m_bUseClassConnection)
      {
        $this->OpenConnection();
        
        if (!$this->m_oDBAccess->Connection->inTransaction())
          $this->m_oDBAccess->Connection->beginTransaction();
      }
      else
      {
        $g_oDBAccess->Connect();
        
        if (!$g_oDBAccess->Connection->inTransaction())
          $g_oDBAccess->Connection->beginTransaction();
      }
    }
    
    
    public function CommitTransaction()
    {
      $this->Commit();
      $this->m_bAutoCommitTransaction = TRUE;
    }
    
    protected function Commit()
    {
      global $g_oDBAccess;
      if ($this->m_bUseClassConnection)
      {
        if ($this->m_oDBAccess->Connection->inTransaction())
            $this->m_oDBAccess->Connection->commit();
      }
      else
      {        
        if ($g_oDBAccess->Connection->inTransaction())
            $g_oDBAccess->Connection->commit();
      }
    }
    
    public function RollbackTransaction()
    {
      global $g_oDBAccess;
      
      if ($this->m_bUseClassConnection)
      {
        if ($this->m_oDBAccess->Connection->inTransaction())
          $this->m_oDBAccess->Connection->rollback();
      }
      else
      {
        if ($g_oDBAccess->Connection->inTransaction())
          $g_oDBAccess->Connection->rollback();
      }
      
      $this->m_bAutoCommitTransaction = TRUE;
    }
    
    private function PrepareSQL($sSQL)
    {
      global $g_oDBAccess;
        
      if ($this->m_bUseClassConnection)
      {
        $this->OpenConnection();
        
        if (!$this->m_oDBAccess->Connection->inTransaction())
          $this->m_oDBAccess->Connection->beginTransaction();
        
        if ($this->m_bUseSecondSqlPreparedStmt)
          $this->m_oSqlPreparedStmt2 = $this->m_oDBAccess->Connection->prepare( $sSQL );
        else
          $this->m_oSqlPreparedStmt = $this->m_oDBAccess->Connection->prepare( $sSQL );
      }
      else
      {
        $g_oDBAccess->Connect();
        
        if (!$g_oDBAccess->Connection->inTransaction())
          $g_oDBAccess->Connection->beginTransaction();
        
        if ($this->m_bUseSecondSqlPreparedStmt)
          $this->m_oSqlPreparedStmt2 = $g_oDBAccess->Connection->prepare( $sSQL );
        else
          $this->m_oSqlPreparedStmt = $g_oDBAccess->Connection->prepare( $sSQL );
      }
    }

    protected function RunSQL($sSQL)
    {
        $this->PrepareSQL($sSQL);
        if ($this->m_bUseSecondSqlPreparedStmt)
          $this->m_oSqlPreparedStmt2->execute( ) ;
        else
          $this->m_oSqlPreparedStmt->execute( ) ;
        
        if ($this->m_bAutoCommitTransaction)
          $this->Commit();
    }
    
    protected function RunSQLWithParams($sSQL, $aParams)
    {
        $this->PrepareSQL($sSQL);
        if ($this->m_bUseSecondSqlPreparedStmt)
          $this->m_oSqlPreparedStmt2->execute( $aParams ) ;
        else
          $this->m_oSqlPreparedStmt->execute( $aParams ) ;
        
        if ($this->m_bAutoCommitTransaction)
          $this->Commit();
    }

    public function fetch()
    {
      if ($this->m_bUseSecondSqlPreparedStmt)
        return $this->m_oSqlPreparedStmt2->fetch( PDO::FETCH_ASSOC );
      
      return $this->m_oSqlPreparedStmt->fetch( PDO::FETCH_ASSOC );
    }
    
    protected function fetchAll()
    {
      if ($this->m_bUseSecondSqlPreparedStmt)
        return $this->m_oSqlPreparedStmt2->fetchAll( );
      
      return $this->m_oSqlPreparedStmt->fetchAll( );
    }
    
    protected function fetchAllOneColumn()
    {
      if ($this->m_bUseSecondSqlPreparedStmt)
        return $this->m_oSqlPreparedStmt2->fetchAll(PDO::FETCH_COLUMN, 0 );
      
      return $this->m_oSqlPreparedStmt->fetchAll(PDO::FETCH_COLUMN, 0 );
    }
    
    protected function fetchAllKeyPair()
    {
      if ($this->m_bUseSecondSqlPreparedStmt)
        return $this->m_oSqlPreparedStmt2->fetchAll( PDO::FETCH_KEY_PAIR );
      
      return $this->m_oSqlPreparedStmt->fetchAll( PDO::FETCH_KEY_PAIR );
    }

    protected function NewKey()
    {
        $sSQL =  " INSERT INTO T_Key(sStringKey) VALUES(NULL);"; 
        $this->RunSQL( $sSQL );
        return $this->GetLastInsertedID();
    }
    
    protected function GetLastInsertedID()
    {
      $sSQL =  " SELECT LAST_INSERT_ID() as nKey;";
      $this->RunSQL( $sSQL );
      $rec = $this->fetch();

      if ($rec)
        return $rec["nKey"];
      return NULL;
    }

    protected function DeleteKey($nKeyID)
    {
        $sSQL =  "DELETE FROM T_Key Where KeyID = " . $nKeyID . ";";

        $this->RunSQL( $sSQL );
    }

    protected function GetKeyString($nKeyID)
    {
        global $g_nCurrentLanguageID;
        global $g_nFallingLanguageID;
      
        $sSQL =  "SELECT ";
        if ($g_nCurrentLanguageID > 0 && $g_nFallingLanguageID > 0)
            $sSQL .=         " IfNull(S.sString,SF.sString) as sString ";
        else       
            $sSQL .=         " S.sString ";

        $sSQL .=  " From T_Key K " .
                         " LEFT JOIN Tlng_String S " .
                                " ON S.KeyID = K.KeyID ";
        if ($g_nCurrentLanguageID > 0)
        {
            $sSQL .=     " AND S.LangID = " . $g_nCurrentLanguageID;
            if ($g_nFallingLanguageID > 0)
            {
            $sSQL .=     " LEFT JOIN Tlng_String SF " .
                                " ON SF.KeyID = K.KeyID " .
                                " AND SF.LangID = " . $g_nFallingLanguageID;
            }
        }

        $sSQL .=  " Where K.KeyID = " . $nKeyID . ';';

        $this->RunSQL( $sSQL );

        $rec = $this->fetch();

        if ($rec)
          return $rec["sString"];

        return NULL;
    }
    
    protected function GetKeyStrings($nKeyID)
    {
      global $g_sLangDir;
      
      if ($nKeyID == NULL || $nKeyID <= 0)
        return NULL;
      
      $bMultiLang = ($g_sLangDir != '');
      
      $sSQL =   "SELECT ";
      if ($bMultiLang)
        $sSQL .= " L.sPhpFolder,";
      
      $sSQL .=  " S.sString ";
      $sSQL .=  " From T_Key K " .
                " LEFT JOIN Tlng_String S " .
                " ON S.KeyID = K.KeyID ";
      if ($bMultiLang)
      {
          $sSQL .= " LEFT JOIN Tlng_Language L ";
          $sSQL .= " ON S.LangID = L.LangID ";
      }

      $sSQL .=  " Where K.KeyID = " . $nKeyID . ';';

      $this->RunSQL( $sSQL );
      
      if ($bMultiLang)
        return $this->fetchAllKeyPair();
      else
        return $this->fetchAllOneColumn();
    }
    
    protected function UpdateString($nKeyID, $nLangID, $sString)
    {
      if ($nKeyID == 0)
        throw new Exception("UpdateString failed. String key was not set for update operation of: " . $sString);
      
      $sSQLWhere = " WHERE KeyID = :Key AND LangID = :Lang ;";
      
      $sSQLQuery = "SELECT COUNT(*) as nCount FROM Tlng_String " . $sSQLWhere;
      $this->RunSQLWithParams( $sSQLQuery, array("Key" => $nKeyID, "Lang" => $nLangID) );
      $res = $this->fetch();
      if (!isset($res) || $res["nCount"] == 0)
      {
        $this->InsertString ($nKeyID, $nLangID, $sString);
        return;
      }
      
      $sSQLUpdate = " UPDATE Tlng_String SET sString = :String " .
                  $sSQLWhere;
      $this->RunSQLWithParams( $sSQLUpdate, array("String" => $sString, "Key" => $nKeyID, "Lang" => $nLangID) );
    }
    
    protected function UpdateStrings($nDataIndex, $nKeyID)
    {
      global $g_oMemberSession;
      
      if ($g_oMemberSession->LangID > 0)
      {
        foreach($this->m_aData[$nDataIndex] as $key => $str)
        {
          $this->UpdateString($nKeyID, $g_oMemberSession->GetLangIDByKey($key), $str);
        }
      }
      else
        $this->UpdateString($nKeyID, $g_oMemberSession->GetLangIDByKey(''), $this->m_aData[$nDataIndex][0]);
    }
    
    protected function InsertString($nKeyID, $nLangID, $sString)
    {
      if ($nKeyID == 0)
        throw new Exception("InsertString failed. String key was not set for insert operation of: " . $sString);
      
      $sSQL =  " INSERT INTO Tlng_String( KeyID, LangID, sString ) ";
      $sSQL .= " VALUES (:key , :lang , :str ); ";
      $this->RunSQLWithParams( $sSQL, array("key" => $nKeyID, "lang" => $nLangID, "str" => $sString ) );
    }
    
    protected function InsertStrings($aArrStrings, $nKeyID)
    {
      global $g_oMemberSession;
      if (is_array($aArrStrings) && count($aArrStrings) > 0)
      {  
        foreach($aArrStrings as $sLang => $sValue)
        {
          $nLangID = $g_oMemberSession->GetLangIDByKey($sLang);
          if ( $nLangID > 0 )
            $this->InsertString($nKeyID, $nLangID, $sValue);
        }
      }
    }
    
    // table alias + _S suffix means the main langauge strings table
    // table alias + _F suffix means the falling langauge strings table
    protected function ConcatStringsJoin($nArea)
    {
      global $g_nCurrentLanguageID;
      global $g_nFallingLanguageID;
      
      $oArea = new PermissionArea($nArea);

      $sSQL =  " LEFT JOIN Tlng_String " . $oArea->TableAlias .
      "_S ON " . $oArea->TableAlias . "_S.KeyID = " . $oArea->TableAlias . "." . $oArea->TablePrimaryKey;
      if ($g_nCurrentLanguageID > 0)
      {
          $sSQL .=     " AND " . $oArea->TableAlias . "_S.LangID = " . $g_nCurrentLanguageID;
          if ($g_nFallingLanguageID > 0)
          {
          $sSQL .=     " LEFT JOIN Tlng_String " . $oArea->TableAlias  . "_F " .
                              " ON " . $oArea->TableAlias  . "_F.KeyID = " . $oArea->TableAlias . "." . $oArea->TablePrimaryKey .
                              " AND " . $oArea->TableAlias  . "_F.LangID = " . $g_nFallingLanguageID;
          }
      }
        
      $sSQL .= ' ';

      unset($oArea);
        
      return $sSQL;
    }
    
    // table alias + _S suffix means the main langauge strings table
    // table alias + _F suffix means the falling langauge strings table
    protected function ConcatForeignStringsJoin($nArea, $nForeignArea)
    {
      global $g_nCurrentLanguageID;
      global $g_nFallingLanguageID;
      
      $oArea = new PermissionArea($nArea);
      
      $oForeignArea = new PermissionArea($nForeignArea);

      $sSQL =  " LEFT JOIN Tlng_String " . $oArea->TableAlias .
      "_S ON " . $oArea->TableAlias . "_S.KeyID = " . $oForeignArea->TableAlias . "." . $oArea->TablePrimaryKey;
      if ($g_nCurrentLanguageID > 0)
      {
          $sSQL .=     " AND " . $oArea->TableAlias . "_S.LangID = " . $g_nCurrentLanguageID;
          if ($g_nFallingLanguageID > 0)
          {
          $sSQL .=     " LEFT JOIN Tlng_String " . $oArea->TableAlias  . "_F " .
                              " ON " . $oArea->TableAlias  . "_F.KeyID = " . $oForeignArea->TableAlias . "." . $oArea->TablePrimaryKey .
                              " AND " . $oArea->TableAlias  . "_F.LangID = " . $g_nFallingLanguageID;
          }
      }
        
      $sSQL .= ' ';

      unset($oArea);
      unset($oForeignArea);
        
      return $sSQL;
    }
    
    protected function ConcatStringsSelect($nArea, $sAlias)
    {
      global $g_nCurrentLanguageID;
      global $g_nFallingLanguageID;
      
      $oArea = new PermissionArea($nArea);
      $sSQL =  '';
      if ($g_nCurrentLanguageID > 0 && $g_nFallingLanguageID > 0)
            $sSQL .= " IfNull(" . $oArea->TableAlias . "_S.sString," . $oArea->TableAlias  . "_F.sString) ";
        else       
            $sSQL .= $oArea->TableAlias . "_S.sString ";

      $sSQL .=  " as " . $sAlias;
      unset($oArea);

      return $sSQL;
    }

    protected function ConcatColIfNotNull($nDataIndex, $sColumnName)
    {
      if ($this->m_aData[$nDataIndex] !== NULL)
        return ',' . $sColumnName;
      return '';
    }
    
    protected function ConcatValIfNotNull($nDataIndex)
    {
      if ($this->m_aData[$nDataIndex] !== NULL)
        return ',' . $this->m_aData[$nDataIndex];
      return '';
    }
    
    protected function ConcatColIfNotValue($nDataIndex, $sColumnName, $oNotValue)
    {
      if ($this->m_aData[$nDataIndex] != $oNotValue)
        return ',' . $sColumnName;
      return '';
    }
    
    protected function ConcatValIfNotValue($nDataIndex, $oNotValue)
    {
      if ($this->m_aData[$nDataIndex] != $oNotValue)
        return ',' . $this->m_aData[$nDataIndex];
      return '';
    }
        
    public function SwitchSort($nSortFieldID)
    {      
      $this->m_aData[self::PROPERTY_SORT_FIELD] = $nSortFieldID;
      
      //decide on sort order by checking what was the previous one
      if ($this->m_aData[self::PROPERTY_SORT_FIELD] == $this->m_aOriginalData[self::PROPERTY_SORT_FIELD])
      {
        //if user has clicked again on the same field: switch its order, whatever it is
        if ($this->m_aOriginalData[self::PROPERTY_SORT_ORDER] == Consts::SORT_ORDER_ASCENDING)
          $this->m_aData[self::PROPERTY_SORT_ORDER] = Consts::SORT_ORDER_DESCENDING;
        else
          $this->m_aData[self::PROPERTY_SORT_ORDER] = Consts::SORT_ORDER_ASCENDING;
      }
      else
      {
        //get the defualt order for the field
        $this->m_aData[self::PROPERTY_SORT_ORDER] = $this->m_aSortFields[$nSortFieldID][self::IND_SORT_FIELD_ORDER]; 
        
      }
    }
    
    public function ConcatSortSQL()
    {
      $sSort = NULL;
      if ($this->m_aData[self::PROPERTY_SORT_ORDER] == Consts::SORT_ORDER_DESCENDING)
        $sSort = " DESC ";
      else
        $sSort = " ASC ";
      
      return " ORDER BY " . $this->m_aSortFields[$this->m_aData[self::PROPERTY_SORT_FIELD]][self::IND_SORT_FIELD_NAME] . $sSort;
    }
    
    public function PreserveSort()
    {
      $this->m_aData[self::PROPERTY_SORT_FIELD] = $this->m_aOriginalData[self::PROPERTY_SORT_FIELD];
      $this->m_aData[self::PROPERTY_SORT_ORDER] = $this->m_aOriginalData[self::PROPERTY_SORT_ORDER];
    }
    
    protected function ValidateRequiredNames($nFieldIndex, $sFieldName)
    {
      global $g_sLangDir;
      global $g_oError;
      global $g_aSupportedLanguages;
      
      if (!is_array($this->m_aData[$nFieldIndex]))
      {
        $g_oError->AddError(sprintf('%s is required.', $sFieldName));
        return FALSE;
      }
      else if ($g_sLangDir != '')
      {
        if ($this->m_aData[$nFieldIndex][$g_sLangDir] == NULL)
        {
          $g_oError->AddError(sprintf('%s is required.', $sFieldName));
          return FALSE;
        }

        //check other languages
        foreach($g_aSupportedLanguages as $Lkey => $aLang)
        {
          if ($aLang[Consts::IND_LANGUAGE_REQUIRED] && $this->m_aData[$nFieldIndex][$Lkey] == NULL)
          {
            $g_oError->AddError(sprintf('%s is required.', $sFieldName));
            return FALSE;
          }
        }
      }
      else if ($this->m_aData[$nFieldIndex][0] == NULL) //one-language deployment
      {
        $g_oError->AddError(sprintf('%s is required.', $sFieldName));
        return FALSE;
      }
      
      return TRUE;
    }
    
    
    protected function GetGroupContactPersons($GroupID)
    {
      $sSQL = " SELECT M.sName, M.sEMail FROM T_Member M INNER JOIN T_CoordinatingGroupMember CGM ON CGM.MemberID = M.MemberID " .
              " WHERE CGM.CoordinatingGroupID = " . $GroupID . " AND bContactPerson = 1;";
      
      $this->RunSQL($sSQL);
      
      return $this->fetchAll();
    }
    
    protected function GetLangPropertyVal($PropertyID, $sLang)
    {
      if ( isset( $this->m_aData[$PropertyID][$sLang] ))
        return $this->m_aData[$PropertyID][$sLang];
      
      return '';
    }
    
    protected function AddDatePreserveDW($PropertyID, $di)
    {
      $orig = clone $this->m_aData[$PropertyID];
      $this->m_aData[$PropertyID]->add($di);
      
      $days = ($this->m_aData[$PropertyID]->format('w')+0) - ($orig->format('w')+0);
      
      $this->m_aData[$PropertyID]->sub(new DateInterval('P' . $days . 'D'));
    }
    
    protected function IfEmpty($value, $replace) {
        if (empty($value)) {
            return $replace;
        }
        return $value;
    }
}
?>
