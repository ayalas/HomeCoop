<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = '<!$PAGE_TITLE_COOP_ORDER_COPY$!>';

$oRecord = new CoopOrder;

try
{
  if (!$oRecord->CheckAccess() || !$oRecord->CanCopy())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->SourceCoopOrderID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    //collect data if status is active or draft
    $oRecord->Names = ComplexPostData::GetNames('txtName');
    $oRecord->Start = ComplexPostData::GetDate('Start');
    $oRecord->End = ComplexPostData::GetDate('End');
    $oRecord->Delivery = ComplexPostData::GetDate('Delivery');
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          $bSuccess = $oRecord->Copy();

          if ( $bSuccess )
              RedirectPage::To( $g_sRootRelativePath . 'coord/cooporder.php?id=' . $oRecord->ID );   
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('<!$RECORD_NOT_SAVED$!>');
        break;
      }
    }
  }
  else if (isset($_GET['id']))
  {
    $oRecord->SourceCoopOrderID = intval($_GET['id']);
    //check permissions for source order
    if (!$oRecord->LoadSourceOrderInitalData())
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
<script type="text/javascript" src="../script/ajax.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >

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
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->SourceCoopOrderID; ?>" />
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
                  <td><?php include_once '../control/error/ctlError.php'; ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError) echo ' disabled="disabled" '; ?>><!$BTN_SAVE$!></button>&nbsp;
                  <?php
                    echo '<a href="cooporder.php?id=', $oRecord->SourceCoopOrderID,'" >', sprintf('<!$LINK_BACK_TO$!>', $oRecord->Name),
                      '</a>';
                  ?>
                  </td>
                </tr>
                <tr><td>
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
                $txtName->EchoHtml();
                unset($txtName);
                
                ?>
                <td></td>
                </tr>
                <tr>
                <?php
                 $dpStart = new HtmlDatePicker('<!$FIELD_COOP_ORDER_START$!>', 'Start', $oRecord->Start);
                 $dpStart->Required = TRUE;
                 $dpStart->EchoHtml();
                 unset($dpStart);
                 
                 HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                <td></td>
                </tr>
                <tr>
                <?php                 
                 $dpEnd = new HtmlDatePicker('<!$FIELD_COOP_ORDER_END$!>', 'End', $oRecord->End);
                 $dpEnd->Required = TRUE;
                 $dpEnd->EchoHtml();
                 unset($dpEnd);
                 
                 HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                <td></td>
                </tr>
                <tr>
                <?php                 
                 $dpDelivery = new HtmlDatePicker('<!$FIELD_COOP_ORDER_DELIVERY$!>', 'Delivery', $oRecord->Delivery);
                 $dpDelivery->Required = TRUE;
                 $dpDelivery->EchoHtml();
                 unset($dpDelivery);
                 
                 HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                ?>
                <td></td>
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