<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//base class for both writing and reading session data
abstract class UserSessionBase extends SQLBase
{
    const KEY_MEMBERID = 'MemberID';
    const KEY_NAME = 'Name';
    const KEY_ISLOGGEDIN = 'IsLoggedIn';
    const KEY_LANGID = 'LangID';
    const KEY_FALLING_LANGID = 'FallingLangID';
    const KEY_PAYMENT_METHOD = "PaymentMethod";
    const KEY_BALANCE = "Balance";
    const KEY_PERCENT_OVER_BALANCE = "PercentOverBalance";
    const KEY_JOINED_DATE = "JoinedOn";
    const KEY_HAS_PERMISSIONS = "HasPermissions";
    const KEY_IS_SYS_ADMIN = "IsSysAdmin";
    const KEY_IS_ONLY_MEMBER = "IsOnlyMember";
    const KEY_PERMISSIONS = "Permissions";
    const KEY_GROUPS = "Groups";
    const KEY_ROLES = "Roles";
    const KEY_LANGUAGES = "Languages";
    const KEY_COORDINATING_GROUP_ID = "CoordinatingGroupID"; //the group that represents this user alone
    const KEY_LAST_ERROR = "LastError";
    const KEY_EXPORT_FORMAT = "ExportFormat";

    protected $m_bHasPermissions = false;
    protected $m_bIsLoggedIn = false;
    protected $m_aRoles = NULL;

    public function __construct()
    {
      $this->m_aData = array(   self::KEY_NAME => NULL, 
                                self::KEY_MEMBERID => 0,
                                self::KEY_COORDINATING_GROUP_ID => 0,
                                self::KEY_IS_SYS_ADMIN => FALSE,
                                self::KEY_LANGID => 0,
                                self::KEY_FALLING_LANGID => 0,
                                self::KEY_PAYMENT_METHOD => 0,
                                self::KEY_JOINED_DATE => NULL,
                                self::KEY_BALANCE => 0,
                                self::KEY_PERCENT_OVER_BALANCE => 0,
                                self::KEY_GROUPS => NULL,
                                self::KEY_PERMISSIONS => NULL,
                                self::KEY_LANGUAGES => NULL,
                                self::KEY_IS_ONLY_MEMBER => FALSE,
                                self::KEY_LAST_ERROR => NULL,
                                self::KEY_EXPORT_FORMAT => DEFAULT_EXPORT_FORMAT,
            );
      if (session_id() == '')
        session_start ();
    }

    public function __get( $name ) {
        
        switch ( $name ) 
        {           
             case self::KEY_HAS_PERMISSIONS;
                return $this->m_bHasPermissions;
            case self::KEY_ISLOGGEDIN;
                return $this->m_bIsLoggedIn;
            case self::KEY_LANGID;
                if ( $this->CheckLanguageReload() )
                    $this->LoadLanguage();
                return $this->m_aData[$name];
            case self::KEY_FALLING_LANGID;
                if ( $this->CheckLanguageReload() )
                    $this->LoadLanguage();
                return $this->m_aData[$name];
            default:
                if (array_key_exists( $name, $this->m_aData))
                    return $this->m_aData[$name];
                $trace = debug_backtrace();
                throw new Exception(
                    'Undefined property via __get(): ' . $name .
                    ' in class '. get_class() .', file ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line']);
            break;
        }
    }
    
    //block parent's class set function
    public function __set( $name, $value ) {        
      $trace = debug_backtrace();
      trigger_error(
          'Undefined property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
    }

    public static function Close()
    {
        if (session_id() != '')
          session_write_close();
    }

    public function CheckLanguageReload()
    {
        global $g_sLangDir;
        return ($g_sLangDir === '' && $this->m_aData[self::KEY_LANGID] !== 0) || ($g_sLangDir !== '' && $this->m_aData[self::KEY_LANGID] === 0);
    }

    protected function LoadLanguage()
    {
        global $g_sLangDir;

        if ($g_sLangDir === '')
            $this->ClearLanguage();
        else
        {
            //load the language id
            $sSQL = "SELECT LangID, FallingLangID FROM Tlng_Language WHERE sPhpFolder = :folder ;";
            $this->RunSQLWithParams($sSQL, array("folder" => $g_sLangDir) );

            $result = $this->fetch();

            $this->m_aData[self::KEY_LANGID] = $result['LangID'];

            $_SESSION[ self::KEY_LANGID ] = $this->m_aData[self::KEY_LANGID];

            if ($result['FallingLangID'])
            {
                $this->m_aData[self::KEY_FALLING_LANGID] = $result['FallingLangID'];
                $_SESSION[ self::KEY_FALLING_LANGID ] = $this->m_aData[self::KEY_FALLING_LANGID];
            }

            unset($stmt);
        }
    }

    public function ClearLanguage()
    {
            $this->m_aData[self::KEY_LANGID] = 0;
            $this->m_aData[self::KEY_FALLING_LANGID] = 0;
            $_SESSION[ self::KEY_LANGID ] = 0;       
            $_SESSION[ self::KEY_FALLING_LANGID ] = 0;
    }
   
    ///ends PHP session and unsets it for the current user
    public function Logout( )
    {
        session_unset();
        $this->m_bIsLoggedIn = false;
    }
    
    //called when users' coordinating groups have changed
    public function LoadCoordinatingGroups()
    {
        //get user groups
        $sSQL = "SELECT CoordinatingGroupID FROM T_CoordinatingGroupMember WHERE MemberID = ? ORDER BY CoordinatingGroupID;";
        
        $this->RunSQLWithParams($sSQL, array( $this->m_aData[self::KEY_MEMBERID] ));

        $this->m_aData[self::KEY_GROUPS] = $this->fetchAllOneColumn();
        $_SESSION[ self::KEY_GROUPS] = $this->m_aData[self::KEY_GROUPS];
        unset($stmt);
    }
}

?>
