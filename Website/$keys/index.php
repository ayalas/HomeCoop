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
                  $g_oError->AddError('<!$LOGIN_INCORRECT$!>');
                  break;
              case Login::ERR_LOGIN_NAME_EMPTY:
                  $g_oError->AddError('<!$LOGIN_NAME_REQUIRED$!>');
                  break;
              case Login::ERR_LOGIN_PASSWORD_EMPTY:
                  $g_oError->AddError('<!$PASSWORD_REQUIRED$!>');
                  break;
              case Login::ERR_NO_PERMISSIONS:
                  $g_oError->AddError(sprintf('<!$PERMISSIONS_NONE$!>', COOP_ADDRESS_MEMBER_PERMISSIONS));
                  break;
              case Login::ERR_MEMBER_DISABLED:
                  $g_oError->AddError(sprintf('<!$ERR_MEMBER_DISABLED$!>', COOP_ADDRESS_MEMBER_PERMISSIONS));
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style/main.css" /> 
<title><!$COOPERATIVE_NAME$!>: <!$PUBLIC_INDEX_TITLE$!></title>

<script type="text/javascript" src="script/public.js" ></script>
 </head>
 <body class="centered">
<form id="frmLogin" name="frmLogin" method="post">
<header>
<table border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td class="logo"><a href="home.php" ><img border="0" src="logo.gif"/></a></td>
  </tr>
   <tr>
    <td><?php
    include_once 'control/language.php';
?></td>
    </tr>
</table>
</header>
<table width="800" cellpadding="0" cellspacing="0">
<tr><td colspan="2"><span class="cooptitle"><!$COOPERATIVE_NAME$!></span></td></tr>
<tr>
<td valign="top">
<table width="400">
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
        echo '<span><a class="LinkButton" href="catalog.php"><!$PAGE_TITLE_PRODUCT_CATALOG$!></a>&nbsp;</span>';
     }
     if (JOIN_PAGE != '')
     {
       echo '<span><a class="LinkButton" href="',JOIN_PAGE,'"><!$PAGE_LINK_JOIN$!></a>&nbsp;</span>';
     }
    ?>
      </td>
    </tr>
    <tr>
        <td colspan="2"><span class="pagename"><!$PUBLIC_INDEX_TITLE$!></span></td>
    </tr>
   <tr>
        <td nowrap><label for="txt_login"><!$FIELD_LOGIN_NAME$!></label></td>
        <td width="100%"><input style="width:120px;" type="text" dir="ltr" maxlength="<!$MAX_LENGTH_LOGIN_NAME$!>" id="txt_login" name="txt_login" required="required" value="<?php echo htmlspecialchars( $sLoginName ); ?>" /></td>
    </tr>
   <tr>
        <td nowrap><label for="txt_pwd"><!$FIELD_PASSWORD$!></label></td>
        <td width="100%"><input style="width:120px;" type="password" dir="ltr" maxlength="<!$MAX_LENGTH_PASSWORD$!>" id="txt_pwd" name="txt_pwd" required="required"/></td>
    </tr>
   <tr>
        <td colspan="2"><button type="submit" value="" id="btn_login" name="btn_login" ><!$BTN_LOGIN$!></button></td>
    </tr>
</table>
</td>
<td valign="top">
    <?php include_once 'coopbrief.htm'; ?>
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
