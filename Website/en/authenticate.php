<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

include_once APP_DIR . '/class/UserSession.php';
include_once APP_DIR . '/class/UserSessionBase.php';
include_once APP_DIR . '/class/Consts.php';

define('POST_ELM_LOGOUT','hidLogout');

$g_oMemberSession = new UserSession ( );

if ($_SERVER[ 'REQUEST_METHOD'] == 'POST')
{
    //basic referer check for each post in the system
    if (  ! HttpRefererCheck::PerformCheck() )
    {
        header('Location: ' . $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        trigger_error('Cannot post from a remote location.',E_USER_NOTICE);
        exit;
    }

    //process logout post request
    if (array_key_exists(POST_ELM_LOGOUT, $_POST)) {
      if ($_POST[ POST_ELM_LOGOUT ] )
      {
          $g_oMemberSession->Logout( );
          RedirectPage::To( $g_sRootRelativePath . Consts::URL_LOGIN );
          exit;
      }
    }
}

//authenticate user by checking session data
if (! $g_oMemberSession->Authenticate( ) )
{
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_LOGIN );
        exit;
}

?>
