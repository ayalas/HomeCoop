<?php

include_once 'settings.php';
include_once 'authenticate.php';

$oData = new MemberOrders;
$recTable = NULL;
$sPageTitle = 'My Orders';

try
{
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'GET' )
  {
    $oData->MemberID = $g_oMemberSession->MemberID;
  }
  else if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case MemberOrders::POST_ACTION_SWITCH_MEMBER:
          $sCtl = HtmlSelectArray::PREFIX . 'Member';
          if ( isset( $_POST[$sCtl] ))
            $oData->MemberID = intval($_POST[$sCtl]);
          if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
            $oData->MemberName = $_POST['hidPostValue'];
         break;
      }
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
    $sPageTitle = sprintf('%s&#x27;s Orders', htmlspecialchars ($oData->MemberName));
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
<link rel="stylesheet" type="text/css" href="style/main.css" />
<title>Enter Your Cooperative Name: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" >
function SwitchMember()
{
  var ctl = document.getElementById("<?php echo HtmlSelectArray::PREFIX , 'Member';?>");
  document.getElementById("hidPostValue").value = ctl.options[ctl.selectedIndex].text;
  document.getElementById("hidPostAction").value = <?php echo MemberOrders::POST_ACTION_SWITCH_MEMBER; ?>;
  document.frmMain.submit();
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
        <td width="948"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
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
                $selMembers = new HtmlSelectArray('Member', 'Member', $arrMembers, $oData->MemberID);
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
              <td class="columntitlelong">Order Title</td>
              <td class="columntitleshort">Delivery</td>
              <td class="columntitleshort">Status</td>
              <td class="columntitleshort">Order Date</td>
              <td class="columntitle">Location Name</td>
              <td class="columntitlenowidth">Total</td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='6'>&nbsp;</td></tr><tr><td align='center' colspan='6'>No records.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
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

