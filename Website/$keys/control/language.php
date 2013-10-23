<?php

//language change link(s). Dynamically creats links according to supported languages

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

$g_sLangControl = '';

if ($g_aSupportedLanguages === NULL) //one language deployment exit
    return;

function AddLanguageOption($sFolder, $sText)
{
    global $g_sLangDir;
    global $g_sLangControl;
    global $g_sRedirectAfterLangChange;
    global $g_sRootRelativePath;

    if (LANGUAGE_SWITCHER == LANGUAGE_SWITCHER_VALUE_DROPDOWN)
    {
        if ($sFolder == $g_sLangDir)
            $g_sLangControl = '<option selected ><!$SELECT_LANGUAGE$!></option>' . $g_sLangControl; //always keep the "choose language" option on top
        else
            $g_sLangControl .= '<option value="' . $sFolder  . '">'  . $sText . '</option>';
     }
     else //LINKS
     {
        if ($sFolder == $g_sLangDir)
            return;

        $g_sLangControl .= '<span class="usermenulink usermenulabel" onclick="JavaScript: ChangeLanguage(\'' . 
                                        $sFolder . '\', \'' . $g_sRedirectAfterLangChange . '\',\'../' . 
                                        $g_sRootRelativePath . 'index.php\');" >' . $sText . '</span>';
     }
}

if (LANGUAGE_SWITCHER == LANGUAGE_SWITCHER_VALUE_DROPDOWN)
{
?>
<select class="usermenucell usermenulabel" onchange="JavaScript: ChangeLanguage(this.value, '<?php echo $g_sRedirectAfterLangChange; ?>','<?php echo '../' , $g_sRootRelativePath; ?>index.php');">
<?php
}

foreach( $g_aSupportedLanguages as $sKey => $aLang)
{
    AddLanguageOption( $sKey, $aLang[Consts::IND_LANGUAGE_NAME]);
}

echo $g_sLangControl;

if (LANGUAGE_SWITCHER == LANGUAGE_SWITCHER_VALUE_DROPDOWN)
{
?>
</select>
<?php
}

?>
