<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new CoopOrders;
$recTable = NULL;
$dDate = NULL;

try
{
  $recTable = $oTable->GetTable();

  if (!$oTable->HasPermission(SQLBase::PERMISSION_COORD) || !$oTable->CanCopy()) //completely denied from page
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
<html dir='rtl' >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>הזינו את שם הקואופרטיב שלכם: העתקת הזמנת קואופרטיב</title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td width="908"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename">העתקת הזמנת קואופרטיב</span></td>
    </tr>
    <tr >
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" height="100%" >
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="8"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                <tr>
                  <td class="columnlongtitle">כותרת הזמנה</td>
                  <td class="columntitleshort">פתיחה</td>
                  <td class="columntitleshort">סגירה</td>
                  <td class="columntitleshort">משלוח</td>
                  <td class="columntitleshort">קואופ</td>
                  <td class="columntitleshort">יצרנים</td>
                  <td class="columntitleshort">מצב</td>
                  <td class="columntitlenowidth"></td>
                </tr>
<?php
                if (!$recTable)
                {
                  ?>
                  <tr><td colspan='8'>&nbsp;</td></tr>
                  <tr><td colspan='8' align='center'>לא נמצאו רשומות.</td></tr>
                  <?php
                }
                else
                {
                  while ( $recTable )
                  {
                      //name
                      echo "<tr><td><a class='tooltiplink' href='cooporder.php?id=" ,  $recTable["CoopOrderKeyID"] , "' >" , 
                        htmlspecialchars($recTable["sCoopOrder"]),
                      //name tooltip
                       "<span>",
                     
                       "סה&quot;כ משלוח‏:‏ " , $recTable["mTotalDelivery"] , "<br/>",
                       "סה&quot;כ מעמסה‏:‏ " , Rounding::Round($recTable["fBurden"], ROUND_SETTING_BURDEN) , "<br/>",
                       "קיבולת משלוח‏:‏ " , $recTable["fMaxBurden"] , "<br/>",
                       "עודכן לאחרונה ע&quot;י‏:‏ " , $recTable["ModifierName"],
                      
                       "</span></a></td>";
                      
                      //start
                      $oDate = new HtmlDateString($recTable["dStart"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                      echo "<td>";
                      $oDate->EchoHtml();
                      echo "</td>";
                      
                      //end
                      $oDate = new HtmlDateString($recTable["dEnd"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                      echo "<td>";
                      $oDate->EchoHtml();
                      echo "</td>";
                      
                      //delivery
                      $oDate = new HtmlDateString($recTable["dDelivery"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                      echo "<td>";
                      $oDate->EchoHtml();
                      echo "</td>";
                      
                      //sums
                      echo "<td>" , $recTable["mCoopTotal"] ,  "</td>";
                      echo "<td>" , $recTable["mProducerTotal"] ,  "</td>";
                      
                      //status
                      echo "<td>" , CoopOrder::StatusName($recTable["nStatus"]) ,  "</td>";
                      
                      echo "<td><a title='" , htmlspecialchars($recTable["sCoopOrder"]), "' href='coopordercopy.php?id=" ,  
                              $recTable["CoopOrderKeyID"] , "'>העתקה</a></td>";
                      
                      echo '</tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
?>
                </table>
                </td>
                <td width="128" >
                <?php 
                    include_once '../control/coordpanel.php'; 
                ?>
                </td>
            </tr>
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