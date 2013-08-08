<?php

include_once 'settings.php';
include_once 'authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style/main.css" />
<title><!$COOPERATIVE_NAME$!>: <!$ACCESS_DENIED$!></title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="<!$TOTAL_PAGE_WIDTH$!>"><span class="coopname"><!$COOPERATIVE_NAME$!>:&nbsp;</span><span class="pagename"><!$ACCESS_DENIED$!></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="<!$USER_PANEL_WIDTH$!>" ><?php include_once 'control/userpanel.php'; ?></td>
                <td width="<!$HOME_CONTENT_WIDTH$!>" >&nbsp;</table>
                </td>
                <td width="<!$COORD_PANEL_WIDTH$!>" ><?php include_once 'control/coordpanel.php'; ?></td>
            </tr>
            </table>
        </td>
    </tr>
</table>
</form>
 </body>
</html>
