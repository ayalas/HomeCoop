<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class RedirectPage {
  
  //closes session that may be still open and redirects to the given url
  public static function To($sUrl)
  {
    UserSessionBase::Close();
    header('Location: ' . $sUrl);
  }
}

?>
