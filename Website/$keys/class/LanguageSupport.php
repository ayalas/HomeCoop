<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//help perform some repeated actions against the supported languages array $g_aSupportedLanguages (defined in settings.php)
class LanguageSupport {
  
  //used for export to OpenOffice Calc fods (flat XML) files in the current language direction (ltr/trl)
  public static function GetCurrentHtmlDir()
  {
    global $g_aSupportedLanguages;
    global $g_sLangDir;
    
    if ( is_array($g_aSupportedLanguages) && count($g_aSupportedLanguages) > 0 )
      return $g_aSupportedLanguages[$g_sLangDir][Consts::IND_LANGUAGE_DIRECTION];
    
    return NULL;
  }
}

?>
