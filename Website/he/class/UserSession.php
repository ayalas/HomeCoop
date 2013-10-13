<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

include_once APP_DIR .'/class/UserSessionBase.php';

//session data reader. main session object used thorughout the system for user specific data
class UserSession extends UserSessionBase
{
    const PROPERTY_CAN_ORDER = "CanOrder";
    const FIND_PERMISSION_SAFETY_MAX_LOOP = 50;
    
    public function __get( $name ) {
      switch ($name)
      {
        case self::PROPERTY_CAN_ORDER:
          if ($this->m_aData[parent::KEY_PAYMENT_METHOD] == Consts::PAYMENT_METHOD_AT_PICKUP)
            return TRUE;

          return ($this->m_aData[parent::KEY_BALANCE] > 0); 
        default:
          return parent::__get($name);
      }
    }
    
    public function __set( $name, $value ) {
        switch( $name )
        {
             case parent::KEY_LAST_ERROR:
             case parent::KEY_EXPORT_FORMAT:
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
  
    public function __construct( )
    {
         parent::__construct();
    }
    
    public static function IsAuthenticated()
    {
      if (session_id() == '')
        session_start ();
      
      return !( empty($_SESSION[ parent::KEY_MEMBERID ] ));
    }
    
    ///starts PHP session and retrieves UserID, Nickname for the current user
    ///sNotLoggedInRedirectAdr: redirects to this address (normally a login page), if the user is not logged in (no UserID saved in session)
    public function Authenticate( )
    {        
      if ( empty($_SESSION[ parent::KEY_MEMBERID ] ))
      {
          $this->m_bIsLoggedIn = false;
      }
      else
      {
          $this->m_bIsLoggedIn = true;
          foreach ($this->m_aData as $key => $value) //no use of the values of the array, bc it is being initialized
          {
              if ( isset($_SESSION[ $key ]) )
                  $this->m_aData[ $key ] = $_SESSION[ $key ];
          }
      }

      return $this->m_bIsLoggedIn;
    }
    
    
    public function GetLangIDByKey($sKey)
    {
        if ($sKey == NULL || $sKey == '')
            return $this->m_aData[parent::KEY_LANGUAGES][0]["LangID"]; //one-language deployment

        $nCount = count($this->m_aData[parent::KEY_LANGUAGES]);
        for($i=0; $i < $nCount; $i++)
        {
          if( $this->m_aData[parent::KEY_LANGUAGES][$i]["sPhpFolder"] ==  $sKey)
            return $this->m_aData[parent::KEY_LANGUAGES][$i]["LangID"];
        }

        return NULL;
    }
    
    public function UserInGroup($GroupID)
    {
      if ($GroupID == 0)
        return FALSE;
      
      return in_array($GroupID, $this->m_aData[parent::KEY_GROUPS]);
    }

    //main search permission function - searches the array of permission, for the given permission
    public function PermissionScope( $nArea, $nType )
    {
        if ( $this->m_aData[parent::KEY_IS_SYS_ADMIN] )
            return Consts::PERMISSION_SCOPE_COOP_CODE;
        
        $nRangeEnd = count( $this->m_aData[parent::KEY_PERMISSIONS] ) -1;
      
        $nRangeStart = 0;
        $nPos = 0;
        $nNext = 0;
        $nCurType = 0;
        $nLoop = 0;

        $nPos = $this->EstimatePermissionAreaRowPosition( $nArea, $nRangeStart, $nRangeEnd );
        while($nPos !== FALSE && $this->m_aData[parent::KEY_PERMISSIONS][$nPos]["PermissionAreaKeyID"] != $nArea)
        {                     
            //cancelled: helper code to block an infinite loop due to bug
            /*if ($nLoop > self::FIND_PERMISSION_SAFETY_MAX_LOOP) 
            {
              $nPos = FALSE;
              break;
            }
            $nLoop++;*/
              
            if ($this->m_aData[parent::KEY_PERMISSIONS][$nPos]["PermissionAreaKeyID"] > $nArea)
              $nRangeEnd = $nPos - 1;
            else if ($this->m_aData[parent::KEY_PERMISSIONS][$nPos]["PermissionAreaKeyID"] < $nArea)
              $nRangeStart = $nPos + 1;
            else //area found
                break;
            
            $nPos = $this->EstimatePermissionAreaRowPosition( $nArea, $nRangeStart, $nRangeEnd );
        }

        if ($nPos !== FALSE)
        {
            //area found, now find type, back or forward        
            $nNext = $nPos;
            //go back
            do
            {
                $nCurType = $this->m_aData[parent::KEY_PERMISSIONS][$nNext]["PermissionTypeKeyID"];
                if ( $nCurType == $nType)
                  return intval($this->m_aData[parent::KEY_PERMISSIONS][$nNext]["nScopeCode"]) ; //type found
                else if ( $nCurType < $nType)
                  break; //wrong loop, should check for higher values
                $nNext--;
            } while($nNext >= 0 && $this->m_aData[parent::KEY_PERMISSIONS][$nNext]["PermissionAreaKeyID"] == $nArea);
            //go forward, excluding $nPos, which was checked already
            $nNext = $nPos + 1;
            while($nNext <= $nRangeEnd && $this->m_aData[parent::KEY_PERMISSIONS][$nNext]["PermissionAreaKeyID"] == $nArea)
            {
                $nCurType = $this->m_aData[parent::KEY_PERMISSIONS][$nNext]["PermissionTypeKeyID"];
                if ( $nCurType == $nType)
                  return intval($this->m_aData[parent::KEY_PERMISSIONS][$nNext]["nScopeCode"]) ; //type found
                else if ( $nCurType > $nType)
                  break; //type not found. $nCurType is only going to get bigger
                $nNext++;
            }
        }

        return 0;
    }
    
    //maximum allowed order for the user
    public function GetMaxOrder()
    {
      return Member::CalculateMaxOrder( $this->m_aData[parent::KEY_PAYMENT_METHOD],
              $this->m_aData[parent::KEY_BALANCE],
              $this->m_aData[parent::KEY_PERCENT_OVER_BALANCE] );
    }

    //estimates position of the given area in the user's permissions array, assuming the array is sorted by area
    protected function EstimatePermissionAreaRowPosition( $nArea, $nRangeStart, $nRangeEnd )
    {
        if ($nRangeStart == $nRangeEnd)
            return $nRangeStart;
        if ($nRangeStart > $nRangeEnd)
            return FALSE;
        $nStartValue = $this->m_aData[parent::KEY_PERMISSIONS][$nRangeStart]["PermissionAreaKeyID"];
        $nEndValue = $this->m_aData[parent::KEY_PERMISSIONS][$nRangeEnd]["PermissionAreaKeyID"];
        
        if ($nArea < $nStartValue || $nArea > $nEndValue)
          return FALSE;
        
        //calculate index\value ration
        $fValuesPerIndex = ($nEndValue - $nStartValue) /($nRangeEnd - $nRangeStart);
        if ($fValuesPerIndex == 0) 
            $fValuesPerIndex = 1;
        $nResult = ceil($nRangeStart + ($nArea - $nStartValue)/$fValuesPerIndex);
        if ($nRangeEnd >= $nResult)
            return $nResult;
        return $nRangeEnd;
    }
}

?>
