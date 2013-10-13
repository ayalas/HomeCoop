<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitate login to the system
class Login extends SQLBase
{
    const PROPERTY_USER_SESSION = "SessionManager";
    const PROPERTY_LOGIN_NAME = "LoginName";
    const PROPERTY_PASSWORD = "Password";
    
    //login error codes
    const SUCCESS = 0;
    const ERR_LOGIN_NAME_EMPTY = 1;
    const ERR_LOGIN_PASSWORD_EMPTY = 2;
    const ERR_LOGIN_INCORRECT_NAME_PASSWORD = 3;
    const ERR_NO_PERMISSIONS = 4;
    const ERR_MEMBER_DISABLED = 5;

    protected $m_oSessionManager = null;
    
    public function __construct()
    {
      $this->m_aData = array( 
                              self::PROPERTY_LOGIN_NAME => NULL,
                              self::PROPERTY_PASSWORD => NULL
                              );
    }

    public function __get( $name ) {
        switch ( $name ) 
        {
            case  self::PROPERTY_USER_SESSION;
                return  $this->m_oSessionManager;
            default:
                $trace = debug_backtrace();
                throw new Exception(
                    'Undefined property via __get(): ' . $name .
                    ' in class '. get_class() .', file ' . $trace[0]['file'] .
                    ' on line ' . $trace[0]['line']);
            break;
        }
    }

    public function DoLogin ()
    {
        //first check for must fields
        if ( !isset($this->m_aData[self::PROPERTY_LOGIN_NAME]) || $this->m_aData[self::PROPERTY_LOGIN_NAME] == "")
            return self::ERR_LOGIN_NAME_EMPTY;

        if ( !isset($this->m_aData[self::PROPERTY_PASSWORD]) || $this->m_aData[self::PROPERTY_PASSWORD] == "")
            return self::ERR_LOGIN_PASSWORD_EMPTY;
        
        try
        {
          $sSQL = "SELECT M.sName, M.MemberID, M.PaymentMethodKeyID, M.bDisabled, M.fPercentOverBalance, " . 
                  " M.dJoined, M.mBalance, CG.CoordinatingGroupID, M.nExportFormat " . 
                  " FROM T_Member M INNER JOIN T_CoordinatingGroupMember CGM ON CGM.MemberID = M.MemberID " . 
                  " INNER JOIN T_CoordinatingGroup CG ON CG.CoordinatingGroupID = CGM.CoordinatingGroupID AND CG.sCoordinatingGroup IS NULL " .
                  " WHERE M.sLoginName = :lname and M.sPassword = md5( :pwd ) ;";

          $this->RunSQLWithParams($sSQL, array("lname" => $this->m_aData[self::PROPERTY_LOGIN_NAME], 
              "pwd" => $this->m_aData[self::PROPERTY_PASSWORD]));
        }
        catch(Exception $e)
        {
          //do not throw original exception here, so user name and password won't be sent back to screen
          throw new Exception('<!$LOGIN_EXCEPTION$!>');
        }

        $result = $this->fetch();

        if (!$result)
            return self::ERR_LOGIN_INCORRECT_NAME_PASSWORD;
        else if ($result["bDisabled"])
            return self::ERR_MEMBER_DISABLED;

        $m_oSessionManager= new SessionManager;
    
        $m_oSessionManager->MemberID= $result['MemberID'];

        $m_oSessionManager->LoadPermissions();

        if (!$m_oSessionManager->HasPermissions)
        {
            unset($result);
            unset($stmt);
            return self::ERR_NO_PERMISSIONS;
        }

        $m_oSessionManager->Name = $result['sName'];
        $m_oSessionManager->PaymentMethod = $result['PaymentMethodKeyID'];
        $m_oSessionManager->JoinedOn = $result['dJoined'];
        $m_oSessionManager->CoordinatingGroupID = $result['CoordinatingGroupID'];
        if ( $result['mBalance'] != NULL)
            $m_oSessionManager->Balance = $result['mBalance'];
        
        if ( $result['nExportFormat'] != NULL)
            $m_oSessionManager->ExportFormat = $result['nExportFormat'];
        
        if ( $result['fPercentOverBalance'] != NULL)
            $m_oSessionManager->PercentOverBalance = $result['fPercentOverBalance'];

        $m_oSessionManager->LoadUserSession();

        $m_oSessionManager->Close();

        unset($result);
        unset($stmt);

        return self::SUCCESS;
    }

    public function UnsetAll()
    {
        unset( $m_oSessionManager );
    }
}
?>
