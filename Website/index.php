<?php 
//show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

define("LANG_PARAM", "lang");
define("SESSION_LANG_ID", "LangID");

//DEFAULT LANGUAGE
$sLangDir = 'en';

if (isset($_GET[LANG_PARAM]))
{    
    //don't allow setting the language to an incorrect value by manually changing the query string param
    $sLangDir = $_GET[LANG_PARAM];
    setcookie(LANG_PARAM, $sLangDir);
}
else if (isset($_COOKIE[LANG_PARAM])) //read cookie value
    $sLangDir = $_COOKIE[LANG_PARAM];

//reset langid
session_start();
$_SESSION[SESSION_LANG_ID] = 0; //signals class\UserSessionBase LangID get property to load the lang id
session_write_close();

if (isset($_GET["redr"])) //redirect address
    header( 'Location: ' . $sLangDir  . '/' . $_GET["redr"] );
else
    header( 'Location: ' . $sLangDir  . '/home.php' );
?>
