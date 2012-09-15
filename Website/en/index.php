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

      $sLoginName = trim($_POST['txt_login']);
      $nLoginFlags = $oLogin->DoLogin ( $sLoginName, trim($_POST['txt_pwd']) );
      $oLogin->UnsetAll();
      unset($oLogin);

      if ( $nLoginFlags == 0 )
      {
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
                  $g_oError->AddError(sprintf('Your user has no permissions at all. If this is a mistake, please contact us at %s to correct the problem', COOP_ADDRESS_MEMBER_PERMISSIONS));
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
<title>Enter Your Cooperative Name: Login</title>

<script type="text/javascript" src="script/public.js" ></script>
 </head>
 <body class="centered">
<form id="frmLogin" name="frmLogin" method="post">
<header>
<br/><br/>
<table>
  <tr>
    <td><a href="home.php" ><img border="0" src="logo.gif"/></a></td>
  </tr>
   <tr>
    <td>
<?php
    include_once 'control/language.php';
?>
    </td>
    </tr>
</table>
<br/><br/>
</header>
<table width="800" cellpadding="0" cellspacing="0">
<tr><td colspan="2"><span class="cooptitle">Enter Your Cooperative Name</span></td></tr>
<tr>
<td valign="top">
<table width="400">
    <tr>
        <td colspan="2"><?php 
                  include_once 'control/error/ctlError.php';
                ?></td>
    </tr>
    <?php
     if (PRODUCT_CATALOG_IS_PUBLIC)
     {
        echo '<tr><td colspan="2"><span><a href="catalog.php">Products Catalog</a></span></td></tr>';
     }
    ?>
    <tr>
        <td colspan="2"><span class="pagename">Login</span></td>
    </tr>
   <tr>
        <td nowrap><label for="txt_login">User name</label></td>
        <td width="100%"><input type="text" dir="ltr" maxlength="128" id="txt_login" name="txt_login" required="required" value="<?php echo htmlspecialchars( $sLoginName ); ?>" /></td>
    </tr>
   <tr>
        <td nowrap><label for="txt_pwd">Password</label></td>
        <td width="100%"><input type="password" dir="ltr" maxlength="30" id="txt_pwd" name="txt_pwd" required="required"/></td>
    </tr>
   <tr>
        <td colspan="2"><button type="submit" value="" id="btn_login" name="btn_login" >Login</button></td>
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
