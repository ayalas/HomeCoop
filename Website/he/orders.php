<?php

include_once 'settings.php';
include_once 'authenticate.php';

$oData = new MemberOrders;
$recTable = NULL;
$sPageTitle = 'ההזמנות שלי';
$g_nCountRecords = 0; //PAGING

try
{
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'GET' )
  {
    if (isset($_GET['id'])) {
      $oData->MemberID = intval($_GET['id']);
      
      if (isset($_GET['name'])) {
        $oData->MemberName = $_GET['name'];
      }
    }
    else {
      $oData->MemberID = $g_oMemberSession->MemberID;
    }
  }
  
  $recTable = $oData->LoadDataByMember();
  
  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ($g_oMemberSession->MemberID != $oData->MemberID)
    $sPageTitle = sprintf('ההזמנות של %s', htmlspecialchars ($oData->MemberName));
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
<?php include_once 'control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" >
function SwitchMember()
{
  var ctl = document.getElementById("<?php echo HtmlSelectArray::PREFIX , 'Member';?>");

  document.location.href = 'orders.php?id=' + ctl.options[ctl.selectedIndex].value + '&name=' +
          encodeURIComponent(ctl.options[ctl.selectedIndex].text) + '&pg=1';
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="" />
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td class="fullwidth"><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td height="100%" >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td colspan="6"><?php include_once 'control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
            <?php
              if ($oData->HasPermission(SQLBase::PERMISSION_COORD))
              {
                echo '<td colspan="5"><table cellspacing="0" cellpadding="0" width="100%"><tr>';
                $oMembersList = new Members;
                $arrMembers = $oMembersList->GetMembersListForOrders();
                $selMembers = new HtmlSelectArray('Member', 'חבר/ה', $arrMembers, $oData->MemberID);
                $selMembers->Required = TRUE;
                $selMembers->OnChange = "JavaScript:SwitchMember();";
                $selMembers->EchoHtml();
                unset($selMembers);
                unset($arrMembers);
                unset($oMembersList);
                echo '<td></td></tr></table></td>';
              }
            ?>
            </tr>
            <tr>
              <td class="columntitlelong">כותרת הזמנה</td>
              <td class="columntitleshort">משלוח</td>
              <td class="columntitleshort">מצב</td>
              <td class="columntitleshort">ת. הזמנה</td>
              <td class="columntitle">מקום האיסוף</td>
              <td class="columntitlenowidth">סכום</td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='6'>&nbsp;</td></tr><tr><td align='center' colspan='6'>לא נמצאו רשומות.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      //PAGING START
                      $g_nCountRecords++;
                      if ($g_nCountRecords > HOMECOOP_RECORDS_PER_PAGE) {
                        //do not display the row over the page reocrds - it's for checking if there is a next page
                        break;
                      }
                      //PAGING END
                      
                      echo "<tr>";
                      
                      echo "<td><a href='orderitems.php?id=" , $recTable["OrderID"] , "' >" ,  
                        htmlspecialchars($recTable["sCoopOrder"]) , "</a></td>";
                      
                      //delivery
                      $oDate = new HtmlDateString($recTable["dDelivery"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                      echo "<td>";
                      $oDate->EchoHtml();
                      echo "</td>";
                      
                      //status
                      $oStatus = new ActiveCoopOrderStatus(new DateTime($recTable["dEnd"], $g_oTimeZone),  new DateTime($recTable["dDelivery"], $g_oTimeZone), 
                              $recTable["nStatus"]);
                      echo "<td>" , $oStatus->StatusName , "</td>";
                      unset($oStatus);
                      
                      //place date                      
                      $oDate = new HtmlDateString($recTable["dCreated"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                      echo "<td>";
                      $oDate->EchoHtml();
                      echo "</td>";
                                           
                      //pickup
                      echo '<td>' , htmlspecialchars($recTable["sPickupLocation"]) , '</td>';
                      
                      //total
                      echo '<td>' , $recTable["OrderCoopTotal"] , '</td>';

                      echo '</tr>';

                      $recTable = $oData->fetch();
                  }
                }
    ?>
            </table>
           <?php
          //PAGING
          $g_BasePageUrl = 'orders.php?id=' . $oData->MemberID . '&name=' . urlencode($oData->MemberName);

          include_once 'control/paging.php';
          ?>
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

