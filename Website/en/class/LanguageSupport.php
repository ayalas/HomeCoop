<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//help perform some repeated actions against the supported languages array $g_aSupportedLanguages (defined in settings.php)
class LanguageSupport {
  
  //used for export to OpenOffice Calc fods (flat XML) files in the current language direction (ltr/trl)
  public static function GetCurrentHtmlDir()
  {
    global $g_aSupportedLanguages;
    global $g_nCountLanguages;
    global $g_sLangDir;
    
    //using language dirs?
    if ($g_nCountLanguages > 0)
      return $g_aSupportedLanguages[$g_sLangDir][Consts::IND_LANGUAGE_DIRECTION];
    
    return NULL;
  }
  
  //supports adding html strings right after using LRM/RLM marks
  public static function AppendInFixedOrder()
  {
    $arrParams = func_get_args();
    
    $sResult = NULL;
    
    foreach($arrParams as $sStr)
    {
      $sResult .= $sStr . '‎';
    }
    
    return $sResult;
  }
  
  //supports adding html strings right after using LRM/RLM marks
  public static function EchoInFixedOrder()
  {
    $arrParams = func_get_args();
    
    foreach($arrParams as $sStr)
    {
      echo $sStr, '‎';
    }
  }
}

?>
