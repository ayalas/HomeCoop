<?php

include_once 'settings.php';
include_once 'authenticate.php';
include_once 'facet.php';

?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
<?php include_once 'control/headtags.php'; ?>
<link rel="stylesheet" type="text/css" href="style/facet.css" />
<title>Enter Your Cooperative Name: Home</title>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" src="script/activeorders.js" ></script>
<script type="text/javascript" src="script/facet.js" ></script>
<script>
  function CloseNavs()
  {
    if (typeof nav !== 'undefined') {
      nav.close();
    }
    if (typeof nav2 !== 'undefined') {
      nav2.close();
    }
  }
</script>
</head>
<body class="centered" onclick="JavaScript:CloseNavs();">
<form id="frmHome" name="frmHome" method="post">
<input type="hidden" id="hidfacetmblexpandstate" name="hidfacetmblexpandstate" value="0" />
<input type="hidden" id="hidplfacetgrpexpandstate" name="hidplfacetgrpexpandstate" value="0" />
<input type="hidden" id="hidSelectedPLs" name="hidSelectedPLs" value="" />
<?php 
  $sHeaderAdditionToLogo = '<a href="#" class="facetmobileexpander mobiledisplay" onclick="JavaScript:ToggleMobileExpand();">
    <img alt="Pickup Locations" id="imgFacetMobileExpandArrow" src="img/filter.png"/></a>';

  include_once 'control/header.php'; 
?>
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
              <td id="tdMain"><?php 
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
