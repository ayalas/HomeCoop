<?php

/*the following files should all be included in a php file to incorporate the Error control:
control/error/hdError.php - in the upper most include_once section
control/error/scError.js - optional. as a script file in the Html document
control/error/ctlError.php - inside the Html document where the error messages should be displayed
*/

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;
   
?>
<div id="ctlError" name="ctlError">
    <span id="ctlError" name="ctlError" class="message"><?php echo $g_oError->Message; ?></span>
</div>