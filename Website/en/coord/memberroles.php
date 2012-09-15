<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new MemberRoles;
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
            $g_oError->AddError('Record saved successfully.');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case MemberRoles::POST_ACTION_REMOVE_ROLE:
          $bSuccess = $oTable->RemoveRole($nRoleID);
            
          if ($bSuccess)
            $g_oError->AddError('The record was deleted successfully.');
          else if ($oTable->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('The record was not deleted.');
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

  $sPageTitle = sprintf('%s - Roles', htmlspecialchars($oTable->Name));

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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>Enter Your Cooperative Name: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
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
<table cellspacing="0" cellpadding="0" width="908" >
    <tr>
      <td><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle; ?></span></td>
    </tr>
    <tr>
        <td>
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" height="100%" >
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                  <td colspan="7"><?php 
                include_once '../control/error/ctlError.php';
                  ?></td>
                </tr>
                <tr>
                  <?php
                   echo '<td colspan="2"><a href="member.php?id=', $oTable->ID, '" >', 
                        sprintf('Back to %s', htmlspecialchars($oTable->Name)), '</a></td>';
                  ?>
                </tr>
                <tr>
                  <td class="columntitlelong">Role</td>
                  <td class="columntitlenowidth"></td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='2'>&nbsp;</td></tr><tr><td align='center' colspan='2'>There are no existing roles to this member</td></tr>";
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
                    echo "<tr><td colspan='2'>&nbsp;</td></tr><tr><td align='center' colspan='2'>This member has all existing roles. There are no roles to add</td></tr>";
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
