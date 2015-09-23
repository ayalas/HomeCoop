<?php

include_once '../settings.php';
include_once '../authenticate.php';

function ProcessMember(&$recMember, $bMember)
{
  global $oCoordinate;
  global $g_oError;
  global $bExtendedGroupPermissions;
  echo "<tr><td>" ,  $recMember["sName"] ,  "</td>",

   '<td><input type="checkbox" value="1" id="chkMember_' ,  $recMember["MemberID"] ,  
  '" name="chkMember_' ,  $recMember["MemberID"] , '" ';
  if ($bMember)
    echo 'checked="1"';
  
  if ($bExtendedGroupPermissions)
    echo ' onchange="JavaScript:OnAddRemoveCoordinator(this.checked, \'chkMemberIsContact' ,  
          $recMember["MemberID"] ,  '\');"';
  else
    echo ' disabled="1" ';
    
   echo' /></td><td><input type="checkbox" value="1" ' ,
    'id="chkMemberIsContact' ,  $recMember["MemberID"] ,  
  '" name="chkMemberIsContact' ,  $recMember["MemberID"] ,'" ';
  
  if ($bMember)
  {
    if ($recMember["bContactPerson"])
      echo ' checked="1" ';
  }
  if (!$bMember || !$bExtendedGroupPermissions)
    echo ' disabled="1" ';

  echo ' /></td><td>';
  
  if ($oCoordinate->PrivateGroupMemberID == $recMember["MemberID"])
  {
    echo '<span class="bolddata">מתאמ/ת בלעדי/ת</span>&nbsp;';
  
    if ($oCoordinate->GetPermissionScope(SQLBase::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_COOP_CODE  && !$g_oError->HadError)
      echo '<button type="button" id="btnRemoveGroup' , $recMember["MemberID"] , '" name="btnRemoveGroup' , $recMember["MemberID"] , 
          '" onclick="JavaScript:RemoveGroup();" >הסרת תיאום</button>&nbsp;';
  }
  else if ($oCoordinate->GetPermissionScope(SQLBase::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_COOP_CODE  && !$g_oError->HadError)
    echo '<button type="button" id="btnAssignToMember' ,  $recMember["MemberID"] , '" name="btnAssignToMember' 
          ,  $recMember["MemberID"] , '" onclick="JavaScript:SetMemberAsCoordinator(' ,  $recMember["MemberID"] ,
            ');" >קביעה כמתאמ/ת בלעדי/ת</button>';

  echo '</td></tr>';
}

$oCoordinate = new Coordinate;
$bGetGroupNameFromList = FALSE;
$bNewGroup = TRUE;
$bLoadFromRecord = TRUE;
$bValidateUnauthorized = FALSE;
$bGroupPickWithUnauthorizedMembers = FALSE;
$recGroupList = NULL;
$recMembers = NULL;
$recNonMembers = NULL;
$bExtendedGroupPermissions = FALSE;
$sPageName = '';
$oPickupLocationTabInfo = NULL;

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {  
    if ( isset( $_POST['hidOriginalData'] ) )
    {
      //save original values for compare
      $oCoordinate->SetSerializedOriginalData( $_POST["hidOriginalData"] );
      //initialize the data with original values, to collect values provided originally in query string
      $oCoordinate->CopyBasicValuesFromOriginal();
    }

    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case Coordinate::POST_ACTION_CHANGE_GROUP:
          if ( isset( $_POST['selGroup'] ) )
          {
            $oCoordinate->GroupID = intval($_POST['selGroup']);
            $bGetGroupNameFromList = TRUE;
            $bLoadFromRecord = FALSE;
            $bValidateUnauthorized = TRUE;
          }
        break;
        case Coordinate::POST_ACTION_NEW:
          $oCoordinate->GroupID = 0;
          $bLoadFromRecord = FALSE;
          $bValidateUnauthorized = TRUE;
        break;
        case  Coordinate::POST_ACTION_REMOVE_GROUP:
          $bSuccess = $oCoordinate->RemoveGroup();
          if ($bSuccess)
            $g_oError->PushError('מתאמות/ים הוסרו בהצלחה.', 'ok');
          else
            $g_oError->PushError('כישלון בהסרת מתאמות/ים.');
          break;
        case Coordinate::POST_ACTION_DELETE:
          if ( isset( $_POST['hidPostValue'] ) )
          {
            $nSelected = intval($_POST['hidPostValue']);
            if ($nSelected > 0)
              $oCoordinate->GroupID = $nSelected;
          }
          $bSuccess = $oCoordinate->DeleteGroup();
          if ($bSuccess)
            $g_oError->PushError('המחיקה התבצעה בהצלחה.', 'ok');
          else
            $g_oError->PushError('המחיקה נכשלה.');
        break;
        case Coordinate::POST_ACTION_SET_MEMBER_AS_COORDINATOR:
          if ( isset( $_POST['hidPostValue'] ) )
          {
            $nNewCoordinator = intval($_POST['hidPostValue']);
            $bSuccess = $oCoordinate->SetMemberAsCoordinator($nNewCoordinator);
            if ($bSuccess)
              $g_oError->PushError('הנתונים נשמרו בהצלחה.', 'ok');
            else
              $g_oError->PushError('הנתונים לא נשמרו.');
          }
          break;
        case Coordinate::POST_ACTION_SAVE:
          
          $bUseGroupNameText = TRUE;
          //collect new data
          
          //Set group to be saved
          if ( isset( $_POST['hidPostValue'] ) )
            $oCoordinate->GroupID = intval($_POST['hidPostValue']);
          
          if ( isset( $_POST['radGroupName'] ) )
            $bUseGroupNameText = ($_POST['radGroupName'] == 1);

          if ($bUseGroupNameText)
          {
            if ( isset( $_POST['txtGroupName'] ) )
              $oCoordinate->GroupName = $_POST['txtGroupName'];
          }
          else
          {
            if ( isset( $_POST['selGroup'] ) )
              $oCoordinate->GroupID = intval($_POST['selGroup']);
          }

          $nKeyLen = strlen("chkMember_");
          $nContactLen = strlen("chkMemberIsContact");
          $aMembersByIDs = array();
          $sArrayKey = NULL;

          //get selected members
          foreach($_POST as $PostKey => $PostValue)
          {
            //if key begins with chkMember_ - take memberid if true
            if (mb_substr($PostKey, 0, $nKeyLen) == "chkMember_")
            {
              if ($PostValue)
                $aMembersByIDs[mb_substr($PostKey, $nKeyLen)] = FALSE;
            }
          }

          //IsContactPerson
          foreach($_POST as $PostKey => $PostValue)
          {
            if (mb_substr($PostKey, 0, $nContactLen) == "chkMemberIsContact")
            {
              $sArrayKey = mb_substr($PostKey, $nContactLen);
              if (array_key_exists($sArrayKey, $aMembersByIDs))
                $aMembersByIDs[$sArrayKey] = $PostValue;
            }
          }

          //submit members array in expected PDO-like form
          foreach($aMembersByIDs as $arrKey => $arrValue)
            $oCoordinate->AddMember(array("MemberID" => intval($arrKey), "bContactPerson" => $arrValue));

          $bSuccess = $oCoordinate->Save();

          if ($bSuccess)
            $g_oError->PushError('הנתונים נשמרו בהצלחה.', 'ok');
          else
            $g_oError->PushError('הנתונים לא נשמרו.');
          
          break;
      }

      switch($oCoordinate->LastOperationStatus)
      {
        case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
        case SQLBase::OPERATION_STATUS_NO_PERMISSION:
        case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
        case SQLBase::OPERATION_STATUS_PARAMETER_INCONSISTENT_WITH_DATA:
          RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
          exit;
        case SQLBase::OPERATION_STATUS_REQUIRED_FIELD_MISSING:
          $g_oError->AddError(' יש להזין שם קבוצה.');
          break;
        case SQLBase::OPERATION_STATUS_NO_LIST_ITEM_SELECTED:
          $g_oError->AddError(' לא נבחרו חברים/ות בקבוצה.');
          break;
        case SQLBase::OPERATION_STATUS_CANT_REMOVE_OWN_PERMISSION:
          $g_oError->AddError(' לא ניתן להסיר את הרשאות התיאום של עצמך. רק לשתף עם אחרות/ים.');
          break;
      }
    } //end of !empty(PostAction)
  } //end of request method is post
  else //request method is get
  {

    if (isset($_GET["rid"]))
      $oCoordinate->RecordID = intval($_GET["rid"]);

    if (isset($_GET["pa"]))
      $oCoordinate->PermissionArea = intval($_GET["pa"]);

    if (isset($_GET["id"]))
    {
      $oCoordinate->GroupID = intval($_GET["id"]);
      if (!$oCoordinate->ValidateRecordGroup())
      {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
      }
    }
    
    $bValidateUnauthorized = TRUE;
  }

  $recMembers = $oCoordinate->GetTable($bLoadFromRecord);

  //in all these cases, redirect to access-denied page
  switch($oCoordinate->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
    case SQLBase::OPERATION_STATUS_PARAMETER_INCONSISTENT_WITH_DATA:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ($bValidateUnauthorized && $oCoordinate->GroupID > 0)
  {
    if ($oCoordinate->HasUnauthorizedMembers)
    {
      if (!$bLoadFromRecord)
      {
        $bGroupPickWithUnauthorizedMembers = TRUE;
        $g_oError->AddError(sprintf('הקבוצה שנבחרה לא יכולה להיקבע כמתאמת כיוון שלחלק מחבריה אין הרשאת תיאום ל%1$s. החברים ללא הרשאות הן: %2$s ', $oCoordinate->RecordName,
          $oCoordinate->GetUnauthorizedMemberNames()), 'warning');
      }
      else
        $g_oError->AddError(sprintf('לחלק מחברי הקבוצה כבר אין הרשאת תיאום ל%1$s. החברים ללא הרשאות הן: %2$s ', $oCoordinate->RecordName,
          $oCoordinate->GetUnauthorizedMemberNames()), 'warning');
    }
  }

  if ($oCoordinate->GroupID > 0 && !$oCoordinate->IsPrivateGroup)
    $bNewGroup = FALSE;

  $bExtendedGroupPermissions = $oCoordinate->CheckGroupsExtendedPermission();

  $recNonMembers = $oCoordinate->GetNonMembers();

  $recGroupList = $oCoordinate->GetGroupList();

  if ($bExtendedGroupPermissions && $bGetGroupNameFromList)
  {
    $sName = array_search($oCoordinate->GroupID, $recGroupList);
    if ($sName)
      $oCoordinate->ResetGroupName( $sName );
  }
  
  $sPageName = htmlspecialchars(sprintf('מתאמים של %s', $oCoordinate->RecordName));
  
  switch($oCoordinate->PermissionArea)
  {
    case Consts::PERMISSION_AREA_PICKUP_LOCATIONS:
      $oPickupLocationTabInfo = new PickupLocationTabInfo($oCoordinate->RecordID, $oCoordinate->GroupID, 
      PickupLocationTabInfo::PAGE_COORD);
      
      $oPickupLocationTabInfo->MainTabName = $oCoordinate->RecordName;
    break;
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
<?php include_once '../control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageName; ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function OnAddRemoveCoordinator(bInclude, sIsContactCheckBox)
{
  ctlIsContact = document.getElementById(sIsContactCheckBox);
  if (!bInclude)
    ctlIsContact.checked = false;
  ctlIsContact.disabled = !bInclude;
}
function OnChangeGroupMode(nUseText)
{
  var ctlGroupName = document.getElementById("txtGroupName");
  if (nUseText == 1)
  {
    ctlGroupName.disabled = false;
    document.getElementById("selGroup").disabled = true;
    document.getElementById("btnSave").disabled = false;
    ctlGroupName.setAttribute("required", "required");
  }
  else //if (nUseText == 2)
  {
    ctlGroupName.removeAttribute("required");
    ctlGroupName.disabled = true;
    document.getElementById("selGroup").disabled = false;
    document.getElementById("btnSave").disabled = true;
  }
}
function LoadGroup()
{
  if (document.getElementById("selGroup").selectedIndex > 0)
  {
    document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_CHANGE_GROUP; ?>;
    document.frmMain.submit();
  }
}
function NewGroup()
{
  document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_NEW; ?>;
  document.frmMain.submit();
}
function DeleteGroup()
{
  if (confirm(decodeXml('נא אשר/י או בטל/י את פעולת המחיקה')))
  {
    document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_DELETE; ?>;
    document.getElementById("hidPostValue").value = <?php echo $oCoordinate->GroupID; ?>;
    document.frmMain.submit(); 
  }
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_SAVE; ?>;
  document.getElementById("hidPostValue").value = <?php echo $oCoordinate->GroupID; ?>;
}
function RemoveGroup()
{
  document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_REMOVE_GROUP; ?>;
  document.frmMain.submit();
}
function SetMemberAsCoordinator(nMemberID)
{
  document.getElementById("hidPostAction").value = <?php echo Coordinate::POST_ACTION_SET_MEMBER_AS_COORDINATOR; ?>;
  document.getElementById("hidPostValue").value = nMemberID;
  document.frmMain.submit(); 
}

</script>
</head>
<body class="centered" >
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oCoordinate->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >   
    <tr>
    <?php
        switch($oCoordinate->PermissionArea)
        {
        case Consts::PERMISSION_AREA_PICKUP_LOCATIONS:
          echo '<td>';
          include_once '../control/pickuploctab.php';
          echo '</td>';
          break;
        default:
          echo '<td class="fullwidth"><span class="pagename">',
              $sPageName, '</span></td>';
          break;
        }
      ?>
    </tr>
    <tr>
        <td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td colspan="5"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                </tr>
                <tr><td colspan="5"><?php
                  if ((!$bNewGroup || $bExtendedGroupPermissions) && !$bGroupPickWithUnauthorizedMembers && !$g_oError->HadError)
                  {
                    echo '<button type="submit" id="btnSave" name="btnSave" onclick="JavaScript:Save();" >';
                    if ($bNewGroup)
                      echo 'שמירת קבוצה חדשה'; 
                    else
                      echo 'שמירה';
                    echo '</button>&nbsp;';
                  }
                  if ($bExtendedGroupPermissions && !$bNewGroup && !$g_oError->HadError) {
                     echo '<button type="button" id="btnNewGroup" name="btnNewGroup" onclick="JavaScript:NewGroup();" >',
                             'קבוצה חדשה</button>&nbsp;';
                  }
                  if ($oCoordinate->GetPermissionScope(SQLBase::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_COOP_CODE 
                          &&  $oCoordinate->OriginalGroupID > 0 
                          && !$oCoordinate->IsOriginalPrivateGroup
                           && !$g_oError->HadError) //remove works on saved data
                        echo '<button type="button" id="btnRemoveGroup" name="btnRemoveGroup" onclick="JavaScript:RemoveGroup();" >',
                          'הסרת תיאום</button>&nbsp;';                     
                  
                  if ($oCoordinate->GroupID > 0 && !$oCoordinate->IsPrivateGroup 
                      && $bExtendedGroupPermissions && !$g_oError->HadError)
                  {
                    echo '<button type="button" id="btnDeleteGroup" name="btnDeleteGroup" onclick="JavaScript:DeleteGroup();"',
                            ' >מחיקת קבוצה</button>&nbsp;';
                  }
                  ?>                  
                  </td></tr> 
                <tr>
                  <td nowrap>
                    <?php if ($bExtendedGroupPermissions || !$bNewGroup) 
                      {
                        echo '<label for="txtGroupName">';
                        if ($bNewGroup) 
                          echo 'קבוצה חדשה:'; 
                        else 
                          echo 'קבוצה נוכחית:'; 

                        echo '</label>';
                      }
                  ?></td>
                  
                  <td >
                    <?php if ($bExtendedGroupPermissions) 
                            echo '<input type="radio" value="1" onchange="JavaScript:OnChangeGroupMode(this.value);" id="radGroupNameText" name="radGroupName" checked="true" />';
                  ?>
                  </td>
                  <td ><?php
                    if ($bExtendedGroupPermissions || !$bNewGroup)
                    {
                      echo '<input type="text" required="required" id="txtGroupName" name="txtGroupName" value="',
                         $oCoordinate->GroupName, '" ';
                      if (!$bExtendedGroupPermissions)
                        echo 'disabled="1" ';
                      echo ' />';
                    }
                   ?></td>
                  <td >
                  <?php if ($recGroupList) { 
                      echo '<input type="radio" value="2" onchange="JavaScript:OnChangeGroupMode(this.value);" id="radGroupNameSelect" ',
                              'name="radGroupName"'; 
                      if (!$bExtendedGroupPermissions)
                        echo ' checked="1" ';
                      
                      echo ' />';
                    }
                    ?></td>
                  <td width="100%">
                    <?php if ($recGroupList) { 
                      echo '<select id="selGroup" title="קבוצה אחרת" name="selGroup" ';
                      if ($bExtendedGroupPermissions) 
                        echo ' disabled="true" ';
                      echo ' onchange="JavaScript:LoadGroup();" ><option value="0">בחר/י מהרשימה</option>';
                      foreach($recGroupList as $sGroup => $nGroup)
                      {
                        echo '<option value="' , $nGroup ,  '" '; 
                        if ($nGroup == $oCoordinate->GroupID)
                          echo 'selected';
                        echo ' >' , $sGroup , '</option>';
                      }
                      
                      echo '</select>';
                    }
                    ?>
                  </td>
                </tr>
                </table>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                  <td class="columntitle">מתאמ/ת</td>
                  <td class="columntitle">כלול בקבוצה?</td>
                  <td class="columntitle">האם איש קשר?</td>
                  <td class="columntitlenowidth"></td>
                </tr>
                <?php
                if ($recMembers)
                {
                  foreach ( $recMembers as $recMember )
                  {
                      ProcessMember($recMember, TRUE);
                  }
                }
          
                if ($recNonMembers)
                {
                  foreach ( $recNonMembers as $recMember )
                  {
                      ProcessMember($recMember, FALSE);
                  }
                }
  
                if ($g_oMemberSession->IsSysAdmin)
                {
                  echo '<tr><td colspan="4">&nbsp;</td></tr><tr><td colspan="4">למתאמים/ות נוספות/ים יש להגדיר תחילה לחברות הרלוונטיים הרשאות תיאום לאיזור ההרשאה</td></tr>';
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
