<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new MemberProducers();
$oMemberTabInfo = NULL;
$recTable = NULL;
$nProducerID = 0;
$nExistingRec = 0;
$nValue = 0;
$sPageTitle = '';
$bFullEdit = FALSE;

try
{
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidOriginalData'] ) )
      $oTable->SetSerializedData( $_POST["hidOriginalData"] ); //sets data directly to the class properties
    
    if (!$oTable->CheckAccess())
    {
       RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
       exit;
    }

    if ( isset( $_POST['hidProducer'] ) && !empty($_POST['hidProducer']) )
      $nProducerID = intval($_POST['hidProducer']);
    
    if ( isset( $_POST['hidValue'] ) && !empty($_POST['hidValue']) )
      $nValue = intval($_POST['hidValue']);
    
    if ( isset( $_POST['hidExistingRec'] ) && !empty($_POST['hidExistingRec']) )
      $nExistingRec = intval($_POST['hidExistingRec']);
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case MemberProducers::POST_ACTION_BLOCK:
          $bSuccess = $oTable->BlockFromFacet($nProducerID, $nValue, ($nExistingRec == 0));
          
          if ($bSuccess)
            $g_oError->AddError('Record saved successfully.', 'ok');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case MemberProducers::POST_ACTION_FILTER:
          $bSuccess = $oTable->RemoveFromFacet($nProducerID, $nValue, ($nExistingRec == 0));
            
          if ($bSuccess)
            $g_oError->AddError('Record saved successfully.', 'ok');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
      }
      
      switch($oTable->LastOperationStatus)
      {
        case SQLBase::OPERATION_STATUS_NO_PERMISSION:
        case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
          RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
          exit;
      }
    }
  }
  else if (isset($_GET['id']))
      $oTable->ID = intval($_GET['id']);

  $recTable = $oTable->GetTable();

  switch($oTable->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $bFullEdit = $oTable->HasPermission(MemberProducers::PERMISSION_EDIT);
  
  $oMemberTabInfo = new MemberTabInfo($oTable->ID, MemberTabInfo::PAGE_PRODUCERS);

  $sPageTitle = sprintf('%s - Producers', htmlspecialchars($oTable->Name));
  
  $oMemberTabInfo->MainTabName = $oTable->Name;
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
<title>Enter Your Cooperative Name: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function SetBlock(nProducerID, nValue, nExistingRec)
{
  PreventMultiplePostBack(); //so no multiple postbacks
  document.getElementById("hidPostAction").value = <?php echo MemberProducers::POST_ACTION_BLOCK; ?>;
  document.getElementById("hidProducer").value = nProducerID;
  document.getElementById("hidValue").value = nValue;
  document.getElementById("hidExistingRec").value = nExistingRec;
  document.frmMain.submit();
}
function SetFilter(nProducerID, nValue, nExistingRec)
{
  PreventMultiplePostBack(); //so no multiple postbacks
  document.getElementById("hidPostAction").value = <?php echo MemberProducers::POST_ACTION_FILTER; ?>;
  document.getElementById("hidProducer").value = nProducerID;
  document.getElementById("hidValue").value = nValue;
  document.getElementById("hidExistingRec").value = nExistingRec;
  document.frmMain.submit();
}
function PreventMultiplePostBack()
{
  var arrInputs = document.getElementsByTagName('input');
    // loop through all collected objects
    for (i = 0; i < arrInputs.length; i++) {
        if (arrInputs[i].type === 'checkbox' && ( arrInputs[i].name.indexOf('chkBlock') == 0  ||
                                                arrInputs[i].name.indexOf('chkRemove') == 0 )) 
            arrInputs[i].disabled = true;
    }
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oTable->GetSerializedData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidProducer" name="hidProducer" value="" />
<input type="hidden" id="hidValue" name="hidValue" value="" />
<input type="hidden" id="hidExistingRec" name="hidExistingRec" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth">
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                  <td colspan="3"><?php 
                include_once '../control/error/ctlError.php';
                  ?></td>
                </tr>
                <tr>
                <td colspan="3"><?php 
                  include_once '../control/membertab.php';
                ?></td>
                </tr>
                <tr>
                  <td class="columntitlelong">Producer Name</td>
                  <td class="columntitleshort">Removed</td>
                  <td class="columntitlenowidth">Blocked</td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='2'>&nbsp;</td></tr><tr><td align='center' colspan='2'>No records.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      echo "<tr>";
                      
                      //name
                      echo "<td>"  , htmlspecialchars($recTable["sProducer"]) ,  "</td>";
                      
                      //Remove Filter
                      echo '<td>';
                      
                      echo '<input type="checkbox" name="chkRemove[]"';
                      if ($recTable['bRemoved'])
                      {
                        echo ' checked ';
                        $nValue = 0;
                      }
                      else
                        $nValue = 1;
                      
                      if ($bFullEdit)
                      {
                        echo ' onchange="JavaScript:SetFilter(', $recTable['ProducerKeyID'], ',', $nValue, ',';
                                            
                        if ($recTable['MPRID'] != NULL)
                          echo '1';
                        else
                          echo '0';

                         echo ');"'; 

                        echo ' />';
                      }
                      else
                        echo ' disabled="disabled" ';                   
                      echo '</td>';
                                            
                      //Block
                      echo '<td>';
                      
                      echo '<input type="checkbox" name="chkBlock[]"';
                      if ($recTable['bBlocked'])
                      {
                        echo ' checked ';
                        $nValue = 0;
                      }
                      else
                        $nValue = 1;
                      
                      echo ' onchange="JavaScript:SetBlock(', $recTable['ProducerKeyID'], ',', $nValue, ',';                     
                      if ($recTable['MPRID'] != NULL)
                        echo '1';
                      else
                        echo '0';

                      echo ');"'; 
                      
                      echo ' />';
                                            
                      echo '</td>';
                      
                      
                      echo '</tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
?>
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
