<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

include_once APP_DIR .'/class/UserSessionBase.php';

//writes to the session once login has succeeded
class SessionManager extends UserSessionBase
{
    //starts the PHP session to enable setting data to it
    public function __construct()
    {
        parent::__construct();
    }

    public function __set( $name, $value ) {
      global $g_oTimeZone;
        switch( $name )
        {
             case parent::KEY_JOINED_DATE;
                 $this->m_aData[$name] = new DateTime($value, $g_oTimeZone);
                 $_SESSION[ $name ] = $this->m_aData[$name];     
                break;
             case parent::KEY_MEMBERID;
             case parent::KEY_NAME;
             case parent::KEY_PAYMENT_METHOD;
             case parent::KEY_BALANCE;
             case parent::KEY_PERCENT_OVER_BALANCE:
             case parent::KEY_COORDINATING_GROUP_ID;
              $this->m_aData[$name] = $value;
              $_SESSION[ $name ] = $value;
              break;
            default:
                $trace = debug_backtrace();
                throw new Exception(
                    'Undefined property via __set(): ' . $name .
                    ' in class '. get_class() .', file ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line']);
            break;
        }
    }
    
    public function LoadPermissions()
    {       
        //get user main ccordinating group
                                " INNER JOIN T_CoordinatingGroupMember CGM ON CGM.MemberID = MR.MemberID " .
                                        
        $sSQL = "SELECT CG.CoordinatingGroupID FROM T_CoordinatingGroupMember CGM " . 
          " INNER JOIN T_CoordinatingGroup CG ON CG.CoordinatingGroupID = CGM.CoordinatingGroupID " .
          " WHERE CGM.MemberID = ? AND CG.sCoordinatingGroup IS NULL ;";
        $this->RunSQLWithParams($sSQL, array( $this->m_aData[parent::KEY_MEMBERID] ));
        
        $rec = $this->fetch( );
        
        //every user got to have a system coordinating group that allows setting hir as coordinator
        if (!$rec || $rec[parent::KEY_COORDINATING_GROUP_ID] == NULL)
        {
            //m_bHasPermissions is already false, so keep it that way when there is no system coordinating group
            unset($stmt);
            $this->Logout( );
            return;
        }
        
        $this->m_aData[parent::KEY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];

        //get user roles
        $sSQL = "SELECT RoleKeyID FROM T_MemberRole WHERE MemberID = ? ;";
        $this->RunSQLWithParams($sSQL, array( $this->m_aData[parent::KEY_MEMBERID] ) ) ;

        $this->m_aRoles = $this->fetchAllOneColumn();

        $nCountRoles = 0;
        if (is_array($this->m_aRoles))
          $nCountRoles = count( $this->m_aRoles );
        if ( $nCountRoles == 0 )
        {
            //m_bHasPermissions is already false, so keep it that way when there are no roles
            unset($stmt);
            $this->Logout( );
            return;
        }
        $this->m_aData[parent::KEY_IS_SYS_ADMIN] = in_array(Consts::ROLE_SYSTEM_ADMIN, $this->m_aRoles);
        $_SESSION[ self::KEY_IS_SYS_ADMIN] = $this->m_aData[parent::KEY_IS_SYS_ADMIN];
        if($this->m_aData[parent::KEY_IS_SYS_ADMIN]) //no need to load permissions for admins
        {
            $this->m_bHasPermissions = true; //this value is relevant only in login, so no need to store in session
            unset($stmt);
            return;
        }
        
        //check if only member, to speed up coordination areas blocking
        $this->m_aData[parent::KEY_IS_ONLY_MEMBER] = ( $nCountRoles == 1 && in_array(Consts::ROLE_MEMBER, $this->m_aRoles) );
        $_SESSION[ self::KEY_IS_ONLY_MEMBER] = $this->m_aData[parent::KEY_IS_ONLY_MEMBER];

        $sSQL =  " SELECT RP.PermissionAreaKeyID, RP.PermissionTypeKeyID, MIN(PD.nScopeCode) as nScopeCode FROM T_MemberRole MR " . 
                        " INNER JOIN T_RolePermission RP ON RP.RoleKeyID = MR.RoleKeyID " .
                        " INNER JOIN T_PermissionScope PD ON PD.PermissionScopeKeyID = RP.PermissionScopeKeyID " .
                        " WHERE MR.MemberID = ? " .
                        " GROUP BY  RP.PermissionAreaKeyID, RP.PermissionTypeKeyID " .
                        " ORDER BY  RP.PermissionAreaKeyID, RP.PermissionTypeKeyID;";

        $this->RunSQLWithParams($sSQL, array( $this->m_aData[parent::KEY_MEMBERID] ) ) ;

        $this->m_aData[parent::KEY_PERMISSIONS] = $this->fetchAll( );

        if ($this->m_aData[parent::KEY_PERMISSIONS] == NULL || count($this->m_aData[parent::KEY_PERMISSIONS]) == 0)
        {
            //m_bHasPermissions is already false, so keep it that way when there are no roles
            unset($stmt);
            $this->Logout( );
            return;
        }
        $_SESSION[ self::KEY_PERMISSIONS] = $this->m_aData[parent::KEY_PERMISSIONS];
        $this->m_bHasPermissions = true; //this value is relevant only in login, so no need to store in session

        unset($stmt);        
    }

    public function LoadUserSession()
    {
        $this->LoadLanguage();
        $this->LoadCoordinatingGroups();
        $this->LoadLanguages();
    }

    
    protected function LoadLanguages()
    {
        //get user groups
        $sSQL = "SELECT LangID, sPhpFolder, sLanguage, bRequired, FallingLangID FROM Tlng_Language WHERE bActive = 1 ;";
        $this->RunSQL($sSQL ) ;

        $this->m_aData[parent::KEY_LANGUAGES] = $this->fetchAll( );
        $_SESSION[ self::KEY_LANGUAGES] = $this->m_aData[parent::KEY_LANGUAGES];
        unset($stmt);
    }
}
?>
