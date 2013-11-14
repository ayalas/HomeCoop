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
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
<?php include_once 'control/headtags.php'; ?>
<title><!$COOPERATIVE_NAME$!>: <!$ERROR_PAGE_TITLE$!></title>
<script type="text/javascript" src="script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmError" name="frmError" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename"><!$ERROR_PAGE_TITLE$!></span></td>
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

