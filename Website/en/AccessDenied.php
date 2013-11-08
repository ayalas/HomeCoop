<?php

include_once 'settings.php';
include_once 'authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

?>
<!DOCTYPE HTML>
<html>
<head>
<?php include_once 'control/headtags.php'; ?>
<title>Enter Your Cooperative Name: Access Denied</title>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename">Access Denied</span></td>
    </tr>
</table>
</form>
 </body>
</html>
