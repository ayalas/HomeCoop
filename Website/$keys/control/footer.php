<br/>
<br/>
<a href="<?php echo $g_sRootRelativePath; ?>about.php" ><img border="0" src="<?php 
    echo $g_sRootRelativePath; ?>img/system.png" title="<!$ABOUT_SOFTWARE$!>" /></a><br/>
<br/>
<?php

//close DB connection and release memory
if ($g_oDBAccess != NULL)
  $g_oDBAccess->Close();
unset($g_oDBAccess);
unset($g_oTimeZone);
unset($g_dNow);
unset($g_aSupportedLanguages);
unset($g_oError);
unset($g_oMemberSession);

?>