<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oRecord = new CoopOrderProducer;

$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PRODUCERS;
$oTabInfo->IsSubPage = TRUE;
$arrProducers = NULL;
$sPageTitle = 'Producers';
$oCoopOrderCapacity = NULL;
$bReadOnly = FALSE;

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

    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          //collect data
          if (!$oRecord->IsExistingRecord) //get producer only on new record. It cannot be changed
          {
            $sCtl = HtmlSelectArray::PREFIX . 'ProducerID';
            if ( isset( $_POST[$sCtl] ))
              $oRecord->ProducerID = intval($_POST[$sCtl]);
          }
          else //get from original value
            $oRecord->ProducerID = $oRecord->OriginalProducerID;

          $oRecord->DeliveryPercent = NULL;
          if ( isset($_POST['txtDeliveryPercent']) && !empty($_POST['txtDeliveryPercent']))
             $oRecord->DeliveryPercent = 0 + trim($_POST['txtDeliveryPercent']);
          
          $oRecord->MinDelivery = NULL;
          if ( isset($_POST['txtMinDelivery']) && !empty($_POST['txtMinDelivery']))
             $oRecord->MinDelivery = 0 + trim($_POST['txtMinDelivery']);
          
          $oRecord->MaxDelivery = NULL;
          if ( isset($_POST['txtMaxDelivery']) && !empty($_POST['txtMaxDelivery']))
             $oRecord->MaxDelivery = 0 + trim($_POST['txtMaxDelivery']);
          
          $oRecord->FixedDelivery = NULL;
          if ( isset($_POST['txtFixedDelivery']) && !empty($_POST['txtFixedDelivery']))
             $oRecord->FixedDelivery = 0 + trim($_POST['txtFixedDelivery']);
          
          $oRecord->MaxProducerOrder = NULL;
          if ( isset($_POST['txtMaxProducerOrder']) && !empty($_POST['txtMaxProducerOrder']))
             $oRecord->MaxProducerOrder = 0 + trim($_POST['txtMaxProducerOrder']);
          
          $oRecord->MaxBurden = NULL;
          if ( isset($_POST['txtMaxBurden']) && !empty($_POST['txtMaxBurden']))
             $oRecord->MaxBurden = 0 + trim($_POST['txtMaxBurden']);

          $bSuccess = false;
          if ($oRecord->IsExistingRecord)
            $bSuccess = $oRecord->Edit();
          else
          {
            $bSuccess = $oRecord->Add();
            if ($bSuccess) 
              $oRecord->LoadRecord(); //loads default values and producer name
          }

          if ( $bSuccess )
            $g_oError->AddError('Record saved successfully.', 'ok');   
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $nCOID = $oRecord->CoopOrderID;
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('coproducers.php?id=' . $nCOID);
              exit;
          }
          else
              $g_oError->AddError('The record was not deleted.');
          break;
      }
    }
  }
  else
  {
    if (isset($_GET['coid']))
      $oRecord->CoopOrderID = intval($_GET['coid']);
    
    if (isset($_GET['pid']))
      $oRecord->ProducerID = intval($_GET['pid']);
    
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

  $bReadOnly = ($oRecord->Status != CoopOrder::STATUS_ACTIVE 
          && $oRecord->Status != CoopOrder::STATUS_DRAFT
          && $oRecord->Status != CoopOrder::STATUS_LOCKED );
  
  if (!$bReadOnly && !$oRecord->IsExistingRecord)
  {
    $oProducers = new Producers;
    $arrProducers = $oProducers->GetListForCoopOrder($oRecord->ProducerID, $oRecord->CoopOrderID );
    if (!$oProducers->HasPermission(SQLBase::PERMISSION_COORD)) //completely denied access
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }

    //check if empty list
    if (!is_array($arrProducers) || count($arrProducers) == 0)
    {
      $g_oError->AddError('There are no producer records to add.','warning');
      $bReadOnly = TRUE;
    }
  }
  
  //if doesn't have edit permission, set form to read only
  if (!$bReadOnly && !$oRecord->HasPermission(SQLBase::PERMISSION_EDIT))
   $bReadOnly = TRUE;
  
  $sPageTitle = $oRecord->Name . ' - Producers';
  $oTabInfo->ID = $oRecord->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oRecord->Name;
  $oTabInfo->Status = $oRecord->Status;
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oRecord->End, $oRecord->Delivery, $oRecord->Status);
  $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oRecord->CoopOrderMaxBurden, $oRecord->CoopOrderBurden, $oRecord->CoopOrderMaxCoopTotal, $oRecord->CoopOrderCoopTotal,
      $oRecord->CoopOrderMaxStorageBurden, $oRecord->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  
  $oCoopOrderCapacity = new CoopOrderCapacity(
                              $oRecord->MaxBurden, $oRecord->TotalBurden, 
                              $oRecord->MaxProducerOrder, $oRecord->ProducerTotal );
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
<script type="text/javascript" >
function Delete()
{
  if (confirm(decodeXml('Please confirm or cancel the delete operation')))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
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
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td>
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                  <td><?php include_once '../control/coopordertab.php'; ?></td>
                </tr>
                <tr>
                  <td><?php include_once '../control/error/ctlError.php'; ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError || $bReadOnly ) echo ' disabled="disabled" '; ?> >Save</button>&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" <?php 
                      if ($g_oError->HadError || !$oRecord->IsExistingRecord || $bReadOnly || $oRecord->ProducerTotal > 0 ) 
                        echo ' disabled="disabled" '; 
                      ?> >Delete</button>
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
                    if ($oRecord->IsExistingRecord || $bReadOnly)
                    {
                      $lblProducerName = new HtmlTextLabel('Producer', 'txtProducerName', $oRecord->ProducerName);
                      $lblProducerName->EchoHtml();
                      unset($lblProducerName);
                    }
                    else //new record - allow select
                    {                      
                      $selProducer = new HtmlSelectArray('ProducerID', 'Producer', $arrProducers, $oRecord->ProducerID);
                      $selProducer->Required = TRUE;
                      $selProducer->EchoHtml();
                      unset($selProducer);
                    }
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php 
                    $txtDeliveryPercent = new HtmlTextEditNumeric('% Delivery Fee', 'txtDeliveryPercent', $oRecord->DeliveryPercent);
                    $txtDeliveryPercent->ReadOnly = $bReadOnly;
                    $txtDeliveryPercent->EchoHtml();
                    unset($txtDeliveryPercent);

                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                      
                    $txtMinDelivery = new HtmlTextEditNumeric('Min. Delivery Fee', 'txtMinDelivery', 
                            $oRecord->MinDelivery);
                    $txtMinDelivery->ReadOnly = $bReadOnly;
                    $txtMinDelivery->EchoHtml();
                    unset($txtMinDelivery);
                     
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                      
                     $txtMaxDelivery = new HtmlTextEditNumeric('Max. Delivery Fee', 'txtMaxDelivery', 
                            $oRecord->MaxDelivery);
                     $txtMaxDelivery->ReadOnly = $bReadOnly;
                     $txtMaxDelivery->EchoHtml();
                     unset($txtMaxDelivery);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                      
                     $txtFixedDelivery = new HtmlTextEditNumeric('Fixed Delivery Fee', 'txtFixedDelivery', 
                            $oRecord->FixedDelivery);
                     $txtFixedDelivery->ReadOnly = $bReadOnly;
                     $txtFixedDelivery->EchoHtml();
                     unset($txtFixedDelivery);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                      
                     $txtMaxProducerOrder = new HtmlTextEditNumeric('Max. Producer Order', 'txtMaxProducerOrder', 
                            $oRecord->MaxProducerOrder);
                     $txtMaxProducerOrder->ReadOnly = $bReadOnly;
                     $txtMaxProducerOrder->EchoHtml();
                     unset($txtMaxProducerOrder);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php   
                    $sProducerTotal = $oRecord->ProducerTotal;
                    if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Total->CanCompute)
                      $sProducerTotal .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Total->PercentRounded . '%)');
                  
                    $txtProducerTotal = new HtmlTextLabel('Producer Total', 'txtProducerTotal', $sProducerTotal);
                    $txtProducerTotal->EchoHtml();
                    unset($txtProducerTotal);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                
                <tr>
                  <?php 
                    $txtMaxBurden = new HtmlTextEditNumeric('Delivery Capacity', 'txtMaxBurden', $oRecord->MaxBurden);
                    $txtMaxBurden->ReadOnly = $bReadOnly;
                    $txtMaxBurden->EchoHtml();
                    unset($txtMaxBurden);

                    HtmlTextEditMultiLang::EchoHelpText('Limits the size of this cooperative order&#x27;s producer to the overall capacity, comapring it to the sum of the &quot;burden&quot; field of each product multiplied by the quantity ordered. Members will not be able to place an order that exceeds the limitation set here.');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php           
                    $sTotalBurden = $oRecord->TotalBurden;
                    if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Burden->CanCompute)                    
                      $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Burden->PercentRounded . '%)');

                    $lblTotalBurden = new HtmlTextLabel('Total Burden', 'txtTotalBurden', $sTotalBurden);
                    $lblTotalBurden->EchoHtml();
                    unset($lblTotalBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('The sum total of each ordered product &quot;Burden&quot; multiplied by the times it was ordered, per producer');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php                                       
                    $txtTotalDelivery = new HtmlTextLabel('Total Delivery', 'txtTotalDelivery', 
                            $oRecord->TotalDelivery);
                    $txtTotalDelivery->EchoHtml();
                    unset($txtTotalDelivery);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                
                <tr>
                  <?php                                       
                    $txtCoopTotal = new HtmlTextLabel('Total Coop', 'txtCoopTotal', 
                            $oRecord->CoopTotal);
                    $txtCoopTotal->EchoHtml();
                    unset($txtCoopTotal);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                  <td></td>
                </tr>
                
                
                </table>
                </td></tr></table>
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

