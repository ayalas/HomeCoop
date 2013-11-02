<?php

include_once 'settings.php';
include_once 'authenticate.php';
include_once 'facet.php';

?>
<!DOCTYPE HTML>
<html dir='rtl' >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style/main.css" />
<link rel="stylesheet" type="text/css" href="style/facet.css" />
<title>הזינו את שם הקואופרטיב שלכם: דף הבית</title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" src="script/activeorders.js" ></script>
<script type="text/javascript" src="script/facet.js" ></script>
</head>
<body class="centered">
<form id="frmHome" name="frmHome" method="post">
<input type="hidden" id="hidplfacetgrpexpandstate" name="hidplfacetgrpexpandstate" value="0" />
<input type="hidden" id="hidSelectedPLs" name="hidSelectedPLs" value="" />
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <?php
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
              <td id="tdFacet">
                <?php  include_once 'control/facetpanel.php'; ?>
              </td>
              <td><?php 
              include_once 'control/activeorders.php';

              //close session opened in 'authenticate.php' when not required anymore
              //must be after any call to HandleException, because it writes to the session
              UserSessionBase::Close();
            ?>
            </td>
            </tr>
            <tr>
              <td colspan="2"><?php 
              include_once 'control/error/ctlError.php';
            ?></td>
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
