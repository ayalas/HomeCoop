<?php

include_once 'settings.php';
include_once 'authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

?>
<!DOCTYPE HTML>
<html dir='rtl' >
<head>
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
<?php include_once 'control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: הגישה נחסמה</title>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename">הגישה נחסמה</span></td>
    </tr>
</table>
</form>
 </body>
</html>
