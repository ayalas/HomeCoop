<?php

include_once 'settings.php';

$sLoginName = '';
$oLogin = new Login;

try
{
  if ($_SERVER[ 'REQUEST_METHOD'] == 'POST')
  {
      //basic referer check
      if (  ! HttpRefererCheck::PerformCheck() )
      {
         header('Location: ' . Consts::URL_ACCESS_DENIED );
         exit();
      }

      $oLogin->LoginName = trim($_POST['txt_login']);
      $oLogin->Password = trim($_POST['txt_pwd']);
      $nLoginFlags = $oLogin->DoLogin ();
      $oLogin->UnsetAll();
      unset($oLogin);

      if ( $nLoginFlags == 0 )
      {
          if (isset($_GET["redr"]))
            header('Location: ' . $_GET["redr"]) ;
          else
            header('Location: ' . Consts::URL_HOME );
          exit();
      }
      else
      {
          switch(  $nLoginFlags )
          {
              case Login::ERR_LOGIN_INCORRECT_NAME_PASSWORD:
                  $g_oError->AddError('Incorrect User name or password.');
                  break;
              case Login::ERR_LOGIN_NAME_EMPTY:
                  $g_oError->AddError('User name is required.');
                  break;
              case Login::ERR_LOGIN_PASSWORD_EMPTY:
                  $g_oError->AddError('Password is required.');
                  break;
              case Login::ERR_NO_PERMISSIONS:
                  $g_oError->AddError(sprintf('Your account has no permissions at all. If this is a mistake, please contact us at %s to correct the problem', COOP_ADDRESS_MEMBER_PERMISSIONS));
                  break;
              case Login::ERR_MEMBER_DISABLED:
                  $g_oError->AddError(sprintf('Your account is inactive. If this is a mistake, please contact us at %s to correct the problem', COOP_ADDRESS_MEMBER_PERMISSIONS));
                  break;
              default:
                  break;
         }
      }
  }
}
catch(Exception $e)
{
  $g_oError->HandleException($e);
}
?>
<!DOCTYPE HTML>
<html>
 <head>
 <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
 <?php include_once 'control/headtags.php'; ?>
  <title>Enter Your Cooperative Name: Login</title>
 </head>
 <body class="centered">
<form id="frmLogin" name="frmLogin" method="post">
<header>
<table border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td class="logo"><a href="home.php" ><img class="logoimg" src="logo.gif"/></a></td>
  </tr>
   <tr><td><span class="coopsubtitle"></span></td></tr>
   <tr>
    <td><?php
    include_once 'control/language.php';
?></td>
    </tr>
</table>
</header>
<table cellspacing="0">
<tr>
<td valign="top">
<table class="entrytable">
    <tr>
        <td colspan="2"><?php 
                  include_once 'control/error/ctlError.php';
                ?></td>
    </tr>
    <tr>
      <td colspan="2">
    <?php
     if (PRODUCT_CATALOG_IS_PUBLIC)
     {
        echo '<span><a class="LinkButton" href="catalog.php">Products Catalog</a>&nbsp;</span>';
     }
     if (JOIN_PAGE != '')
     {
       echo '<span><a class="LinkButton" href="',JOIN_PAGE,'">Join</a>&nbsp;</span>';
     }
    ?>
      </td>
    </tr>
    <tr>
        <td colspan="2"><span class="pagename">Login</span></td>
    </tr>
   <tr>
        <td class="paddable nowrapping"><label for="txt_login">User name</label></td>
        <td class="paddable"><input class="dataentrysmall" type="text" dir="ltr" maxlength="128" id="txt_login" name="txt_login" required="required" value="<?php echo htmlspecialchars( $sLoginName ); ?>" /></td>
    </tr>
   <tr class="inputrow">
        <td class="paddable"><label for="txt_pwd">Password</label></td>
        <td class="paddable"><input class="dataentrysmall" type="password" dir="ltr" maxlength="30" id="txt_pwd" name="txt_pwd" required="required"/></td>
    </tr>
   <tr>
        <td colspan="2"><button type="submit" value="" id="btn_login" name="btn_login" >Login</button></td>
    </tr>
    <tr>
      <td colspan="2"><?php include_once 'coopbrief.htm'; ?></td>
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
