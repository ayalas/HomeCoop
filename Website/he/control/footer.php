<br/>
<br/>
<a href="<?php echo $g_sRootRelativePath; ?>about.php" ><img border="0" src="<?php 
    echo $g_sRootRelativePath; ?>img/system.png" title="אודות התוכנה" /></a><br/>
<br/>
<script>
<?php
  if (isset($g_oMemberSession)) {
    
    if (!$g_oMemberSession->IsOnlyMember) {
?>
    var nav2 = responsiveNav("#navCoord", { 
      animate: true, 
      transition: 400,
      label: "",
      insert: "after",
      customToggle: "tglCoord",
      openPos: "relative",
      jsClass: "js",
      init: function(){},
      open: function(){},
      close: function(){}
    });
<?php
    }
  
?>
    var nav = responsiveNav("#navUser", { 
      animate: true,
      transition: 400,
      label: "", 
      insert: "after",
      customToggle: "tglUser",
      openPos: "relative", 
      jsClass: "js", 
      init: function(){},
      open: function(){},
      close: function(){}
    });
<?php
    }
  
?>
</script>
<?php


//close DB connection and release memory
if ($g_oDBAccess != NULL)
  $g_oDBAccess->Close();
unset($g_oDBAccess);
unset($g_oTimeZone);
unset($g_dNow);
unset($g_aSupportedLanguages);
unset($g_oError);
if (isset($g_oMemberSession))
  unset($g_oMemberSession);

?>
