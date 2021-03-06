<?php

include_once 'settings.php';
include_once 'authenticate.php';

$oRecord = new Member;

try
{
  $oRecord->ID = $g_oMemberSession->MemberID;
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    //preserve fields
    $oRecord->PreserveFieldsForProfileScreen();
    
    //collect data for postback
    if ( isset( $_POST['txtName'] ) && !empty($_POST['txtName']))
      $oRecord->Name = trim($_POST['txtName']);

    if ( isset( $_POST['txtEMail'] ) && !empty($_POST['txtEMail']))
      $oRecord->EMail = trim($_POST['txtEMail']);

    if ( isset( $_POST['txtEMail2'] ) && !empty($_POST['txtEMail2']))
      $oRecord->EMail2 = trim($_POST['txtEMail2']);

    if ( isset( $_POST['txtEMail3'] ) && !empty($_POST['txtEMail3']))
      $oRecord->EMail3 = trim($_POST['txtEMail3']);

    if ( isset( $_POST['txtEMail4'] ) && !empty($_POST['txtEMail4']))
      $oRecord->EMail4 = trim($_POST['txtEMail4']);
    
    $sCtl = HtmlSelectArray::PREFIX . 'FileFormat';
    if ( isset( $_POST[$sCtl] ))
      $oRecord->ExportFormat = intval($_POST[$sCtl]);
    
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
          $bSuccess = $oRecord->Edit();
          
          if ( $bSuccess )
            $g_oError->AddError('הרשומה נשמרה בהצלחה.', 'ok');
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('הרשומה לא נשמרה. אין לך הרשאות מספיקות או שאירעה שגיאה.');
          break;
      }
    }
  }
  else if(!$oRecord->LoadRecord( $g_oMemberSession->MemberID ))
  {
    RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
    exit;
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
<html dir='rtl' >
<head>
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
<?php include_once 'control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: הפרופיל שלי</title>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" >
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
      document.getElementById("spVerifyResult").innerHTML = 'אימות הסיסמא הצליח';
    }
  }
  else
  {
    document.getElementById("spVerifyResult").style.color = 'red';
    document.getElementById("spVerifyResult").innerHTML = 'אימות הסיסמא נכשל';
  }
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename">הפרופיל שלי</span></td>
    </tr>
    <tr>
        <td>
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td><?php 
                  include_once 'control/error/ctlError.php';
                ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError || !$oRecord->CanModify) echo ' disabled="disabled" '; ?>>שמירה</button>
                  </td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <?php
                                                
                $txtName = new HtmlTextEditOneLang('שם', 'txtName', $oRecord->Name);
                $txtName->Required = TRUE;
                $txtName->ReadOnly = !$oRecord->CanModify;
                $txtName->EchoHtml();
                unset($txtName);
                
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                <tr>
                <?php                 
                $txtLoginName = new HtmlTextLabel('שם כניסה', 'txtLoginName', $oRecord->LoginName);
                $txtLoginName->EchoHtml();
                unset($txtLoginName);
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <tr>
                <?php                 
                $txtNewPassword = new HtmlTextEditOneLang('סיסמא חדשה', 'txtNewPassword', '');
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
                $txtVerifyPassword = new HtmlTextEditOneLang('אימות סיסמא', 'txtVerifyPassword', '');
                $txtVerifyPassword->ReadOnly = !$oRecord->CanModify;
                $txtVerifyPassword->SetAttribute("onkeyup","JavaScript:VerifyPassword();");
                $txtVerifyPassword->ControlType = HtmlTextEdit::PASSWORD;
                $txtVerifyPassword->EchoHtml();
                unset($txtVerifyPassword);
                
                ?>
                  <td><span id="spVerifyResult" name="spVerifyResult"></span></td>
                </tr>
                
                
                <tr>
                  <?php                     
                    $txtBalance = new HtmlTextLabel('יתרה', 'txtBalance', $oRecord->Balance);
                    $txtBalance->EchoHtml();
                    unset($txtBalance);
                  ?>
                  <td>&nbsp;</td>
                </tr>
                
                <?php
                    if ($oRecord->BalanceHeld != $oRecord->Balance) {
                        echo '<tr>';
                        $txtBalanceHeld = new HtmlTextLabel('יתרה בקופה', 'txtBalanceHeld', $oRecord->BalanceHeld);
                        $txtBalanceHeld->EchoHtml();
                        unset($txtBalanceHeld);
                        echo '<td>&nbsp;</td>',
                         '</tr>';
                    }
                ?>
                
                <?php
                    if ($oRecord->BalanceInvested != NULL && $oRecord->BalanceInvested != 0) {
                        echo '<tr>';
                        $txtBalanceInvested = new HtmlTextLabel('יתרה בהשקעה', 'txtBalanceInvested', $oRecord->BalanceInvested);
                        $txtBalanceInvested->EchoHtml();
                        unset($txtBalanceInvested);
                        echo '<td>&nbsp;</td>',
                         '</tr>';
                    }
                ?>
                
                <tr>
                  <?php                     
                    $txtPaymentMethod = new HtmlTextLabel('שיטת תשלום', 'txtPaymentMethod', $oRecord->PaymentMethodName);
                    $txtPaymentMethod->EchoHtml();
                    unset($txtPaymentMethod);
                  ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                  <?php                     
                    $txtPOBalance = new HtmlTextLabel('אחוז חריגה', 'txtPercentOverBalance', $oRecord->PercentOverBalance);
                    $txtPOBalance->EchoHtml();
                    unset($txtPOBalance);
                  ?>
                  <td><a id="percentoverbalancehlp" name="percentoverbalancehlp" class="tooltiphelp" href="#percentoverbalancehlp" >‏?‏<span class="helpspan">בכמה אחוזים מהיתרה ניתן לחרוג בכל הזמנה. כלל זה מוחל רק כאשר שיטת התשלום מאפשרת אחוז חריגה</span></a></td>
                </tr>
                <tr>
                <?php
                                                
                $txtEMail = new HtmlTextEditOneLang('כתובת דוא&quot;ל', 'txtEMail', $oRecord->EMail);
                $txtEMail->ReadOnly = !$oRecord->CanModify;
                $txtEMail->Required = TRUE;
                $txtEMail->EchoHtml();
                unset($txtEMail);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <tr>
                <?php
                                                
                $txtEMail2 = new HtmlTextEditOneLang('כתובת דוא&quot;ל 2', 'txtEMail2', $oRecord->EMail2);
                $txtEMail2->ReadOnly = !$oRecord->CanModify;
                $txtEMail2->EchoHtml();
                unset($txtEMail2);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                <?php
                                                
                $txtEMail3 = new HtmlTextEditOneLang('כתובת דוא&quot;ל 3', 'txtEMail3', $oRecord->EMail3);
                $txtEMail3->ReadOnly = !$oRecord->CanModify;
                $txtEMail3->EchoHtml();
                unset($txtEMail3);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                <tr>
                <?php
                                                
                $txtEMail4 = new HtmlTextEditOneLang('כתובת דוא&quot;ל 4', 'txtEMail4', $oRecord->EMail4);
                $txtEMail4->ReadOnly = !$oRecord->CanModify;
                $txtEMail4->EchoHtml();
                unset($txtEMail4);
                
                ?>
                <td>&nbsp;</td>
                </tr>
                
                
                <?php
                if ($oRecord->ID > 0)
                {
                  echo '<tr>';
                  
                  $txtJoinedOn = new HtmlTextLabel('הצטרפות', 'JoinedOn', 
                          $oRecord->JoinedOn->format('j.n.Y'));
                  $txtJoinedOn->EchoHtml();
                        
                  echo '<td>&nbsp;</td></tr>';
                }

                if (!$oRecord->IsRegularMember)
                {
                  echo '<tr>';

                  $arrFormats = array(Consts::EXPORT_FORMAT_MS_EXCEL_XML => 'MS Excel xml',
                                      Consts::EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS => 'Libre Office flat ods');
                  $formatList = new HtmlSelectArray('FileFormat', 'תבנית קובץ',$arrFormats, $oRecord->ExportFormat
                      );
                  $formatList->EncodeHtml = FALSE; //already encoded
                  $formatList->EchoHtml();

                  echo '<td><a id="exportformathlp" name="exportformathlp" class="tooltiphelp" href="#exportformathlp" >‏?‏<span class="helpspan">תבנית הקובץ המועדפת ליצוא נתונים.</span></a></td></tr>';
                }
                ?>
                </table>
                </td></tr></table>
        </td>
    </tr>
    <tr>
      <td>
        <?php 
        include_once 'control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>