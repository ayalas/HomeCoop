<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = 'New Pickup Location';

$oRecord = new PickupLocation;

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
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          
          $oRecord->Names = ComplexPostData::GetNames('txtName');
                    
          $oRecord->AddressStrings = ComplexPostData::GetNames('txtAddress');
          
          $oRecord->PublishedComments = ComplexPostData::GetNames('txtPublishedComments');
          
          $oRecord->AdminComments = ComplexPostData::GetNames('txtAdminComments');
          
          $oRecord->MaxBurden = NULL;
          if ( isset( $_POST['txtMaxBurden'] ) && !empty($_POST['txtMaxBurden']))
            $oRecord->MaxBurden = 0 + $_POST['txtMaxBurden'];
          
          $oRecord->RotationOrder = NULL;
          if ( isset( $_POST['txtRotationOrder'] ) && !empty($_POST['txtRotationOrder']))
            $oRecord->RotationOrder = 0 + $_POST['txtRotationOrder'];
          
           if ( isset( $_POST['txtExportFileName'] ) && !empty($_POST['txtExportFileName']))
            $oRecord->ExportFileName = $_POST['txtExportFileName'];
               
          if ( isset( $_POST['ctlIsDisabled'] ))
            $oRecord->IsDisabled = (intval($_POST['ctlIsDisabled']) == 1);
          
          if ( isset( $_POST['txtCachier'] ))
            $oRecord->Cachier = 0 + $_POST['txtCachier'];

          $bSuccess = false;
          if ($oRecord->ID > 0)
          {
            $oRecord->PreserveFormValues(); //preserve values unchanged or only possibly changed inside the Edit method
            $bSuccess = $oRecord->Edit();
          }
          else
            $bSuccess = $oRecord->Add();

          if ( $bSuccess )
          {
              $g_oError->AddError('Record saved successfully.');
              $sPageTitle = $oRecord->Name;
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('pickuplocs.php');
              exit;
          }
          else
              $g_oError->AddError('The record was not deleted.');
          
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

    $sPageTitle = $oRecord->Name;
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
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->ID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td><?php 
                  include_once '../control/error/ctlError.php';
                ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError) echo ' disabled="disabled" '; ?>>Save</button><?php 
                  if (!$g_oError->HadError && $oRecord->ID > 0 && $oRecord->CheckDeletePermission())
                  {
                   echo '&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete">Delete</button>'; 
                  } ?>
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
                
                $txtName = new HtmlTextEditMultiLang('Location Name', 'txtName', HtmlTextEdit::TEXTBOX, $oRecord->Names);
                $txtName->Required = TRUE;
                $txtName->EchoHtml();
                unset($txtName);
                
                ?>
                <td></td>
                </tr>               
                <tr>
                <?php
                                
                $txtAddress = new HtmlTextEditMultiLang('Address', 'txtAddress', HtmlTextEdit::TEXTAREA, $oRecord->AddressStrings);
                $txtAddress->Required = TRUE;
                $txtAddress->EchoHtml();
                unset($txtAddress);
                
                ?>
                <td></td>
                </tr>
                
                
                
                <tr>
                <?php
                
                $txtPublishedComments = new HtmlTextEditMultiLang('Pickup Instructions', 'txtPublishedComments', 
                        HtmlTextEdit::TEXTAREA, $oRecord->PublishedComments);
                $txtPublishedComments->EchoHtml();
                unset($txtPublishedComments);
                
                ?>
                <td></td>
                </tr>
                
                <tr>
                <?php
                                
                $txtAdminComments = new HtmlTextEditMultiLang('Coordination Comments', 'txtAdminComments', HtmlTextEdit::TEXTAREA, 
                        $oRecord->AdminComments);
                $txtAdminComments->EchoHtml();
                unset($txtAdminComments);
                
                ?>
                <td></td>
                </tr>
                
                
                <tr>
                  <?php
                    $oIsDisabled = new HtmlSelectBoolean('ctlIsDisabled', 'Status', $oRecord->IsDisabled, 'Inactive', 
                            'Active');
                    $oIsDisabled->EchoHtml();
                    unset($oIsDisabled);
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                
                <tr>
                  <?php                     
                    $txtRotationOrder = new HtmlTextEditNumeric('Rotation Order', 'txtRotationOrder', $oRecord->RotationOrder);
                    $txtRotationOrder->EchoHtml();
                    unset($txtRotationOrder);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php                     
                    $txtMaxBurden = new HtmlTextEditNumeric('Delivery Capacity', 'txtMaxBurden', $oRecord->MaxBurden);
                    $txtMaxBurden->EchoHtml();
                    unset($txtMaxBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('The maximum capacity of the pickup location in terms of the product field &quot;Burden&quot;. the sum for all products of &quot;Burden&quot; times product quantity will be compared to this value for all the members&#x27; orders that have this pickup location selected. This is only a default value and can be overwritten in the cooperative order&#x27;s pickup location settings. If not overridden in the coop order, members will not be able to place an order that exceeds the limitation set here.');
                  ?>
                </tr> 
                
                <tr>
                  <?php                     
                    $txtCachier = new HtmlTextEditNumeric('Cashier', 'txtCachier', $oRecord->Cachier);
                    $txtCachier->EchoHtml();
                    unset($txtCachier);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php
                  
                    $sDate = '';
                    if ($oRecord->CachierDate != NULL)
                      $sDate = $oRecord->CachierDate->format('n.j.Y g:i A');
                  
                    $lblCachierDate = new HtmlTextLabel('Cashier Update', 'lblCachierDate', $sDate);
                    $lblCachierDate->SetAttribute('dir','ltr');
                    $lblCachierDate->EchoHtml();
                    unset($lblCachierDate);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php
                  
                    $lblPrevCachier = new HtmlTextLabel('Prev. Cashier', 'lblPrevCachier', $oRecord->PrevCachier);
                    $lblPrevCachier->EchoHtml();
                    unset($lblPrevCachier);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php           
                    $txtExportFileName = new HtmlTextEditOneLang('Export File Name', 'txtExportFileName', $oRecord->ExportFileName);
                    $txtExportFileName->MaxLength = PickupLocation::MAX_LENGTH_EXPORT_FILE_NAME;
                    $txtExportFileName->EchoHtml();
                    unset($txtExportFileName);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr> 
                
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
