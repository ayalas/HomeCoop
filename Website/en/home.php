<?php

include_once 'settings.php';
include_once 'authenticate.php';

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style/main.css" />
<title>Enter Your Cooperative Name: Home</title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" src="script/activeorders.js" ></script>
</head>
<body class="centered">
<form id="frmHome" name="frmHome" method="post">
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename">Home</span></td>
    </tr>
   <tr>
     <td>
       &nbsp;
     </td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="108" ><?php include_once 'control/userpanel.php'; ?></td>
                <td width="672" ><table cellspacing="0" cellpadding="0" width="100%"><?php
                  try
                  {
                    $oNotifications = new Notifications;
                    $oNotifications->DisplayNotifications();
                    unset($oNotifications);
                  }
                  catch(Exception $e)
                  {
                    $g_oError->HandleException($e);
                  }
                ?>
                <tr>
                  <td><?php 
                  include_once 'control/activeorders.php';
                  
                  //close session opened in 'authenticate.php' when not required anymore
                  //must be after any call to HandleException, because it writes to the session
                  UserSessionBase::Close();
                ?>
                </td>
                </tr>
                <tr>
                  <td><?php 
                  include_once 'control/error/ctlError.php';
                ?></td>
                </tr>
                </table>
                </td>
                <td width="128" >
                <?php 
                    include_once 'control/coordpanel.php'; 
                ?>
                </td>
            </tr>
            </table>
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
