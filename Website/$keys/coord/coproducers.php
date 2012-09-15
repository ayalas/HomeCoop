<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderProducers;
$recTable = NULL;
$oTabInfo = new CoopOrderTabInfo;
$bReadOnly = FALSE;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PRODUCERS;
$oCoopOrderCapacity = NULL;
$sPageTitle = '<!$TAB_ORDER_PRODUCERS$!>';

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

  $sPageTitle = $oData->Name . '<!$PAGE_TITLE_SEPARATOR$!><!$TAB_ORDER_PRODUCERS$!>';
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);

  if (!$oData->HasPermission(CoopOrderProducers::PERMISSION_COOP_ORDER_PRODUCER_EDIT))
      $bReadOnly = TRUE;
  else if ($oData->Status != CoopOrder::STATUS_ACTIVE && $oData->Status != CoopOrder::STATUS_DRAFT)
  {
    $bReadOnly = TRUE;
    $g_oError->AddError('<!$COOP_ORDER_CANNOT_BE_UPDATED_AT_THIS_STATUS$!>');
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
<title><!$COOPERATIVE_NAME$!>: <?php echo $sPageTitle;  ?></title>
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
        <td width="<!$TOTAL_PAGE_WIDTH$!>"><span class="coopname"><!$COOPERATIVE_NAME$!>:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
            <td width="<!$COORD_PAGE_WIDTH$!>" height="100%" >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="5"><?php include_once '../control/coopordertab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="5"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td colspan="5"><?php if (!$bReadOnly)
                echo '<a href="coproducer.php?coid=' , $oData->CoopOrderID , '" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a>';
                ?></td>
            </tr>
            <tr>
              <td class="columntitlelong"><!$FIELD_PRODUCER$!></td>
              <td class="columntitle"><a class="tooltip" href="#" ><!$FIELD_COOP_ORDER_TOTAL_BURDEN$!><span><!$TOOLTIP_COOP_ORDER_PRODUCER_TOTAL_BURDEN$!></span></a></td>              
              <td class="columntitle"><!$FIELD_PRODUCER_TOTAL$!></td>
              <td class="columntitle"><!$FIELD_COOP_ORDER_COOP_TOTAL$!></td>
              <td class="columntitlenowidth"><!$FIELD_COOP_ORDER_PRODUCER_TOTAL_DELIVERY$!></td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='5'>&nbsp;</td></tr><tr><td align='center' colspan='5'><!$NO_RECORD_FOUND$!></td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      //name
                      echo "<tr><td><a href='coproducer.php?pid=" , $recTable["ProducerKeyID"] , "&coid="
                              , $oData->CoopOrderID , "' >" ,  htmlspecialchars($recTable["sProducer"]) , "</a></td>";                     
                      
                      $oCoopOrderCapacity = new CoopOrderCapacity(
                              $recTable["fMaxBurden"], $recTable["fBurden"], 
                              $recTable["mMaxProducerOrder"], $recTable["mProducerTotal"] );
                     
                      //burden
                      echo '<td>' , Rounding::Round($recTable["fBurden"], ROUND_SETTING_BURDEN);
                      if ($oCoopOrderCapacity->Burden->CanCompute)
                        echo ' (' , $oCoopOrderCapacity->Burden->PercentRounded , '%)';
                      echo '</td>';                  
                      
                      //ProducerTotal
                      echo '<td>' , $recTable["mProducerTotal"];
                      if ($oCoopOrderCapacity->Total->CanCompute)
                        echo ' (' , $oCoopOrderCapacity->Total->PercentRounded , '%)';                      
                      echo '</td>';
       
                      //CoopTotal
                      echo '<td>' , $recTable["mCoopTotal"], '</td>';                     
                      
                      //delivery
                      echo '<td>' , $recTable["mTotalDelivery"] , '</td>',

                       '</tr>';

                      $recTable = $oData->fetch();
                  }
                }
    ?>
                </table>
                </td>
                <td width="<!$COORD_PANEL_WIDTH$!>" >
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
