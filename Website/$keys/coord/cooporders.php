<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new CoopOrders;
$recTable = NULL;
$dDate = NULL;
$oCoopOrderCapacity = NULL;
try
{
  $recTable = $oTable->GetTable();

  if ($oTable->LastOperationStatus == CoopOrders::OPERATION_STATUS_NO_PERMISSION) //completely denied from page
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

$bCanViewSums = $oTable->HasPermission(CoopOrders::PERMISSION_SUMS);
$bCanCopy = $oTable->HasPermission(SQLBase::PERMISSION_COPY);
$bCanEdit = $oTable->HasPermission(SQLBase::PERMISSION_COORD);
$bCanSetCoord = $oTable->HasPermission(SQLBase::PERMISSION_COORD_SET);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title><!$COOPERATIVE_NAME$!>: <!$PAGE_TITLE_COOP_ORDERS$!></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td width="<!$TOTAL_PAGE_WIDTH$!>"><span class="coopname"><!$COOPERATIVE_NAME$!>:&nbsp;</span><span class="pagename"><!$PAGE_TITLE_COOP_ORDERS$!></span></td>
    </tr>
    <tr>
        <td>
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="8"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                  <?php 
                  if ($bCanEdit)
                  {
                    echo '<tr><td colspan="8"><a href="cooporder.php" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a></td></tr>';
                  }
                  ?>
                <tr>
                  <td class="columntitlelong"><!$FIELD_COOP_ORDER_NAME$!></td>
                  <td class="columntitletiny"><!$ORDER_CAPACITY_PERCENT_FULL$!></td>
                  <td class="columntitletiny"><!$FIELD_COOP_ORDER_START$!></td>
                  <td class="columntitletiny"><!$FIELD_COOP_ORDER_END$!></td>
                  <td class="columntitletiny"><!$FIELD_COOP_ORDER_DELIVERY$!></td>
                  <td class="columntitletiny"><!$FIELD_COOP_ORDER_STATUS$!></td>
                  <?php if ($bCanViewSums) 
                        {
                          echo '<td class="columntitleshort"><!$FIELD_COOP_ORDER_COOP_TOTAL_SHORT$!></td>',
                               '<td class="columntitleshort"><!$FIELD_COOP_ORDER_PRODUCER_TOTAL_SHORT$!></td>'; 
                        }
                        else
                          echo '<td colspan="2"></td>';
                  ?>
                  
                  
                  <td class="columntitletiny"><?php 
                    if ($bCanCopy)
                    {
                      echo '<!$FIELD_COPY$!>';
                    }
                  ?></td>
                  <td class="columntitlenowidth"><?php 
                    if ($bCanSetCoord)
                    {
                      echo '<!$FIELD_COORD$!>';
                    }
                  ?></td>
                </tr>
<?php
                if (!$recTable)
                {
                  ?>
                  <tr><td colspan='8'>&nbsp;</td></tr>
                  <tr><td colspan='8' align='center'><!$NO_RECORD_FOUND$!></td></tr>
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
                     
                       "<!$FIELD_COOP_ORDER_TOTAL_DELIVERY$!><!$FIELD_DISPLAY_NAME_SUFFIX$!> " , $recTable["mTotalDelivery"] , "<br/>",
                       "<!$FIELD_COOP_ORDER_TOTAL_BURDEN$!><!$FIELD_DISPLAY_NAME_SUFFIX$!> " , Rounding::Round($recTable["fBurden"], ROUND_SETTING_BURDEN) , "<br/>",
                       "<!$FIELD_COOP_ORDER_MAX_BURDEN$!><!$FIELD_DISPLAY_NAME_SUFFIX$!> " , $recTable["fMaxBurden"] , "<br/>",
                       "<!$FIELD_COOP_ORDER_MODIFIER$!><!$FIELD_DISPLAY_NAME_SUFFIX$!> " , $recTable["ModifierName"],
                      
                       "</span></a></td>";
                      
                      $oCoopOrderCapacity = new CoopOrderCapacity(
                              $recTable["fMaxBurden"], $recTable["fBurden"], 
                              $recTable["mMaxCoopTotal"], $recTable["mCoopTotal"] ,
                          $recTable["fMaxStorageBurden"], $recTable["fStorageBurden"]);
                      
                      //% full
                      echo '<td>';
                      if ($oCoopOrderCapacity->SelectedType != CoopOrderCapacity::TypeNone)
                        echo $oCoopOrderCapacity->PercentRounded , '%';
                      echo '</td>'; 
                      
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
                      
                      //status
                      echo "<td>" , CoopOrder::StatusName($recTable["nStatus"]) ,  "</td>";
                      
                      //sums
                      echo "<td>";
                      if ($bCanViewSums)
                        echo $recTable["mCoopTotal"];
                      echo "</td>";
                      echo "<td>"; 
                      if ($bCanViewSums)
                        echo $recTable["mProducerTotal"];
                      echo "</td>";
                      
                      echo '<td>';
                      if ($bCanCopy)
                      {
                        echo "<a title='" , htmlspecialchars($recTable["sCoopOrder"]), 
                                "' href='coopordercopy.php?id=" ,  $recTable["CoopOrderKeyID"] , "'><!$LINK_COPY_COOP_ORDER$!></a>";
                      }
                      echo '<td>';
                      
                      echo "<td>";
                      if ( $bCanSetCoord )
                      {
                        echo "<a title='" , htmlspecialchars($recTable["sCoopOrder"]), 
                                "' href='coordinate.php?rid=" , $recTable["CoopOrderKeyID"] ,
                                "&pa=" , Consts::PERMISSION_AREA_COOP_ORDERS;
                        if ($recTable["CoordinatingGroupID"])
                          echo "&id=" ,  $recTable["CoordinatingGroupID"];
                        echo "' ><!$RECORD_COORD$!></a>";
                      }
                      echo "</td>";
                      
                      echo '</tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
?>
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
