<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitate login to the system
class Login extends SQLBase
{
    const PROPERTY_USER_SESSION = "SessionManager";
    //login error codes
    const SUCCESS = 0;
    const ERR_LOGIN_NAME_EMPTY = 1;
    const ERR_LOGIN_PASSWORD_EMPTY = 2;
    const ERR_LOGIN_INCORRECT_NAME_PASSWORD = 3;
    const ERR_NO_PERMISSIONS = 4;

    protected $m_oSessionManager = null;

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

    public function DoLogin ($sLoginName, $sPassword)
    {
        //first check for must fields
        if ( !isset($sLoginName) || $sLoginName == "")
            return self::ERR_LOGIN_NAME_EMPTY;

        if ( !isset($sPassword) || $sPassword == "")
            return self::ERR_LOGIN_PASSWORD_EMPTY;

        $sSQL = "SELECT M.sName, M.MemberID, M.PaymentMethodKeyID, M.fPercentOverBalance, M.dJoined, M.mBalance, CG.CoordinatingGroupID " . 
                " FROM T_Member M INNER JOIN T_CoordinatingGroupMember CGM ON CGM.MemberID = M.MemberID " . 
                " INNER JOIN T_CoordinatingGroup CG ON CG.CoordinatingGroupID = CGM.CoordinatingGroupID AND CG.sCoordinatingGroup IS NULL " .
                " WHERE M.sLoginName = ? and M.sPassword = md5( ? ) ;";

        $this->RunSQLWithParams($sSQL, array($sLoginName, $sPassword));

        $result = $this->fetch();

        if (!$result)
            return self::ERR_LOGIN_INCORRECT_NAME_PASSWORD;

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
