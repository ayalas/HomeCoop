<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new MemberRoles;
$oMemberTabInfo = NULL;
$recTable = NULL;
$arrTableToAdd = NULL;
$nRoleID = 0;
$bViewOnly = TRUE;
$sPageTitle = '';

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidOriginalData'] ) )
      $oTable->SetSerializedData( $_POST["hidOriginalData"] );

    if ( isset( $_POST['hidRole'] ) && !empty($_POST['hidRole']) )
      $nRoleID = intval($_POST['hidRole']);
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case MemberRoles::POST_ACTION_ADD_ROLE:
          $bSuccess = $oTable->AddRole($nRoleID);
          
          if ($bSuccess)
            $g_oError->AddError('<!$RECORD_SAVED$!>', 'ok');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('<!$RECORD_NOT_SAVED$!>');
          break;
        case MemberRoles::POST_ACTION_REMOVE_ROLE:
          $bSuccess = $oTable->RemoveRole($nRoleID);
            
          if ($bSuccess)
            $g_oError->AddError('<!$DELETE_SUCCESS$!>', 'ok');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('<!$DELETE_FAILURE$!>');
          break;
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
  
  $oMemberTabInfo = new MemberTabInfo($oTable->ID, MemberTabInfo::PAGE_ROLES);

  $sPageTitle = sprintf('<!$PAGE_TITLE_MEMBER_ROLES$!>', htmlspecialchars($oTable->Name));
  
  $oMemberTabInfo->MainTabName = $oTable->Name;

  if ($oTable->HasPermission(SQLBase::PERMISSION_COORD))
  {
    $arrTableToAdd = $oTable->GetOtherRoles();
    $bViewOnly = FALSE;
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
<?php include_once '../control/headtags.php'; ?>
<title><!$COOPERATIVE_NAME$!>: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function RemoveRole(nRole)
{
  DisableAll(); //so no multiple postbacks
  document.getElementById("hidPostAction").value = <?php echo MemberRoles::POST_ACTION_REMOVE_ROLE; ?>;
  document.getElementById("hidRole").value = nRole;
  document.frmMain.submit();
}
function AddRole(nRole)
{
  DisableAll(); //so no multiple postbacks
  document.getElementById("hidPostAction").value = <?php echo MemberRoles::POST_ACTION_ADD_ROLE; ?>;
  document.getElementById("hidRole").value = nRole;
  document.frmMain.submit();
}
function DisableAll()
{
  var arrInputs = document.getElementsByTagName('input');
    // loop through all collected objects
    for (i = 0; i < arrInputs.length; i++) {
        if (arrInputs[i].type === 'checkbox' && arrInputs[i].name.indexOf('chkRole') == 0) 
            arrInputs[i].disabled = true;
    }
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oTable->GetSerializedData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidRole" name="hidRole" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth">
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                  <td colspan="2"><?php 
                include_once '../control/error/ctlError.php';
                  ?></td>
                </tr>
                <tr>
                <td colspan="2"><?php 
                  include_once '../control/membertab.php';
                ?></td>
                </tr>
                <tr>
                  <td class="columntitlelong"><!$FIELD_ROLE_NAME$!></td>
                  <td class="columntitlenowidth"></td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='2'>&nbsp;</td></tr><tr><td align='center' colspan='2'><!$NO_EXISTING_ROLES$!></td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      echo "<tr>";
                      
                      //name
                      echo "<td>"  , $recTable["sRole"] ,  "</td>";
                      
                      echo '<td>';
                      
                      if (!$bViewOnly)
                      {
                        echo '<input type="checkbox" name="chkRole[]" checked onchange="JavaScript:RemoveRole(', $recTable["RoleKeyID"], ');" />';
                      }
                      
                      echo '</td></tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
                
                if (!$bViewOnly)
                {
                  if (!is_array($arrTableToAdd) || count($arrTableToAdd) == 0)
                  {
                    echo "<tr><td colspan='2'>&nbsp;</td></tr><tr><td align='center' colspan='2'><!$NO_ROLES_TO_ADD$!></td></tr>";
                  }
                  else
                  {
                    foreach ( $arrTableToAdd as $roleid => $rolename )
                    {
                        echo "<tr>";

                        //name
                        echo "<td>"  , $rolename ,  "</td>";

                        echo '<td><input type="checkbox" name="chkRole[]" onchange="JavaScript:AddRole(', 
                                $roleid, ');" /></td>';

                        echo '</tr>';
                    }
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
