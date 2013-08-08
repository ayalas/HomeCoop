<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitate a permission check through UserSession::PermissionScope and UserSession::UserInGroup
class PermissionBridge {

const PROPERTY_PERMISSION_AREA = "PermissionArea";
const PROPERTY_PERMISSION_TYPE = "PermissionType";
const PROPERTY_PERMISSION_SCOPES = "PermissionScopes";
const PROPERTY_ALLOW_NO_RECORD_GROUP = "AllowNoRecordGroup";
const PROPERTY_COORDINATING_GROUP_ID = "RecordGroupID";
const PROPERTY_RESULT = "Result";
const PROPERTY_RESULT_SCOPE = "ResultScope";

protected $m_aData = NULL;

 public function __construct($nArea, $nType, $nScopes, $nRecordGroupID, $bAllowNoRecordGroup)
  {
    global $g_oMemberSession;
    
    $this->m_aData = array( self::PROPERTY_PERMISSION_AREA => $nArea,
                            self::PROPERTY_PERMISSION_TYPE => $nType,
                            self::PROPERTY_PERMISSION_SCOPES => $nScopes,
                            self::PROPERTY_COORDINATING_GROUP_ID => $nRecordGroupID,
                            self::PROPERTY_RESULT => FALSE,
                            self::PROPERTY_RESULT_SCOPE => 0,
                            self::PROPERTY_ALLOW_NO_RECORD_GROUP => $bAllowNoRecordGroup
                            );
    //perform the permission check
    $this->m_aData[self::PROPERTY_RESULT_SCOPE] = $g_oMemberSession->PermissionScope($nArea, $nType);
    //analize results
    $this->CheckScope();
  }
  
  //analize results of the permission check performed in the constructor
  public function CheckScope()
  {
    global $g_oMemberSession;
    //user has only group scope - so should check specific-record group permissions
    if ($this->m_aData[self::PROPERTY_RESULT_SCOPE] == Consts::PERMISSION_SCOPE_GROUP_CODE)
    {
      //see if group permission is at all acceptable
      if (($this->m_aData[self::PROPERTY_RESULT_SCOPE] & $this->m_aData[self::PROPERTY_PERMISSION_SCOPES]) > 0)
      {
        //has group id?
        if ($this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] > 0)
          //check permission for the specific record, according to its coordinating group id
          $this->m_aData[self::PROPERTY_RESULT] = $g_oMemberSession->UserInGroup($this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID]);
        else //return default value when no group id is provided
          $this->m_aData[self::PROPERTY_RESULT] = $this->m_aData[self::PROPERTY_ALLOW_NO_RECORD_GROUP];
      }
    }
    else if ($this->m_aData[self::PROPERTY_RESULT_SCOPE] == Consts::PERMISSION_SCOPE_COOP_CODE)
    { 
      //user has coop-wide permission for this permission area and type
      //if this is what was searched for (in the SCOPES parameter. normally, it would have been), return TRUE
      if (($this->m_aData[self::PROPERTY_RESULT_SCOPE] & $this->m_aData[self::PROPERTY_PERMISSION_SCOPES]) > 0)
        $this->m_aData[self::PROPERTY_RESULT] = TRUE;
    }
    //return the result (defaults to FALSE)
    return $this->m_aData[self::PROPERTY_RESULT];
  }
  
  public function __get( $name ) {
    if ( array_key_exists( $name, $this->m_aData) )
      return $this->m_aData[$name];
    $trace = debug_backtrace();
    throw new Exception(
        'Undefined property via __get(): ' . $name .
        ' in class '. get_class() .', file ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line']);
  }
    
  public function __set( $name, $value ) {        
    if ( $name != self::PROPERTY_RESULT &&
         $name != self::PROPERTY_RESULT_SCOPE &&   
         array_key_exists( $name, $this->m_aData) )
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
  
}

?>
