<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderPickupLocations;
$recTable = NULL;
$oTabInfo = new CoopOrderTabInfo;
$bReadOnly = FALSE;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PICKUP;
$oCoopOrderCapacity = NULL;
$bShowSums = FALSE;
$sPageTitle = 'Pickup Locations';

try
{
  if (isset($_GET['id']))
    $oData->CoopOrderID = intval($_GET['id']);
    
  $recTable = $oData->LoadData();
  $bShowSums = $oData->HasPermission(CoopOrderPickupLocations::PERMISSION_SUMS);
  
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

  $sPageTitle = $oData->Name . ' - Pickup Locations';
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

  if (!$oData->HasPermission(CoopOrderPickupLocations::PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT))
      $bReadOnly = TRUE;
  else if ($oData->Status != CoopOrder::STATUS_ACTIVE 
          && $oData->Status != CoopOrder::STATUS_DRAFT
          && $oData->Status != CoopOrder::STATUS_LOCKED )
  {
    $bReadOnly = TRUE;
    $g_oError->AddError('Cooperative order cannot be updated at its current status');
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>Enter Your Cooperative Name: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oData->CoopOrderID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="4"><?php include_once '../control/coopordertab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php if (!$bReadOnly)
                echo '<a href="copickuploc.php?coid=' , $oData->CoopOrderID , '" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a>';
                ?></td>
            </tr>
            <tr>
              <td class="columntitlelong">Location Name</td>
              <td class="columntitlelong">Address</td>
              <td class="columntitle"><a class="tooltip" href="#" >Total Burden<span>The sum total of each ordered product &quot;Burden&quot; multiplied by the times it was ordered, per pickup location</span></a></td>
              <td class="columntitle"><a class="tooltip" href="#" >Total storage full<span>Total storage areas used space.</span></a></td>
              <td class="columntitlenowidth"><?php if ($bShowSums) echo 'Total Coop'; ?></td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='4'>&nbsp;</td></tr><tr><td align='center' colspan='4'>No records.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      //name
                      echo "<tr><td><a href='copickuploc.php?plid=" , $recTable["PickupLocationKeyID"] , "&coid="
                              , $oData->CoopOrderID , "' >" ,  htmlspecialchars($recTable["sPickupLocation"]) ,  "</a></td>",

                      //address
                       "<td>";

                      $cellAddress = new HtmlGridCellText( $recTable["sAddress"], HtmlGridCellText::CELL_TYPE_LONG );
                      $cellAddress->EchoHtml();
                      unset($cellAddress);

                      echo "</td>";

                      $oCoopOrderCapacity = new CoopOrderCapacity(
                              $recTable["fMaxBurden"], $recTable["fBurden"], 
                              $recTable["mMaxCoopTotal"], $recTable["mCoopTotal"], 
                              $recTable["fMaxStorageBurden"], $recTable["fStorageBurden"] );
                     
                      //burden
                      echo '<td>' , Rounding::Round($recTable["fBurden"], ROUND_SETTING_BURDEN);
                      if ($oCoopOrderCapacity->Burden->CanCompute)
                        LanguageSupport::EchoInFixedOrder('&nbsp;', '(' . $oCoopOrderCapacity->Burden->PercentRounded . '%)');
                      
                      echo '</td>';   
                      
                      //storage burden
                      echo '<td>' , Rounding::Round($recTable["fStorageBurden"], ROUND_SETTING_BURDEN);
                      if ($oCoopOrderCapacity->StorageBurden->CanCompute)
                        LanguageSupport::EchoInFixedOrder('&nbsp;', '(' . $oCoopOrderCapacity->StorageBurden->PercentRounded . '%)');
                      
                      echo '</td>'; 
                      
                      //CoopTotal
                      echo '<td>';
                      if ($bShowSums)
                      {
                        echo $recTable["mCoopTotal"];
                        if ($oCoopOrderCapacity->Total->CanCompute)
                          LanguageSupport::EchoInFixedOrder('&nbsp;', '(' . $oCoopOrderCapacity->Total->PercentRounded . '%)');
                      }
                      echo '</td>',

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
