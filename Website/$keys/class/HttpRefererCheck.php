<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//check referer for all post requests, just as another precaution, though it is easily manipulated
//action is performed on each postback in login screen (index.php) and on each "authenticated user" page
//through authenticate.php
class HttpRefererCheck {
  public static function PerformCheck()
  {
    global $_SERVER;
    
    if ($_SERVER[ 'REQUEST_METHOD'] != 'POST')
        return true;
    
    if ( stripos ( $_SERVER['HTTP_REFERER'] , 'http://' . $_SERVER['SERVER_NAME'] ) === 0
       || stripos ( $_SERVER['HTTP_REFERER'] , 'http://' . $_SERVER['SERVER_ADDR'] ) === 0 )
       return true;

    return false;
 }
}

?>
