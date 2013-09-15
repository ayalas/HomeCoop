<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = '<!$NEW_MEMBER$!>';
$oRecord = new Member;
$arrPaymentMethods = NULL;
$arrPickupLocations = NULL;
$bHasAccessToRoles = $oRecord->HasAccessToRoles();
try
{
  //must be coordinator to enter this page
  if (!$oRecord->CheckAccess() || !$oRecord->IsCoordinator)
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->ID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    //collect data for postback
    if ( isset( $_POST['txtName'] ) && !empty($_POST['txtName']))
      $oRecord->Name = trim($_POST['txtName']);

    if ($oRecord->ID == 0)
    {
      if ( isset( $_POST['txtLoginName'] ) && !empty($_POST['txtLoginName']))
        $oRecord->LoginName = trim($_POST['txtLoginName']);
    }
    
    $sCtl = HtmlSelectPDO::PREFIX . 'PaymentMethodKeyID';
    if ( isset( $_POST[$sCtl] ))
      $oRecord->PaymentMethodID = intval($_POST[$sCtl]);
    
    $sCtl = HtmlSelectPDO::PREFIX . 'PickupLocationKeyID';
    if ( isset( $_POST[$sCtl] ))
      $oRecord->PickupLocationID = intval($_POST[$sCtl]);

    $oRecord->Balance = NULL;
    if ( isset( $_POST['txtBalance'] ) && !empty($_POST['txtBalance']))
      $oRecord->Balance = 0 + trim($_POST['txtBalance']);
    
    $oRecord->BalanceHeld = NULL;
    if ( isset( $_POST['txtBalanceHeld'] ) && !empty($_POST['txtBalanceHeld']))
      $oRecord->BalanceHeld = 0 + trim($_POST['txtBalanceHeld']);
    
    $oRecord->BalanceInvested = NULL;
    if ( isset( $_POST['txtBalanceInvested'] ) && !empty($_POST['txtBalanceInvested']))
      $oRecord->BalanceInvested = 0 + trim($_POST['txtBalanceInvested']);

    $oRecord->PercentOverBalance = NULL;
    if ( isset( $_POST['txtPercentOverBalance'] ) && !empty($_POST['txtPercentOverBalance']))
      $oRecord->PercentOverBalance = 0 + trim($_POST['txtPercentOverBalance']);

    if ( isset( $_POST['txtEMail'] ) && !empty($_POST['txtEMail']))
      $oRecord->EMail = trim($_POST['txtEMail']);

    if ( isset( $_POST['txtEMail2'] ) && !empty($_POST['txtEMail2']))
      $oRecord->EMail2 = trim($_POST['txtEMail2']);

    if ( isset( $_POST['txtEMail3'] ) && !empty($_POST['txtEMail3']))
      $oRecord->EMail3 = trim($_POST['txtEMail3']);

    if ( isset( $_POST['txtEMail4'] ) && !empty($_POST['txtEMail4']))
      $oRecord->EMail4 = trim($_POST['txtEMail4']);
    
    if ( isset( $_POST['ctlIsDisabled'] ))
      $oRecord->IsDisabled = (intval($_POST['ctlIsDisabled']) == 1);
    
    $oRecord->Comments = NULL;
    if ( isset( $_POST['txtComments'] ) && !empty($_POST['txtComments']))
      $oRecord->Comments = trim($_POST['txtComments']);
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          //collect data for save only
          if ( isset( $_POST['txtNewPassword'] ) && !empty($_POST['txtNewPassword']))
              $oRecord->NewPassword = trim($_POST['txtNewPassword']);
          
          if ( isset( $_POST['txtVerifyPassword'] ) && !empty($_POST['txtVerifyPassword']))
              $oRecord->VerifyPassword = trim($_POST['txtVerifyPassword']);

          //perform save
          $bSuccess = false;
          if ($oRecord->ID > 0)
            $bSuccess = $oRecord->Edit();
          else
            $bSuccess = $oRecord->Add();

          if ( $bSuccess )
            $g_oError->AddError('<!$RECORD_SAVED$!>','ok');
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('<!$RECORD_NOT_SAVED$!>');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('members.php');
              exit;
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('<!$DELETE_FAILURE$!>');
          break;
        case MEMBER::POST_ACTION_DEACTIVATE:
          if ($oRecord->Deactivate())
            $g_oError->AddError('<!$MEMBER_DEACTIVATE_SUCCESS$!>', 'ok');
          else
            $g_oError->AddError('<!$MEMBER_DEACTIVATE_FAILURE$!>');
          break;
        case MEMBER::POST_ACTION_ACTIVATE:
          if ($oRecord->Activate())
            $g_oError->AddError('<!$MEMBER_ACTIVATE_SUCCESS$!>', 'ok');
          else
            $g_oError->AddError('<!$MEMBER_ACTIVATE_FAILURE$!>');
          break;
      }
    }
  }
  else if (isset($_GET['id']))
  {
    //editing existing
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
  
  if ($oRecord->Name != NULL)
    $sPageTitle = $oRecord->Name;
  
  $arrPaymentMethods = $oRecord->GetPaymentMethods();
  
  $arrPickupLocations = $oRecord->GetCachiers();
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
  if (confirm(decodeXml('<!$ARE_YOU_SURE_DELETE_USER_MSG$!>')))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function Deactivate()
{
    document.getElementById("hidPostAction").value = <?php echo MEMBER::POST_ACTION_DEACTIVATE; ?>;
    document.frmMain.submit();  
}
function Activate()
{
    document.getElementById("hidPostAction").value = <?php echo MEMBER::POST_ACTION_ACTIVATE; ?>;
    document.frmMain.submit();  
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
function VerifyPassword()
{
  if (document.getElementById("txtNewPassword").value == document.getElementById("txtVerifyPassword").value)
  {
    if (document.getElementById("txtNewPassword").value == '')
      document.getElementById("spVerifyResult").innerHTML = '';
    else
    {
      document.getElementById("spVerifyResult").style.color = 'green';
      document.getElementById("spVerifyResult").innerHTML = '<!$PASSWORDS_MATCH$!>';
    }
  }
  else
  {
    document.getElementById("spVerifyResult").style.color = 'red';
    document.getElementById("spVerifyResult").innerHTML = '<!$PASSWORDS_DO_NOT_MATCH$!>';
  }
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
        <td width="<!$TOTAL_PAGE_WIDTH$!>"><span class="coopname"><!$COOPERATIVE_NAME$!>:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="<!$COORD_PAGE_WIDTH$!>" >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td><?php 
                  include_once '../control/error/ctlError.php';
                ?></td>
                </tr>
                <tr>
                  <td>
                    <a href="member.php" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a>&nbsp;
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError || !$oRecord->CanModify) echo ' disabled="disabled" '; ?>><!$BTN_SAVE$!></button>&nbsp;<?php 
                  if ($oRecord->ID > 0 && $oRecord->CanModify && $oRecord->HasPermission(SQLBase::PERMISSION_DELETE)) 
                  {
                      echo '<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" ';
                      if ($g_oError->HadError) 
                        echo ' disabled="disabled" '; 
                      echo '><!$BTN_DELETE$!></button>&nbsp;';
                  } 
                  if ($oRecord->IsRegularMember && $oRecord->CanModify && !$g_oError->HadError)
                  {
                    echo '<button type="button" onclick="JavaScript:Deactivate();" id="btnDeactivate" name="btnDeactivate">',
                          '<!$BTN_REMOVE_PERMISSIONS$!></button>&nbsp;';
                            
                  }
                  else if ($oRecord->HasNoPermissions && $oRecord->CanModify && !$g_oError->HadError)
                  {
                    echo '<button type="button" onclick="JavaScript:Activate();" id="btnActivate" name="btnActivate">',
                          '<!$BTN_RESTORE_PERMISSIONS$!></button>&nbsp;';
                            
                  }
                  if (!$g_oError->HadError && $oRecord->ID > 0 && $bHasAccessToRoles)
                  {
                    echo '<a href="memberroles.php?id=', $oRecord->ID, '"><!$LINK_MEMBER_ROLES$!></a>';
                  } 
                ?></td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <?php
                                                
                $txtName = new HtmlTextEditOneLang('<!$FIELD_MEMBER_NAME$!>', 'txtName', $oRecord->Name);
                $txtName->Required = TRUE;
                $txtName->ReadOnly = !$oRecord->CanModify;
                $txtName->EchoHtml();
                unset($txtName);
                
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                <tr>
                <?php                 
                $txtLoginName = new HtmlTextEditOneLang('<!$FIELD_LOGIN_NAME$!>', 'txtLoginName', $oRecord->LoginName);
                $txtLoginName->Required = TRUE;
                //read only for existing record 
                //in order to allow coordinators to identify the member
                //throughout hir name changes
                $txtLoginName->ReadOnly = ($oRecord->ID > 0); 
                $txtLoginName->EchoHtml();
                unset($txtLoginName);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <tr>
                <?php                 
                $txtNewPassword = new HtmlTextEditOneLang('<!$FIELD_NEW_PASSWORD$!>', 'txtNewPassword', '');
                $txtNewPassword->Required = ($oRecord->ID == 0);
                $txtNewPassword->ReadOnly = !$oRecord->CanModify;
                $txtNewPassword->SetAttribute("onkeyup","JavaScript:VerifyPassword();");
                $txtNewPassword->ControlType = HtmlTextEdit::PASSWORD;
                $txtNewPassword->EchoHtml();
                unset($txtNewPassword);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <tr>
                <?php                 
                $txtVerifyPassword = new HtmlTextEditOneLang('<!$FIELD_VERIFY_PASSWORD$!>', 'txtVerifyPassword', '');
                $txtVerifyPassword->Required = ($oRecord->ID == 0);
                $txtVerifyPassword->SetAttribute("onkeyup","JavaScript:VerifyPassword();");
                $txtVerifyPassword->ReadOnly = !$oRecord->CanModify;
                $txtVerifyPassword->ControlType = HtmlTextEdit::PASSWORD;
                $txtVerifyPassword->EchoHtml();
                unset($txtVerifyPassword);
                
                ?>
                <td><span id="spVerifyResult" name="spVerifyResult"></span></td>
                </tr>
                
                <tr>
                  <?php                     
                    $txtBalance = new HtmlTextEditNumeric('<!$FIELD_BALANCE$!>', 'txtBalance', $oRecord->Balance);
                    $txtBalance->ReadOnly = !$oRecord->CanModify;
                    $txtBalance->EchoHtml();
                    unset($txtBalance);
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                <tr>
                  <?php                     
                    $txtBalanceHeld = new HtmlTextEditNumeric('<!$FIELD_BALANCE_HELD$!>', 'txtBalanceHeld', $oRecord->BalanceHeld);
                    $txtBalanceHeld->ReadOnly = !$oRecord->CanModify;
                    $txtBalanceHeld->EchoHtml();
                    unset($txtBalanceHeld);
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                <tr>
                  <?php                     
                    $txtBalanceInvested = new HtmlTextEditNumeric('<!$FIELD_BALANCE_INVESTED$!>', 'txtBalanceInvested', $oRecord->BalanceInvested);
                    $txtBalanceInvested->ReadOnly = !$oRecord->CanModify;
                    $txtBalanceInvested->EchoHtml();
                    unset($txtBalanceInvested);
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                  <?php          
                  if ($oRecord->CanModify)
                    {
                      echo '<tr>';
                      $selPickupLoc = new HtmlSelectArray('PickupLocationKeyID', '<!$FIELD_CACHIER$!>', $arrPickupLocations,
                            0);
                      $selPickupLoc->ReadOnly = !$oRecord->CanModify;
                      $selPickupLoc->EchoHtml();
                      unset($selPickupLoc);
                    
                      echo '<td><a class="tooltiphelp" href="#" ><!$HELP_SIGN$!><span><!$HELP_MEMBER_BALANCE_UPDATES_CASHIER$!></span></a></td>';
                      echo '</tr>';
                    }
                  ?>                
                
                <tr>
                  <?php                    
                    $selPaymentMethod = new HtmlSelectArray('PaymentMethodKeyID', '<!$FIELD_PAYMENT_METHOD$!>', $arrPaymentMethods,
                          $oRecord->PaymentMethodID);
                    $selPaymentMethod->Required = TRUE;
                    $selPaymentMethod->ReadOnly = !$oRecord->CanModify;
                    $selPaymentMethod->EchoHtml();
                    unset($selPaymentMethod);
                    
                  ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                  <?php                     
                    $txtPOBalance = new HtmlTextEditNumeric('<!$FIELD_PERCENT_OVER_BALANCE$!>', 'txtPercentOverBalance', $oRecord->PercentOverBalance);
                    $txtPOBalance->ReadOnly = !$oRecord->CanModify;
                    $txtPOBalance->EchoHtml();
                    unset($txtPOBalance);
                  ?>
                  <td><a class="tooltiphelp" href="#" ><!$HELP_SIGN$!><span><!$HELP_PERCENT_OVER_BALANCE$!></span></a></td>
                </tr>

                <tr>
                  <?php
                    $lblMaxOrder = new HtmlTextLabel('<!$FIELD_MAX_ORDER$!>', 'lblMaxOrder',$oRecord->MaxOrder);
                    $lblMaxOrder->EchoHtml();
                    unset($lblMaxOrder);
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                <tr>
                <?php
                                                
                $txtEMail = new HtmlTextEditOneLang('<!$FIELD_EMAIL$!>', 'txtEMail', $oRecord->EMail);
                $txtEMail->ReadOnly = !$oRecord->CanModify;
                $txtEMail->Required = TRUE;
                $txtEMail->EchoHtml();
                unset($txtEMail);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <tr>
                <?php
                                                
                $txtEMail2 = new HtmlTextEditOneLang('<!$FIELD_EMAIL2$!>', 'txtEMail2', $oRecord->EMail2);
                $txtEMail2->ReadOnly = !$oRecord->CanModify;
                $txtEMail2->EchoHtml();
                unset($txtEMail2);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                <?php
                                                
                $txtEMail3 = new HtmlTextEditOneLang('<!$FIELD_EMAIL3$!>', 'txtEMail3', $oRecord->EMail3);
                $txtEMail3->ReadOnly = !$oRecord->CanModify;
                $txtEMail3->EchoHtml();
                unset($txtEMail3);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                <?php
                                                
                $txtEMail4 = new HtmlTextEditOneLang('<!$FIELD_EMAIL4$!>', 'txtEMail4', $oRecord->EMail4);
                $txtEMail4->ReadOnly = !$oRecord->CanModify;
                $txtEMail4->EchoHtml();
                unset($txtEMail4);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                  <?php
                   $oIsDisabled = new HtmlSelectBoolean('ctlIsDisabled', '<!$FIELD_IS_DISABLED$!>', $oRecord->IsDisabled, '<!$FIELD_VALUE_DISABLED$!>', 
                          '<!$FIELD_VALUE_ENABLED$!>');
                    $oIsDisabled->ReadOnly =  (!$oRecord->CanModify || ($oRecord->ID == 0));
                    $oIsDisabled->EchoHtml();
                   unset($oIsDisabled);
                  
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                
                <?php
                if ($oRecord->ID > 0)
                {
                  echo '<tr>';
                  
                  $txtJoinedOn = new HtmlTextLabel('<!$FIELD_JOINED_ON$!>', 'JoinedOn', 
                          $oRecord->JoinedOn->format('<!$DATE_PICKER_DATE_FORMAT$!>'));
                  $txtJoinedOn->EchoHtml();
                        
                  echo '<td>&nbsp;</td></tr>';
                }
                ?>
                
                <tr>
                <?php
                                                
                $txtComments = new HtmlTextEditOneLang('<!$FIELD_MEMBER_COMMENTS$!>', 'txtComments', $oRecord->Comments);
                $txtComments->ReadOnly = !$oRecord->CanModify;
                $txtComments->ControlType = HtmlTextEdit::TEXTAREA;
                $txtComments->Rows = 5;
                $txtComments->MaxLength = 300;
                $txtComments->EchoHtml();
                unset($txtComments);
                
                ?>
                <td>&nbsp;</td>
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
