<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new Members;
$recTable = NULL;
$sSearch = NULL;
$bShowMails = FALSE;
$sMailList = NULL;
$arrList = $oTable->GetExportList();
$bFirstRequest = false;
$PostAction = Members::POST_ACTION_SEARCH;

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidOriginalData'] ) )
      $oTable->SetSerializedData( $_POST["hidOriginalData"] );
    
    if (isset($_POST['hidPostAction']) && !empty( $_POST['hidPostAction'] )) {
      $PostAction = intval($_POST['hidPostAction']);
    }
    switch($PostAction)
    {
      case Members::POST_ACTION_SEARCH:
          $oTable->SearchPhrase = NULL;
          if ( isset( $_POST['txtSearch'] ) && !empty($_POST['txtSearch']) )
            $oTable->SearchPhrase = $_POST['txtSearch'];    
        break;
      case Members::POST_ACTION_LIST_SELECT:

         $sCtl = HtmlSelectArray::PREFIX . 'DataSet';
         if ( isset( $_POST[$sCtl] ))
         {
            $nAction = intval($_POST[$sCtl]);
            if ($nAction == MEMBERS::EXPORT_LIST_ITEM_SELECTED_MEMBERS_EMAILS)
            {
              if (isset($_POST["chkMember"]))
              {
                $oTable->MemberIDs = implode(",", $_POST["chkMember"]);
                $sMailList = $oTable->GetMailingList();
              }
              else
                $g_oError->AddError('No member was selected.');
            }
        }
        break;
    } 
    
  $recTable = $oTable->GetTable();

    if ($oTable->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION)
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }
  }
  else {
    $bFirstRequest = true;
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
<title>Enter Your Cooperative Name: Members</title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function ListSelect()
{
  var nAction = document.getElementById("selDataSet").value;
  if (nAction == 0)
    return;
  
  if (nAction == <?php echo MEMBERS::EXPORT_LIST_ITEM_SELECTED_MEMBERS_EMAILS; ?>)
  {
    document.getElementById("hidPostAction").value = <?php echo MEMBERS::POST_ACTION_LIST_SELECT; ?>;
    document.frmMain.submit();
  }
  else if (nAction == <?php echo MEMBERS::EXPORT_LIST_ITEM_ALL_MEMBERS_DATA; ?>)
  {
    window.open("membersexportres.php","_blank", "status=0,toolbar=0,menubar=0,top=150, left=100, width=400,height=400");
    document.getElementById("selDataSet").selectedIndex = 0;
  }
}
function Search()
{
  //default post action
  document.frmMain.submit();
}
function SelectAll(bCheck)
{
  var arrInputs = document.getElementsByTagName('input');
    // loop through all collected objects
    for (i = 0; i < arrInputs.length; i++) {
        if (arrInputs[i].type === 'checkbox' && arrInputs[i].name.indexOf('chkMember') == 0) 
            arrInputs[i].checked = bCheck;
    } 
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oTable->GetSerializedData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="2" class="fullwidth" >
    <tr>
      <td colspan="4" ><span class="pagename">Members</span></td>
    </tr>
    <tr>
      <td nowrap>
        <input id="txtSearch" name="txtSearch" type="text" maxlength="100" value="<?php 
            echo htmlspecialchars($oTable->SearchPhrase); ?>"/><span class="link" onclick="JavaScript:Search();" ><img border="0" title="Search" src="../img/view-refresh-8.png" /></span>&nbsp;
      </td>
      <?php
          $selList = new HtmlSelectArray('DataSet', 'Data Set', $arrList, 0);
          $selList->EncodeHtml = FALSE; //already encoded
          $selList->OnChange = "JavaScript:ListSelect();";
          $selList->EchoHtml();
       ?>
      <td width="100%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="4">
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="7"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                  <?php
                  if ($sMailList != NULL)
                  {
                    echo '<tr><td colspan="7">';
                      $txtMailList = new HtmlTextEdit('MailList', 'ltr', HtmlTextEdit::TEXTAREA, $sMailList);
                      $txtMailList->CssClass = "mailinglist";
                      $txtMailList->EncloseInHtmlCell = FALSE;
                      $txtMailList->EchoEditPartHtml();

                    echo '</td></tr>';
                  }
                  ?>
                  <tr>
                    <td colspan="7"><a href="member.php" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a>
                      &nbsp;<span class="link" onclick="JavaScript:SelectAll(true);">All</span>&nbsp;
                      <span class="link" onclick="JavaScript:SelectAll(false);">None</span>
                    </td>
                  </tr>
                  <?php
                  if (!$bFirstRequest) {
                    
                  ?>
                <tr>
                  <td class="columntitletiny"></td>
                  <td class="columntitlelong">Name</td>
                  <td class="columntitle">Joined On</td>
                  <td class="columntitle">User name</td>
                  <td class="columntitletiny">Balance</td>
                  <td class="columntitle">Balance Held</td>
                  <td class="columntitlelong">Payment Method</td>
                  <td class="columntitlelong">Email address</td>
                  <td class="columntitletiny"></td>
                  
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='7'>&nbsp;</td></tr><tr><td align='center' colspan='7'>No records.</td></tr>";
                }
                else
                {
                  $sCommentsTooltipID = '';
                  while ( $recTable )
                  {
                      echo "<tr>";
                      
                      echo '<td>';
                      
                      //do not allow selecting disabled members
                      if (!$recTable["bDisabled"])
                      {
                        echo '<input type="checkbox" name="chkMember[]" value="',$recTable["MemberID"], '"';

                        //restore is checked
                        if (isset($_POST["chkMember"]))
                        {
                          if (in_array($recTable["MemberID"], $_POST["chkMember"]))
                            echo ' checked ';
                        }

                        echo ' />';
                      }
                      
                      echo '</td>';
                      
                      //name
                      echo "<td><a href='member.php?id=",$recTable["MemberID"],"' >"  , htmlspecialchars( $recTable["sName"] ) ,  "</a></td>";
                      
                      //joined on
                      $oDate = new DateTime($recTable["dJoined"], $g_oTimeZone);
                      echo '<td>' , $oDate->format('n.j.Y') ,  '</td>';
                      
                      //login name
                      echo '<td>', htmlspecialchars( $recTable["sLoginName"] ) ,  '</td>';
                      
                      //balance
                      echo '<td>' , $recTable["mBalance"] , '</td>';
                      
                      echo '<td>' , $recTable["mBalanceHeld"] , '</td>';
                      
                      //payment method
                      echo '<td>';
                      if ($recTable["PaymentMethodKeyID"] == Consts::PAYMENT_METHOD_PLUS_EXTRA)
                        echo sprintf('Up to Balance + %%%s Over', ($recTable["fPercentOverBalance"] + 0));
                      else 
                        echo $recTable["sPaymentMethod"];
                      
                      echo '</td>';
                                            
                      //Emails
                      echo '<td>' , htmlspecialchars($recTable["sEMail"]);
                      if ( $recTable["sEMail2"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail2"]);
                      if ( $recTable["sEMail3"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail3"]);
                      if ( $recTable["sEMail4"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail4"]);
                      
                      echo '</td>';

                      
                      
                      //comments
                      echo '<td>';
                      
                      if ($recTable["sComments"] != NULL)
                      {
                        $sCommentsTooltipID = 'commentshlp' . $recTable["MemberID"];
                        echo '<a id="', $sCommentsTooltipID, '" name="', $sCommentsTooltipID, '" href="#', $sCommentsTooltipID, '" class="tooltiphelp" >...<span class="helpspan">',
                             htmlspecialchars($recTable["sComments"]),
                             '</span></a>'; 
                      }
                      
                      echo '</td>';
                      
                      
                      echo '</tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
              }
?>
                </table>
                </td>
    </tr>
    <tr>
      <td  colspan="4">
        <?php 
        include_once '../control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>
