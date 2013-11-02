<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = 'New Producer';
$oRecord = new Producer;
$bReadOnly = TRUE;

try
{
  if (!$oRecord->CheckAccess()) //completely denied from page
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidProducerId'] ) && !empty($_POST['hidProducerId']) )
      $oRecord->ProducerID = intval($_POST['hidProducerId']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case Producer::POST_ACTION_SAVE:
          $oRecord->ProducerNames = ComplexPostData::GetNames('txtProducer');

          if ( isset( $_POST['ctlIsDisabled'] ))
            $oRecord->IsDisabled = (intval($_POST['ctlIsDisabled']) == 1);
          
          if ( isset( $_POST['txtExportFileName'] ) && !empty($_POST['txtExportFileName']))
            $oRecord->ExportFileName = $_POST['txtExportFileName'];

          $bSuccess = false;
          if ($oRecord->ProducerID > 0)
            $bSuccess = $oRecord->Edit();
          else
            $bSuccess = $oRecord->Add();

          if ( $bSuccess )
          {
              $g_oError->AddError('Record saved successfully.', 'ok');
              $sPageTitle = $oRecord->ProducerName;
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case Producer::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('producers.php');
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
    //editing existing producer, for loading a specific producer, access may be denied completely
    if(!$oRecord->LoadRecord(intval($_GET['id'])))
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }

    $sPageTitle = $oRecord->ProducerName;
  }
  
  $bReadOnly = !$oRecord->HasPermission(Producer::PERMISSION_COORD);
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
    document.getElementById("hidPostAction").value = <?php echo Producer::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo Producer::POST_ACTION_SAVE; ?>;
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidProducerId" name="hidProducerId" value="<?php echo $oRecord->ProducerID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="948"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td><?php 
                  include_once '../control/error/ctlError.php';
                ?></td>
                </tr>
                <tr>
                  <td>
                  <?php
                  if (!$bReadOnly && !$g_oError->HadError)
                  {
                    echo '<button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save">Save</button>';

                    if ($oRecord->ProducerID > 0 && $oRecord->CheckDeletePermission())
                    {
                     echo '&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete">Delete</button>'; 
                    } 
                  }
                  ?></td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <td></td>
                <?php
                  HtmlTextEditMultiLang::EchoColumnHeaders();
                ?>
                <td></td>
                </tr>
                <tr>
                 <?php
                  $txtProducer = new HtmlTextEditMultiLang('Producer Name', 'txtProducer', HtmlTextEdit::TEXTBOX, 
                        $oRecord->ProducerNames);
                  $txtProducer->Required = TRUE;
                  $txtProducer->ReadOnly = $bReadOnly;
                  $txtProducer->EchoHtml();
                  unset($txtProducer);
                 
                 ?>
                <td width="100%">&nbsp;</td>
                </tr>
                
                <tr>
                  <?php           
                    $txtExportFileName = new HtmlTextEditOneLang('Export File Name', 'txtExportFileName', $oRecord->ExportFileName);
                    $txtExportFileName->MaxLength = Producer::MAX_LENGTH_EXPORT_FILE_NAME;
                    $txtExportFileName->ReadOnly = $bReadOnly;
                    $txtExportFileName->EchoHtml();
                    unset($txtExportFileName);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr> 
                
                <tr>                 
                  <?php
                    $oIsDisabled = new HtmlSelectBoolean('ctlIsDisabled', 'Status', $oRecord->IsDisabled, 'Inactive', 
                            'Active');
                    $oIsDisabled->ReadOnly = $bReadOnly;
                    $oIsDisabled->EchoHtml();
                    unset($oIsDisabled);
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
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
