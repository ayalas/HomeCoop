<?php

include_once 'settings.php';
include_once 'authenticate.php';

 if ( $g_oMemberSession->LastError != NULL )
 {
    $g_oError->AddError($g_oMemberSession->LastError);
    
    $g_oMemberSession->LastError = NULL;
 }

 //close session opened in 'authenticate.php' when not required anymore
 UserSessionBase::Close();
 

?>
<!DOCTYPE HTML>
<html>
<head>
<?php include_once 'control/headtags.php'; ?>
<title>Enter Your Cooperative Name: System Error</title>
<script type="text/javascript" src="script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmError" name="frmError" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename">System Error</span></td>
    </tr>
   <tr>
     <td>
       &nbsp;
     </td>
    </tr>
    <tr>
        <td>
            <?php 
                  include_once 'control/error/ctlError.php';
            ?>
        </td>
    </tr>
    <tr>
    <td>
      <?php 
      include_once 'control/footer.php';
      ?>
    </td>
  </tr>
</table>
</form>
 </body>
</html>

