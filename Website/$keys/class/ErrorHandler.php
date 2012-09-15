<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//concats messages to display in control/error/ctlerror, including exceptions data
class ErrorHandler {
  const PROPERTY_MESSAGE = "Message";
  const PROPERTY_HAD_ERROR = "HadError";
  
  protected $m_aData = array
  (
      self::PROPERTY_MESSAGE => '',
      self::PROPERTY_HAD_ERROR => FALSE
  );
  
  public function __get( $name ) {
      if (array_key_exists( $name, $this->m_aData))
          return $this->m_aData[$name];
      $trace = debug_backtrace();
      throw new Exception(
          'Undefined property via __get(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line']);
  }
  
  //this function writes to the session, so the session should be open for write operations whenever this function is called
  public function HandleException($e)
  {
    global $g_oMemberSession, $g_sRootRelativePath;
    if (!$e) return;
    
    $sException = $e->getMessage();
    
    $sErrorMessage = '';
    
    //handle known errors
    if (stripos($sException,"SQLSTATE[42000]") > 0 &&
            stripos($sException,"more than 'max_user_connections' active connections"))
    {
      $sErrorMessage = '<!$ERROR_MAX_USER_CONNECTIONS$!>';
    }
    else
    {
      $sErrorMessage = sprintf('<!$ERROR_TECHNICAL_INFORMATION$!><br/><div dir="ltr">Message: %s<br/>Trace: %s</div>', 
            $sException, nl2br($e->getTraceAsString () ) );
    }
  
    if (USE_ERROR_PAGE && isset($g_oMemberSession) && $g_oMemberSession->IsLoggedIn)
    {
      $g_oMemberSession->LastError = $sErrorMessage;
      RedirectPage::To( $g_sRootRelativePath . 'error.php');
    }
    else
    {
      $this->m_aData[self::PROPERTY_HAD_ERROR] = TRUE;
      
      $this->AddError( '<!$ERROR_OCCURED$!><br/>' . $sErrorMessage );
    }
  }
  
  public function AddError( $sErr )
  {
      $this->m_aData[self::PROPERTY_MESSAGE] .= $sErr .'<br/>';
  }

  public function SetError( $sErr )
  {
      $this->m_aData[self::PROPERTY_MESSAGE] = $sErr .'<br/>';
  }

  public function PushError( $sErr )
  {
      $this->m_aData[self::PROPERTY_MESSAGE] = $sErr .'<br/>' . $this->m_aData[self::PROPERTY_MESSAGE];
  }
}

?>
