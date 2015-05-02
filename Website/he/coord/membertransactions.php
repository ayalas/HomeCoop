<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new TransactionTable();
$recTransaction = NULL;
$oMember = new Member();
$sPageTitle = '';
$g_nCountRecords = 0; //PAGING

try
{
  if (isset($_GET['id']))
    $oTable->MemberID = intval($_GET['id']);

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

  if (!$oMember->CheckAccess() || !$oMember->IsCoordinator || !$oMember->HasPermission(Member::PERMISSION_VIEW))
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if(!$oMember->LoadRecord($oTable->MemberID))
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  switch($oMember->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $oMemberTabInfo = new MemberTabInfo($oTable->MemberID, MemberTabInfo::PAGE_TRANSACTIONS);

  $sPageTitle = sprintf('תנועות כספיות של %s', htmlspecialchars($oMember->Name));
  
  $oMemberTabInfo->MainTabName = $oMember->Name;
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
<?php include_once '../control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td>
              <table cellspacing="0" cellpadding="2" width="100%">
              <tr>
                <td><?php 
              include_once '../control/error/ctlError.php';
                ?></td>
              </tr>
              <tr>
              <td><?php 
                include_once '../control/membertab.php';
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
              </table>
          <?php
          //PAGING
          $g_BasePageUrl = 'membertransactions.php?id=' . $oTable->MemberID;

          include_once '../control/paging.php';
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

