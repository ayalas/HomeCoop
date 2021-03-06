<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CachierTotals;

try
{
  if (!$oData->GetData())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
}
catch(Exception $e)
{
  $g_oError->HandleException($e);
}

//close session opened in 'authenticate.php' when not required anymore
//must be after any call to HandleException, because it writes to the session
UserSessionBase::Close();

?>
<!DOCTYPE HTML>
<html>
<head>
<?php include_once '../control/headtags.php'; ?>
<title>Enter Your Cooperative Name: Cashier Totals</title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename">Cashier Totals</span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td><?php 
                  include_once '../control/error/ctlError.php';
                ?></td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <?php                
                $lblTotalMemberBalances = new HtmlTextLabel('Total Member Balances', 'lblTotalMemberBalances', 
                        $oData->TotalMemberBalances);
                $lblTotalMemberBalances->SetAttribute('dir', 'ltr');
                $lblTotalMemberBalances->SetAttribute('class', 'headlabel');
                $lblTotalMemberBalances->EchoHtml();
                unset($lblTotalMemberBalances);
                
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                <tr>
                <?php                
                $lblTotalPickupLocationCachiers = new HtmlTextLabel('Total Cashiers', 'lblTotalPickupLocationCachiers', 
                        $oData->TotalPickupLocationCachiers);
                $lblTotalPickupLocationCachiers->SetAttribute('dir', 'ltr');
                $lblTotalPickupLocationCachiers->SetAttribute('class', 'headlabel');
                $lblTotalPickupLocationCachiers->EchoHtml();
                unset($lblTotalPickupLocationCachiers);
                
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                <tr>
                <?php                
                $lblBalance = new HtmlTextLabel('Total Balance', 'lblBalance', $oData->TotalCachierBalance);
                $lblBalance->SetAttribute('dir', 'ltr');
                $lblBalance->SetAttribute('class', 'headlabel');
                $lblBalance->EchoHtml();
                unset($lblBalance);
                
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                </table>
                </td></tr>
            </table>
        </td>
    </tr>
    <tr>
      <td>
        <?php 
        include_once '../control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>
