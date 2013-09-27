<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderPickupLocationProducers;
$recTable = NULL;
$oTabInfo = new CoopOrderTabInfo;
$oPLTabInfo = NULL;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PICKUP;
$oTabInfo->IsSubPage = TRUE;
$oCoopOrderCapacity = NULL;
$sPageTitle = '';

try
{
  if (isset($_GET['coid']))
    $oData->CoopOrderID = intval($_GET['coid']);
  
  if (isset($_GET['plid']))
    $oData->PickupLocationID = intval($_GET['plid']);
    
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

  if ($oData->CoopOrderID <= 0 || $oData->PickupLocationID <= 0)
  {
     RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
     exit;
  }

  $sPageTitle = sprintf('Producers by Pickup Location %s', $oData->PickupLocationName);
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal,
      $oData->CoopOrderMaxStorageBurden, $oData->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);

  $oPLTabInfo = new CoopOrderPickupLocationTabInfo($oData->CoopOrderID, $oData->PickupLocationID, $oData->PickupLocationName, 
          CoopOrderPickupLocationTabInfo::PAGE_PRODUCERS);
  $oPLTabInfo->CoordinatingGroupID = $oData->PickupLocationCoordGroupID;
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
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
            <td width="780" height="100%" >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="4"><?php include_once '../control/coopordertab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php include_once '../control/copickuploctab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="4"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td class="columntitlelong">Producer</td>
              <td class="columntitleshort">Producer Total</td>
              <td class="columntitleshort">Total Coop</td>
              <td class="columntitlenowidth" ><a class="tooltip" 
               href="#" >Total Delivery<span>This sum refers to the delivery costs to all pickup locations in the current cooperative order for each producer</span></a></td>
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
                      echo "<tr>";
                      
                      echo "<td>" ,  htmlspecialchars($recTable["sProducer"]) ,  "</td>";

                      //ProducerTotal
                      echo '<td>' , $recTable["mProducerTotal"], '</td>';
       
                      //CoopTotal
                      echo '<td>' , $recTable["mCoopTotal"], '</td>';
                      
                      //total delivery
                      echo '<td>' , $recTable["mTotalDelivery"], '</td>';

                      echo '</tr>';

                      $recTable = $oData->fetch();
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