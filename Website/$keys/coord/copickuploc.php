<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oRecord = new CoopOrderPickupLocation;
$oPickupLocs = new PickupLocations;
$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PICKUP;
$oTabInfo->IsSubPage = TRUE;

$arrPickupLocs = NULL;
$sPageTitle = '<!$TAB_ORDER_PICKUP_LOCATIONS$!>';
$bReadOnly = FALSE;
$bShowSums = FALSE;

$oPLTabInfo = NULL;

$oCoopOrderCapacity = NULL;

try
{
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {   
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    $oRecord->CopyCoopOrderData();
    $oRecord->PreserveUnsavedData();
    
    $sCtl = HtmlSelectArray::PREFIX . 'PickupLocationKeyID';
    if ( isset( $_POST[$sCtl] ))
      $oRecord->PickupLocationID = intval($_POST[$sCtl]);
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case CoopOrderPickupLocation::POST_ACTION_SELECT_LOCATION:
          
          //get product defaults
          if ($oRecord->PickupLocationID > 0)
          {
            $oPickupLocation = new PickupLocation;
            if ($oPickupLocation->LoadCOPickupLocationDefaults($oRecord->PickupLocationID))
            {
               $oRecord->MaxBurden = $oPickupLocation->MaxBurden;
               $oRecord->PickupLocationName = $oPickupLocation->Name;
            }
            $oRecord->AddCoordinatorPermissionBridges();
          }
          break;
        case SQLBase::POST_ACTION_SAVE:
          //collect data
          $oRecord->MaxBurden = NULL;
          if ( isset($_POST['txtMaxBurden']) && !empty($_POST['txtMaxBurden']))
             $oRecord->MaxBurden = 0 + trim($_POST['txtMaxBurden']);
          
          $oRecord->MaxCoopTotal = NULL;
          if ( isset($_POST['txtMaxCoopTotal']) && !empty($_POST['txtMaxCoopTotal']))
             $oRecord->MaxCoopTotal = 0 + trim($_POST['txtMaxCoopTotal']);
                     
          $bSuccess = false;
          if ($oRecord->IsExistingRecord)
            $bSuccess = $oRecord->Edit();
          else
            $bSuccess = $oRecord->Add();

          if ( $bSuccess )
          {
            $g_oError->AddError('<!$RECORD_SAVED$!>', 'ok');   
            
            if(!$oRecord->LoadRecord())
            {
                RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
                exit;
            }
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('<!$RECORD_NOT_SAVED$!>');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $nCOID = $oRecord->CoopOrderID;
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('copickuplocs.php?id=' . $nCOID);
              exit;
          }
          else
              $g_oError->AddError('<!$DELETE_FAILURE$!>');
          break;
      }
    }
  }
  else
  {
    if (isset($_GET['coid']))
      $oRecord->CoopOrderID = intval($_GET['coid']);
    
    if (isset($_GET['plid']))
      $oRecord->PickupLocationID = intval($_GET['plid']); 
    
    if(!$oRecord->LoadRecord())
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }
  }

  switch($oRecord->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $arrPickupLocs = $oPickupLocs->GetListForCoopOrder($oRecord->PickupLocationID, $oRecord->CoopOrderID   );
  
  $bReadOnly = ($oRecord->Status != CoopOrder::STATUS_ACTIVE 
          && $oRecord->Status != CoopOrder::STATUS_DRAFT
          && $oRecord->Status != CoopOrder::STATUS_LOCKED );
  
  //check if empty list
  if (!is_array($arrPickupLocs) || count($arrPickupLocs) == 0)
  {
    $g_oError->AddError('<!$COOP_ORDER_PICKUP_LOCATION_LIST_IS_EMPTY$!>', 'warning');
    $bReadOnly = TRUE;
  }
  
  //check edit permission
  if (!$bReadOnly && !$oRecord->HasPermission(CoopOrderPickupLocation::PERMISSION_EDIT))
    $bReadOnly = TRUE;
  
  $sPageTitle = $oRecord->Name . '<!$PAGE_TITLE_SEPARATOR$!><!$TAB_ORDER_PICKUP_LOCATIONS$!>';
  $oTabInfo->ID = $oRecord->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oRecord->Name;
  $oTabInfo->Status = $oRecord->Status;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oRecord->End, $oRecord->Delivery, $oRecord->Status);
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oRecord->CoopOrderMaxBurden, $oRecord->CoopOrderBurden, $oRecord->CoopOrderMaxCoopTotal, $oRecord->CoopOrderCoopTotal);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  
  $oCoopOrderCapacity = new CoopOrderCapacity(
                              $oRecord->MaxBurden, $oRecord->TotalBurden, 
                              $oRecord->MaxCoopTotal, $oRecord->CoopTotal );
  
  $oPLTabInfo = new CoopOrderPickupLocationTabInfo( $oRecord->CoopOrderID, $oRecord->PickupLocationID, $oRecord->PickupLocationName, 
        CoopOrderPickupLocationTabInfo::PAGE_PICKUP_LOCATION );
  $oPLTabInfo->CoordinatingGroupID = $oRecord->PickupLocationCoordinatingGroupID;
  $oPLTabInfo->IsExistingRecord = $oRecord->IsExistingRecord;
  
  $bShowSums = $oRecord->HasPermission(CoopOrderPickupLocation::PERMISSION_SUMS);
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
<script type="text/javascript" >
function Delete()
{
  if (confirm(decodeXml('<!$ARE_YOU_SURE_DELETE_MSG$!>')))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
//when selecting a Pickup Location, get its default values
function SelectPickupLocation()
{
  document.getElementById("hidPostAction").value = <?php echo CoopOrderPickupLocation::POST_ACTION_SELECT_LOCATION; ?>;
  document.frmMain.submit();
}

</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="<!$TOTAL_PAGE_WIDTH$!>"><span class="coopname"><!$COOPERATIVE_NAME$!>:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>    
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="<!$COORD_PAGE_WIDTH$!>" >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                  <td><?php include_once '../control/coopordertab.php'; ?></td>
                </tr>
                <tr>
                  <td><?php include_once '../control/copickuploctab.php'; ?></td>
                </tr>
                <tr>
                  <td><?php include_once '../control/error/ctlError.php'; ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError || $bReadOnly ) echo ' disabled="disabled" '; ?>><!$BTN_SAVE$!></button>&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" <?php 
                      if ($g_oError->HadError || !$oRecord->IsExistingRecord || $bReadOnly || $oRecord->CoopTotal > 0 ) 
                        echo ' disabled="disabled" '; 
                      ?> ><!$BTN_DELETE$!></button>
                  </td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <td></td>
                <?php
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                <tr>
                  <?php
                    $selPickupLoc = new HtmlSelectArray('PickupLocationKeyID', '<!$FIELD_PICKUP_LOCATION_NAME$!>', $arrPickupLocs, $oRecord->PickupLocationID);
                    $selPickupLoc->Required = TRUE;
                    $selPickupLoc->ReadOnly = $bReadOnly;
                    $selPickupLoc->OnChange = "JavaScript:SelectPickupLocation();";
                    $selPickupLoc->EchoHtml();
                    unset($selPickupLoc);
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php 
                    $txtMaxBurden = new HtmlTextEditNumeric('<!$FIELD_PICKUP_LOCATION_MAX_BURDEN$!>', 'txtMaxBurden', $oRecord->MaxBurden);
                    $txtMaxBurden->ReadOnly = $bReadOnly;
                    $txtMaxBurden->EchoHtml();
                    unset($txtMaxBurden);

                    HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_PICKUP_LOCATION_MAX_BURDEN$!>');
                  ?>
                </tr>
                <tr>
                  <?php    
                    $sTotalBurden = $oRecord->TotalBurden;
                    if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Burden->CanCompute)
                      $sTotalBurden .= ' (' . $oCoopOrderCapacity->Burden->PercentRounded . '%)';
                  
                    $lblTotalBurden = new HtmlTextLabel('<!$FIELD_COOP_ORDER_TOTAL_BURDEN$!>', 'txtTotalBurden', $sTotalBurden);
                    $lblTotalBurden->EchoHtml();
                    unset($lblTotalBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_PICKUP_LOCATION_TOTAL_BURDEN$!>');
                  ?>
                </tr>
                <tr>
                  <?php           
                     if ($bShowSums)
                     {
                       $txtMaxCoopTotal = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_MAX_COOP_TOTAL$!>', 'txtMaxCoopTotal', 
                              $oRecord->MaxCoopTotal);
                       $txtMaxCoopTotal->ReadOnly = $bReadOnly;
                       $txtMaxCoopTotal->EchoHtml();
                       unset($txtMaxCoopTotal);
                     }
                     else
                       echo '<td colspan="2"></td>';
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php    
                    if ($bShowSums)
                    {
                      $sCoopTotal = $oRecord->CoopTotal;
                      if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Total->CanCompute)
                        $sCoopTotal .= ' (' . $oCoopOrderCapacity->Total->PercentRounded . '%)';

                      $txtCoopTotal = new HtmlTextLabel('<!$FIELD_COOP_ORDER_COOP_TOTAL$!>', 'txtCoopTotal', $sCoopTotal);
                      $txtCoopTotal->EchoHtml();
                      unset($txtCoopTotal);
                    }
                    else
                       echo '<td colspan="2"></td>';
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                </table>
                </td></tr></table>
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
