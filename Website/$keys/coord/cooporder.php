<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = '<!$NEW_COOP_ORDER$!>';

$oRecord = new CoopOrder;
$bStatusOnly = TRUE;
$bReadOnly = FALSE;
$bShowSums = FALSE;
$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_ENTRY;

$oCoopOrderCapacity = NULL;

$sHelpTimeFormat = sprintf('<!$HELP_TIME_FORMAT$!>', $g_dNow->format('<!$DATE_PICKER_TIME_FORMAT$!>'));

try
{
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->ID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
    {
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
      $oRecord->PreserveData();
    }
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          
          $sCtl = HtmlSelectArray::PREFIX . 'Status';
          if ( isset( $_POST[$sCtl] ))
            $oRecord->Status = intval($_POST[$sCtl]);

          switch($oRecord->OriginalStatus)
          {
            case CoopOrder::STATUS_ACTIVE:
            case CoopOrder::STATUS_DRAFT:
            case CoopOrder::STATUS_LOCKED:
              //collect data if status is active or draft
              $oRecord->Names = ComplexPostData::GetNames('txtName');
              $oRecord->Start = ComplexPostData::GetDateTime('Start',array(0,0,0));
              $oRecord->End = ComplexPostData::GetDateTime('End',array(23,59,0));
              $oRecord->Delivery = ComplexPostData::GetDate('Delivery');
              $oRecord->MaxBurden = NULL;
              if ( isset($_POST['txtMaxBurden']) && !empty($_POST['txtMaxBurden']))
                 $oRecord->MaxBurden = 0 + trim($_POST['txtMaxBurden']);
              $oRecord->MaxCoopTotal = NULL;
              if ( isset($_POST['txtMaxCoopTotal']) && !empty($_POST['txtMaxCoopTotal']))
                 $oRecord->MaxCoopTotal = 0 + trim($_POST['txtMaxCoopTotal']);
              $oRecord->CoopFee = NULL;
              if ( isset($_POST['txtCoopFee']) && !empty($_POST['txtCoopFee']))
                 $oRecord->CoopFee = 0 + trim($_POST['txtCoopFee']);
              $oRecord->SmallOrder = NULL;
              if ( isset($_POST['txtSmallOrder']) && !empty($_POST['txtSmallOrder']))
                 $oRecord->SmallOrder = 0 + trim($_POST['txtSmallOrder']);
              $oRecord->SmallOrderCoopFee = NULL;
              if ( isset($_POST['txtSmallOrderCoopFee']) && !empty($_POST['txtSmallOrderCoopFee']))
                 $oRecord->SmallOrderCoopFee = 0 + trim($_POST['txtSmallOrderCoopFee']);
              $oRecord->CoopFeePercent = NULL;
              if ( isset($_POST['txtCoopFeePercent']) && !empty($_POST['txtCoopFeePercent']))
                 $oRecord->CoopFeePercent = 0 + trim($_POST['txtCoopFeePercent']);       
              break;
          }
          

          $bSuccess = false;
          if ($oRecord->ID > 0)
            $bSuccess = $oRecord->Edit(); //must not use private connection, because in transaction with other classes
          else
            $bSuccess = $oRecord->Add();

          if ( $bSuccess )
          {
              $g_oError->AddError('<!$RECORD_SAVED$!>', 'ok');
              if (!$oRecord->LoadRecord($oRecord->ID))
                throw new Exception('<!$LOAD_RECORD_FAILED$!>');      
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('<!$RECORD_NOT_SAVED$!>');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('cooporders.php');
              exit;
          }
          else
              $g_oError->AddError('<!$DELETE_FAILURE$!>');
          
          break;
      }
    }
  }
  else if (isset($_GET['id']))
  {
    if(!$oRecord->LoadRecord(intval($_GET['id'])))
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


  $bReadOnly = !$oRecord->HasPermission(SQLBase::PERMISSION_EDIT);

  $oCoopOrderCapacity = new CoopOrderCapacity($oRecord->MaxBurden, $oRecord->TotalBurden, $oRecord->MaxCoopTotal, $oRecord->CoopTotal,
      $oRecord->MaxStorageBurden, $oRecord->StorageBurden);

  if ($oRecord->ID > 0)
  {
    $sPageTitle = $oRecord->Name;
    $oTabInfo->ID = $oRecord->ID;
    $oTabInfo->CoopOrderTitle = $oRecord->Name;
    $oTabInfo->Status = $oRecord->Status;
    $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
    $oTabInfo->CoopTotal = $oRecord->CoopTotal;
    $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oRecord->End, $oRecord->Delivery, $oRecord->Status);

    if ($oCoopOrderCapacity->SelectedType != CoopOrderCapacity::TypeNone)
      $oTabInfo->Capacity = $oCoopOrderCapacity->PercentRounded . '%';
  }

  switch($oRecord->Status)
  {
    case CoopOrder::STATUS_ACTIVE:
    case CoopOrder::STATUS_DRAFT:
    case CoopOrder::STATUS_LOCKED:
      $bStatusOnly = FALSE; //allow update of other fields when order is active or draft
      break;
   }

   $bShowSums = $oRecord->CheckSumsPermission();
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
<title><!$COOPERATIVE_NAME$!>: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/ajax.js" ></script>
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
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->ID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>    
    <tr>
        <td >
              <table cellspacing="0" cellpadding="0" width="100%">
              <tr>
                <td><?php include_once '../control/coopordertab.php'; ?></td>
              </tr>
              <tr>
                <td><?php include_once '../control/error/ctlError.php'; ?></td>
              </tr>
              <tr><td>
             <?php
                if (!$bReadOnly)
                {
                  echo '<button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" ';
                  if ($g_oError->HadError) 
                    echo ' disabled="disabled" ';
                  echo '><!$BTN_SAVE$!></button>&nbsp;';

                  if ($oRecord->HasDeletePermission()) 
                  {
                      echo '<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" ';
                      if ($g_oError->HadError || $oRecord->ID == 0 || $oRecord->Status == CoopOrder::STATUS_ACTIVE ) 
                        echo ' disabled="disabled" '; 
                      echo '><!$BTN_DELETE$!></button>';
                  } 
                 }
                ?>
                </td>
              </tr>
              <tr>
              <td>
              <table cellspacing="0" cellpadding="2" width="100%">
              <tr>
              <td></td>
              <?php
                HtmlTextEditMultiLang::EchoColumnHeaders();
              ?>
              <td width="100%">&nbsp;</td>
              </tr>
              <tr>
              <?php

              $txtName = new HtmlTextEditMultiLang('<!$FIELD_COOP_ORDER_NAME$!>', 'txtName', HtmlTextEdit::TEXTBOX, $oRecord->Names);
              $txtName->Required = TRUE;
              $txtName->ReadOnly = $bStatusOnly || $bReadOnly;
              $txtName->EchoHtml();
              unset($txtName);

              ?>
              <td></td>
              </tr>
              <tr>
                <?php 
                  $aArr = CoopOrder::GetStatusesToChangeTo($oRecord->Status);
                  $selStatus = new HtmlSelectArray('Status', '<!$FIELD_COOP_ORDER_STATUS$!>', $aArr, $oRecord->Status);
                  $selStatus->EncodeHtml = FALSE; //already encoded in python script
                  $selStatus->EmptyText = NULL; //means no empty entry
                  $selStatus->Required = TRUE;
                  $selStatus->ReadOnly = ($oRecord->ID == 0) || $bReadOnly;
                  $selStatus->EchoHtml();
                  unset($selStatus);
                  HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
              <td></td>
              </tr>
              <tr>
              <?php
               $dpStart = new HtmlDatePicker('<!$FIELD_COOP_ORDER_START$!>', 'Start', $oRecord->Start);
               $dpStart->Required = TRUE;
               $dpStart->TimeSetting = HtmlDatePicker::TIME_DISPLAYED;
               $dpStart->ReadOnly = $bStatusOnly || $bReadOnly;
               $dpStart->EchoHtml();
               unset($dpStart);

               HtmlTextEditMultiLang::EchoHelpText( $sHelpTimeFormat, 'costart' );
               HtmlTextEditMultiLang::OtherLangsEmptyCells();
              ?>
              </tr>
              <tr>
              <?php                 
               $dpEnd = new HtmlDatePicker('<!$FIELD_COOP_ORDER_END$!>', 'End', $oRecord->End);
               $dpEnd->Required = TRUE;
               $dpEnd->TimeSetting = HtmlDatePicker::TIME_DISPLAYED;
               $dpEnd->ReadOnly = $bStatusOnly || $bReadOnly;
               $dpEnd->EchoHtml();
               unset($dpEnd);

               HtmlTextEditMultiLang::EchoHelpText( $sHelpTimeFormat, 'coend' );
               HtmlTextEditMultiLang::OtherLangsEmptyCells();
              ?>
              </tr>
              <tr>
              <?php                 
               $dpDelivery = new HtmlDatePicker('<!$FIELD_COOP_ORDER_DELIVERY$!>', 'Delivery', $oRecord->Delivery);
               $dpDelivery->Required = TRUE;
               $dpDelivery->TimeSetting = HtmlDatePicker::TIME_NOT_DISPLAYED;
               $dpDelivery->ReadOnly = $bStatusOnly || $bReadOnly;
               $dpDelivery->EchoHtml();
               unset($dpDelivery);

               HtmlTextEditMultiLang::OtherLangsEmptyCells();
              ?>
              <td></td>
              </tr> 
              <?php
              if ($bShowSums)
              {
              ?>
              <tr>
                <?php 
                  $txtMaxBurden = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_MAX_BURDEN$!>', 'txtMaxBurden', $oRecord->MaxBurden);
                  $txtMaxBurden->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtMaxBurden->EchoHtml();
                  unset($txtMaxBurden);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_MAX_BURDEN$!>', 'comaxburden');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>
              <tr>
                <?php                     
                  $txtMaxCoopTotal = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_MAX_COOP_TOTAL$!>', 'txtMaxCoopTotal', $oRecord->MaxCoopTotal);
                  $txtMaxCoopTotal->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtMaxCoopTotal->EchoHtml();
                  unset($txtMaxCoopTotal);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_MAX_COOP_TOTAL$!>', 'comaxcooptotal');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();                
                ?>
              </tr>
              <tr>
                <?php                     
                  $txtCoopFee = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_COOP_FEE$!>', 'txtCoopFee', $oRecord->CoopFee);
                  $txtCoopFee->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtCoopFee->EchoHtml();
                  unset($txtCoopFee);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_COOP_FEE$!>', 'cofee');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>
              <tr>
                <?php                     
                  $txtSmallOrder = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_SMALL_ORDER$!>', 'txtSmallOrder', $oRecord->SmallOrder);
                  $txtSmallOrder->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtSmallOrder->EchoHtml();
                  unset($txtSmallOrder);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_SMALL_ORDER$!>', 'cosmallorder');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>
              <tr>
                <?php                     
                  $txtSmallOrderCoopFee = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_SMALL_ORDER_COOP_FEE$!>', 'txtSmallOrderCoopFee', 
                          $oRecord->SmallOrderCoopFee);
                  $txtSmallOrderCoopFee->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtSmallOrderCoopFee->EchoHtml();
                  unset($txtSmallOrderCoopFee);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_SMALL_ORDER_COOP_FEE$!>', 'cosmallcoopfee');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>
              <tr>
                <?php                     
                  $txtCoopFeePercent = new HtmlTextEditNumeric('<!$FIELD_COOP_ORDER_COOP_FEE_PERCENT$!>', 'txtCoopFeePercent', 
                          $oRecord->CoopFeePercent);
                  $txtCoopFeePercent->ReadOnly = $bStatusOnly || $bReadOnly;
                  $txtCoopFeePercent->EchoHtml();
                  unset($txtCoopFeePercent);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_COOP_FEE_PERCENT$!>', 'cocoopfeepercent');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>                
               <tr>
                <?php 
                  $sCoopTotal = $oRecord->CoopTotal;
                  if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Total->CanCompute)
                    $sCoopTotal .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Total->PercentRounded . '%)');

                  $lblCoopTotal = new HtmlTextLabel('<!$FIELD_COOP_ORDER_COOP_TOTAL$!>', 'txtCoopTotal', $sCoopTotal);
                  $lblCoopTotal->EchoHtml();
                  unset($lblCoopTotal);

                  HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                 <td></td>
              </tr>
              <tr>
                <?php                                       
                  $lblProducerTotal = new HtmlTextLabel('<!$FIELD_COOP_ORDER_PRODUCER_TOTAL$!>', 'txtProducerTotal', $oRecord->ProducerTotal);
                  $lblProducerTotal->EchoHtml();
                  unset($lblProducerTotal);

                  HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                <td></td>
              </tr>

              <tr>
                <?php                                       
                  $lblTotalDelivery = new HtmlTextLabel('<!$FIELD_COOP_ORDER_TOTAL_DELIVERY$!>', 'txtTotalDelivery', $oRecord->TotalDelivery);
                  $lblTotalDelivery->EchoHtml();
                  unset($lblTotalDelivery);

                  HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                 <td></td>
              </tr>

              <tr>
                <?php    
                  $sTotalBurden = $oRecord->TotalBurden;
                  if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->Burden->CanCompute)
                    $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->Burden->PercentRounded . '%)');

                  $lblTotalBurden = new HtmlTextLabel('<!$FIELD_COOP_ORDER_TOTAL_BURDEN$!>', 'txtTotalBurden', $sTotalBurden);

                  $lblTotalBurden->EchoHtml();
                  unset($lblTotalBurden);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_TOTAL_BURDEN$!>', 'cototalburden');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
              </tr>

              <tr>
                <?php
                  $lblMaxStorageBurden = new HtmlTextLabel('<!$FIELD_TOTAL_MAX_STORAGE_BURDEN$!>', 'lblMaxStorageBurden', 
                      $oRecord->MaxStorageBurden);
                  $lblMaxStorageBurden->EchoHtml();
                  unset($lblMaxStorageBurden);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_TOTAL_MAX_STORAGE_BURDEN$!>', 'coMaxStorageBurden');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>

              <tr>
                <?php
                  $sTotalBurden = $oRecord->StorageBurden;
                  if ($oCoopOrderCapacity != NULL && $oCoopOrderCapacity->StorageBurden->CanCompute)
                    $sTotalBurden .= LanguageSupport::AppendInFixedOrder(' ', '(' . $oCoopOrderCapacity->StorageBurden->PercentRounded . '%)');

                  $lblStorageBurden = new HtmlTextLabel('<!$FIELD_TOTAL_STORAGE_BURDEN$!>', 'lblStorageBurden', 
                      $sTotalBurden);
                  $lblStorageBurden->EchoHtml();
                  unset($lblStorageBurden);

                  HtmlTextEditMultiLang::EchoHelpText('<!$TOOLTIP_COOP_ORDER_TOTAL_STORAGE_BURDEN$!>', 'coStorageBurden');
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
              </tr>

                <?php    
                } //end of ShowSums
                  if (!$bReadOnly)
                  {
                    echo '<tr>';
                    $lblModifiedByMemberName = new HtmlTextLabel('<!$FIELD_COOP_ORDER_MODIFIER$!>', 'txtModifiedByMemberName', $oRecord->ModifiedByMemberName);
                    $lblModifiedByMemberName->EchoHtml();
                    unset($lblModifiedByMemberName);

                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 

                    echo ' <td></td></tr>';
                  }
              ?>
              </table>
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
