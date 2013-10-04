<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oRecord = new CoopOrderPickupLocation;
$oPickupLocs = new PickupLocations;
$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PICKUP;
$oTabInfo->IsSubPage = TRUE;

$arrPickupLocs = NULL;
$sPageTitle = 'מקומות איסוף';
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
               $oRecord->StorageAreas = $oPickupLocation->StorageAreas;
            }
            
            $oRecord->LoadStorageAreas();
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
            $g_oError->AddError('הרשומה נשמרה בהצלחה.', 'ok');   
            
            if(!$oRecord->LoadRecord())
            {
                RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
                exit;
            }
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('הרשומה לא נשמרה. אין לך הרשאות מספיקות או שאירעה שגיאה.');
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
              $g_oError->AddError('הרשומה לא נמחקה.');
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
    $g_oError->AddError('אין מקומות איסוף לבחירה. ייתכן שלא מוגדרים במערכת מקומות איסוף פעילים שלא נבחרו כבר בהזמנה הנוכחית או שאין לך הרשאות מספיקות', 'warning');
    $bReadOnly = TRUE;
  }
  
  //check edit permission
  if (!$bReadOnly && !$oRecord->HasPermission(CoopOrderPickupLocation::PERMISSION_EDIT))
    $bReadOnly = TRUE;
  
  $sPageTitle = $oRecord->Name . ' - מקומות איסוף';
  $oTabInfo->ID = $oRecord->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oRecord->Name;
  $oTabInfo->Status = $oRecord->Status;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oRecord->End, $oRecord->Delivery, $oRecord->Status);
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oRecord->CoopOrderMaxBurden, $oRecord->CoopOrderBurden, $oRecord->CoopOrderMaxCoopTotal, $oRecord->CoopOrderCoopTotal,
      $oRecord->CoopOrderMaxStorageBurden, $oRecord->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  
  $oCoopOrderCapacity = new CoopOrderCapacity(
                              $oRecord->MaxBurden, $oRecord->TotalBurden, 
                              $oRecord->MaxCoopTotal, $oRecord->CoopTotal,
                              $oRecord->MaxStorageBurden, $oRecord->StorageBurden);
  
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
<html dir='rtl' >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function Delete()
{
  if (confirm(decodeXml('נא אשר/י או בטל/י את פעולת המחיקה')))
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
function ActivateStorageArea(sTargetElement, sSourceElement)
{
  if (document.getElementById(sSourceElement).value == "0")
    document.getElementById(sTargetElement).removeAttribute("readonly");
  else
    document.getElementById(sTargetElement).setAttribute("readonly", "1");
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
        <td width="908"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>    
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" >
                <table cellspacing="0" cellpadding="0"  width="100%">
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
                  <?php if ($g_oError->HadError || $bReadOnly ) echo ' disabled="disabled" '; ?>>שמירה</button>&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" <?php 
                      if ($g_oError->HadError || !$oRecord->IsExistingRecord || $bReadOnly || $oRecord->CoopTotal > 0 ) 
                        echo ' disabled="disabled" '; 
                      ?> >מחיקה</button>
                  </td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <td colspan="2"></td>
                <?php
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                <tr>
                  <?php
                    $selPickupLoc = new HtmlSelectArray('PickupLocationKeyID', 'מקום האיסוף', $arrPickupLocs, $oRecord->PickupLocationID);
                    $selPickupLoc->Required = TRUE;
                    $selPickupLoc->ReadOnly = $bReadOnly;
                    $selPickupLoc->OnChange = "JavaScript:SelectPickupLocation();";
                    $selPickupLoc->EchoHtml();
                    unset($selPickupLoc);
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php 
                    $txtMaxBurden = new HtmlTextEditNumeric('קבולת משלוח', 'txtMaxBurden', $oRecord->MaxBurden);
                    $txtMaxBurden->ReadOnly = $bReadOnly;
                    $txtMaxBurden->EchoHtml();
                    unset($txtMaxBurden);

                    HtmlTextEditMultiLang::EchoHelpText('הגבלת גודל המשלוח למקום איסוף זה לפי הקבולת הכוללת של המוצרים שיגיעו אליו. חברות/ים לא יוכלו להשלים הזמנה שחורגת מההגבלה שהוגדרה כאן.');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php    
                    $sTotalBurden = $oRecord->TotalBurden;
                    if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Burden->CanCompute)
                      $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Burden->PercentRounded . '%)');
                  
                    $lblTotalBurden = new HtmlTextLabel('סה&quot;כ מעמסה', 'txtTotalBurden', $sTotalBurden);
                    $lblTotalBurden->EchoHtml();
                    unset($lblTotalBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('הסכום הכולל של ערך מעמסה של כל מוצר שהוזמן בהזמנת הקואופרטיב עבור מקום האיסוף כפול מספר הפעמים שהוזמן');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php           
                     if ($bShowSums)
                     {
                       $txtMaxCoopTotal = new HtmlTextEditNumeric('מכסת סכום לקואופ', 'txtMaxCoopTotal', 
                              $oRecord->MaxCoopTotal);
                       $txtMaxCoopTotal->ReadOnly = $bReadOnly;
                       $txtMaxCoopTotal->EchoHtml();
                       unset($txtMaxCoopTotal);
                     }
                     else
                       echo '<td colspan="2"></td>';
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php    
                    if ($bShowSums)
                    {
                      $sCoopTotal = $oRecord->CoopTotal;
                      if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Total->CanCompute)
                        $sCoopTotal .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Total->PercentRounded . '%)');

                      $txtCoopTotal = new HtmlTextLabel('סכום לקואופ', 'txtCoopTotal', $sCoopTotal);
                      $txtCoopTotal->EchoHtml();
                      unset($txtCoopTotal);
                    }
                    else
                       echo '<td colspan="2"></td>';
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                
                <tr>
                  <?php
                    $lblMaxStorageBurden = new HtmlTextLabel('סה&quot;כ קיבולת אחסון', 'lblMaxStorageBurden', 
                        $oRecord->MaxStorageBurden);
                    $lblMaxStorageBurden->EchoHtml();
                    unset($lblMaxStorageBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('סה&quot;כ קיבולת אחסון של כל מקומות האחסון במקום האיסוף');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php
                    $sTotalBurden = $oRecord->StorageBurden;
                    if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->StorageBurden->CanCompute)
                      $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->StorageBurden->PercentRounded . '%)');
                    
                    $lblStorageBurden = new HtmlTextLabel('סה&quot;כ תפוסת אחסון', 'lblStorageBurden', 
                        $sTotalBurden);
                    $lblStorageBurden->EchoHtml();
                    unset($lblStorageBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('סה&quot;כ תפוסת אחסון של כל מקומות האחסון במקום האיסוף');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <?php
                //STORAGE AREAS
                $nCount = 0;
                foreach ($oRecord->StorageAreas as $aStorageArea)
                {
                  HtmlTextEditMultiLang::EchoSeparatorLine();
                  
                  $nCount++;

                  echo '<tr>';

                  $lblStorageArea = new HtmlTextLabel(sprintf('מקום אחסון #%s', $nCount), 
                      'lblStorageArea_' . $aStorageArea['StorageAreaKeyID'], $aStorageArea['sStorageArea']);
                  $lblStorageArea->EchoHtml();
                  unset($lblStorageArea);

                  //put inactive/active dropdown without label in help slot
                  $selIsDisabled = new HtmlSelectBoolean(CoopOrderPickupLocation::CTL_STORAGE_AREA_DISABLED . $aStorageArea['StorageAreaKeyID'], '',
                    $aStorageArea['bDisabled'], 'לא פעיל', 
                    'פעיל');
                  $selIsDisabled->OmitLabel = TRUE;
                  $selIsDisabled->ReadOnly = $bReadOnly;
                  $selIsDisabled->OnChange = 'JavaScript:ActivateStorageArea(\'' . CoopOrderPickupLocation::CTL_STORAGE_AREA_MAX_BURDEN . 
                      $aStorageArea['StorageAreaKeyID'] . '\', \'' . CoopOrderPickupLocation::CTL_STORAGE_AREA_DISABLED . 
                      $aStorageArea['StorageAreaKeyID']  . '\');';
                  $selIsDisabled->EchoHtml();
                  
                  //CoopOrderPickupLocation::CTL_STORAGE_AREA_MAX_BURDEN . $aStorageArea['StorageAreaKeyID']

                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  echo '</tr>',
                       '<tr>';

                  $txtMaxBurden = new HtmlTextEditNumeric('קבולת משלוח', 
                      CoopOrderPickupLocation::CTL_STORAGE_AREA_MAX_BURDEN . $aStorageArea['StorageAreaKeyID'], $aStorageArea['fMaxBurden']);
                  $txtMaxBurden->ReadOnly = $bReadOnly  || $aStorageArea['bDisabled'];
                  $txtMaxBurden->EchoHtml();
                  unset($txtMaxBurden);

                  HtmlTextEditMultiLang::EchoHelpText('הגבלת גודל המשלוח למקום איסוף זה לפי הקבולת הכוללת של המוצרים שיגיעו אליו. חברות/ים לא יוכלו להשלים הזמנה שחורגת מההגבלה שהוגדרה כאן.');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();

                  echo '</tr>';
                  
                  echo '<tr>';

                  $sTotalBurden = $aStorageArea['fBurden']; 
                  
                  if (isset($aStorageArea['fBurden']))
                  {
                    $oCoopOrderCapacity = new CoopOrderCapacity(
                            $aStorageArea['fMaxBurden'], $aStorageArea['fBurden'], 
                            NULL, NULL );
                    if ($oCoopOrderCapacity->Burden->CanCompute)
                    {                      
                      $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Burden->PercentRounded . '%)');
                    }
                  }

                  $lblTotalBurden = new HtmlTextLabel('סה&quot;כ מעמסה', 'lblTotalBurden_' .
                      $aStorageArea['StorageAreaKeyID'], $sTotalBurden);
                  $lblTotalBurden->EchoHtml();
                  unset($lblTotalBurden);

                  HtmlTextEditMultiLang::EchoHelpText('הסכום הכולל של ערך מעמסה של כל מוצר שהוזמן בהזמנת הקואופרטיב עבור מקום האחסון כפול מספר הפעמים שהוזמן');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();

                  echo '</tr>';
                }
                //END STORAGE AREAS
                ?>
                </table>
                </td></tr></table>
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
