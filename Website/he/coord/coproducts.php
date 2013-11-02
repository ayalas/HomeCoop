<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderProducts;
$recTable = NULL;
$oTabInfo = new CoopOrderTabInfo;
$bReadOnly = FALSE;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PRODUCTS;
$sTooltipLines = NULL;
$bTooltip = FALSE;
$sLink = NULL;
$sPageTitle = 'מוצרים';
try
{
  if (isset($_GET['id']))
    $oData->CoopOrderID = intval($_GET['id']);
    
  $recTable = $oData->LoadData();
  
  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ($oData->CoopOrderID <= 0)
  {
     RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
     exit;
  }

  $sPageTitle = $oData->Name . ' - מוצרים';
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal,
      $oData->CoopOrderMaxStorageBurden, $oData->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);

  if (!$oData->HasPermission(CoopOrderProducts::PERMISSION_COOP_ORDER_PRODUCT_EDIT))
    $bReadOnly = TRUE;
  else if ($oData->Status != CoopOrder::STATUS_ACTIVE 
          && $oData->Status != CoopOrder::STATUS_DRAFT
          && $oData->Status != CoopOrder::STATUS_LOCKED )
  {
    $bReadOnly = TRUE;
    $g_oError->AddError('לא ניתן לעדכן את הזמנת הקואופרטיב במצב הנוכחי שלה', 'warning');
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
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function OpenPartialOrders(nProductID)
{
  window.open("partialorders.php?prd=" + nProductID + "&coid=<?php echo $oData->CoopOrderID; ?>",  "vcpartialorders", 
  "status=0,toolbar=0,menubar=0,top=150, left=100, width=700,height=" + (screen.availHeight-250) );
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oData->CoopOrderID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="948"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="8"><?php include_once '../control/coopordertab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="8"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td colspan="8"><?php if (!$bReadOnly)
                echo '<a href="coproduct.php?coid=' , $oData->CoopOrderID , '" ><img border="0" title="הוספה" src="../img/edit-add-2.png" /></a>';
                ?></td>
            </tr>
            <tr>
              <td class="columntitlelong">מוצר</td>
              <td class="columntitlelong">יצרן</td>
              <td class="columntitletiny">סה&quot;כ</td>
              <td class="columntitleshort">סה&quot;כ ליצרן</td>
              <td class="columntitleshort">מ. יצרן</td>
              <td class="columntitleshort">מ. קואופ</td>
              <td class="columntitleshort">כמות</td>
              <td class="columntitlenowidth" ><a class="tooltip" href="#" >מעמסה<span>מדד שמציין כמה מוצר זה &quot;מכביד&quot; על המשלוח. מאפשר לעמוד במכסת גודל משלוח, אותה אפשר להגדיר בהזמנת הקואופרטיב</span></a></td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='8'>&nbsp;</td></tr><tr><td align='center' colspan='7'>לא נמצאו רשומות.</td></tr>";
                }
                else
                {//PackageSize
                  while ( $recTable )
                  {
                      //name
                      $sTooltipLines = '';
                      $bTooltip = FALSE;
                      if ($recTable["fMaxUserOrder"] != NULL)
                      {
                        $sTooltipLines .= sprintf("<div>%s: %s פריטים</div>", "מכסת הזמנה לחבר/ה", $recTable["fMaxUserOrder"]);
                        $bTooltip = TRUE;
                      }
                      if ($recTable["fMaxCoopOrder"] != NULL)
                      {
                        $sTooltipLines .= sprintf("<div>%s: %s פריטים</div>", "מכסת הזמנה לקואופ", $recTable["fMaxCoopOrder"]);
                        $bTooltip = TRUE;
                      }
                      
                      $oProductPackage = new ProductPackage(
                              $recTable["ProductItems"], $recTable["fItemQuantity"], $recTable["sItemUnitAbbrev"], 
                              $recTable["fUnitInterval"], $recTable["sUnitAbbrev"], $recTable["fPackageSize"], $recTable["ProductQuantity"],
                              $recTable["fMaxCoopOrder"], $recTable["fTotalCoopOrder"]
                      );
                      
                      if ($oProductPackage->HasTooltip)
                        $bTooltip = TRUE;
                      
                      echo "<tr>";

                      //product name
                      echo "<td><a ";
                      if ($bTooltip)
                        echo " class='tooltiphelp' ";
                      
                      echo " href='coproduct.php?prdid=" ,  $recTable["ProductKeyID"] , "&coid="
                              , $oData->CoopOrderID , "' >" , htmlspecialchars($recTable["sProduct"] );                      
                      
                      if ($bTooltip)
                      {
                        echo "<span>" , $sTooltipLines;
                        if ($oProductPackage->HasTooltip)
                          $oProductPackage->EchoTooltip();
                        echo "</span>";
                      }
                      echo "</a></td>";
                      
                      //producer
                      echo "<td>" , htmlspecialchars($recTable["sProducer"]),"</td>";
                      
                      //quantity
                      $fPackageSize = $recTable["fPackageSize"];
                      if ($fPackageSize == NULL)
                        $fPackageSize = $recTable["ProductQuantity"];

                      echo '<td'; 
                      if ( fmod($recTable["fTotalCoopOrder"], $fPackageSize) != 0 )
                        echo ' class="alarmingdata" ';
                      
                      echo '>'; 
                      
                      echo '<span class="link" onclick="JavaScript:OpenPartialOrders(' , $recTable["ProductKeyID"]  ,
                              ');" >' , $recTable["fTotalCoopOrder"] , '</span>';
                      
                      echo '</td>';

                      //sum
                      echo '<td>';
                      
                      if ( $recTable["nJoinedStatus"] != CoopOrderProduct::JOIN_STATUS_NONE )
                      {
                        echo '<a href="coproduct.php?prdid=' ,  $recTable["ProductKeyID"] , '&coid='
                              , $oData->CoopOrderID,'" class="tooltiphelp">', $recTable["mProducerTotal"],'<span>';
                        if ( $recTable["nJoinedStatus"] == CoopOrderProduct::JOIN_STATUS_JOINED )
                          echo 'לפחות חלק מהכמות של מוצר זה צורפה למוצר מקושר';
                        else
                          echo 'לכמות של המוצר הזה צורפה כמות ממוצר מקושר';
                        echo '</span></a>';
                      }
                      else
                        echo $recTable["mProducerTotal"];
                      
                      echo '</td>';
                      

                      //Producer Price
                      echo '<td>' , $recTable["mProducerPrice"] , '</td>';
                      
                      //Coop Price
                      echo '<td>' , $recTable["mCoopPrice"] , '</td>';
                      
                      //PackageSize
                      
                      
                      echo '<td>'; 
                      $oProductPackage->SuppressTooltip = TRUE;
                      $oProductPackage->EchoHtml();
                      echo '</td>';
                      
                      //burden
                      echo '<td>' , Rounding::Round($recTable["fBurden"], ROUND_SETTING_BURDEN) , '</td>',

                       '</tr>';

                      $recTable = $oData->fetch();
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

