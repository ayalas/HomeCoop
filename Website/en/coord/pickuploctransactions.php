<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new TransactionTable();
$recTransaction = NULL;
$oRecord = new PickupLocation;
$sPageTitle = '';
$oPickupLocationTabInfo = NULL;

try
{
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if (!isset($_GET['id']))
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if(!$oRecord->LoadRecord(intval($_GET['id'])))
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $sPageTitle = sprintf('%s - Transactions', htmlspecialchars($oRecord->Name));

  switch($oRecord->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $oTable->PickupLocationID = $oRecord->ID;
  $oTable->PickupLocationGroupID = $oRecord->CoordinatingGroupID;
  
  $recTransaction = $oTable->LoadTable();

  switch($oTable->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $oPickupLocationTabInfo = new PickupLocationTabInfo($oRecord->ID, $oRecord->CoordinatingGroupID, 
    PickupLocationTabInfo::PAGE_TRANSACTIONS);
  
  $oPickupLocationTabInfo->MainTabName = htmlspecialchars($oRecord->Name);
  
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
<title>Enter Your Cooperative Name: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
    <td><?php 
      include_once '../control/pickuploctab.php';
    ?></td>
    </tr>
    <tr>
    <td><?php 
      include_once '../control/error/ctlError.php';
    ?></td>
    </tr>
    <tr>
      <td class="resgridparent">
          <?php
            $oRenderer = new TransactionTableHtml($oTable);
            $oRenderer->EchoHtml($recTransaction);
            $oRenderer->TableObject = NULL;
            unset($oRenderer);
          ?>
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
