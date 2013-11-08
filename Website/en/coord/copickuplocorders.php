<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderPickupLocationOrders;
$recTable = NULL;
$oTabInfo = new CoopOrderTabInfo;
$oPLTabInfo = NULL;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PICKUP;
$oTabInfo->IsSubPage = TRUE;
$oCoopOrderCapacity = NULL;
$sPageTitle = '';
$mMaxOrder = 0;

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidOriginalData'] ) )
      $oData->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    $oData->PreserveFields();
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SORT:
          if ( isset( $_POST['hidSortField'] ) && !empty($_POST['hidSortField']) )
            $oData->SwitchSort(intval($_POST['hidSortField']));
          break;
      }
    }
  }
  else //GET
  {
    if (isset($_GET['coid']))
      $oData->CoopOrderID = intval($_GET['coid']);

    if (isset($_GET['plid']))
      $oData->PickupLocationID = intval($_GET['plid']);
  }
    
  $recTable = $oData->LoadData();

  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ($oData->CoopOrderID <= 0 || $oData->PickupLocationID <= 0)
  {
     RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
     exit;
  }

  $sPageTitle = sprintf('Members Orders of Pickup Location %s', $oData->PickupLocationName);
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal,
      $oData->CoopOrderMaxStorageBurden, $oData->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);

  $oPLTabInfo = new CoopOrderPickupLocationTabInfo($oData->CoopOrderID, $oData->PickupLocationID, $oData->PickupLocationName, 
          CoopOrderPickupLocationTabInfo::PAGE_ORDERS);
  $oPLTabInfo->CoordinatingGroupID = $oData->PickupLocationCoordGroupID;

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
<script type="text/javascript" >
function Sort(nField)
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SORT; ?>;
  document.getElementById("hidSortField").value = nField;
  document.frmMain.submit();
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidSortField" name="hidSortField" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="6"><?php include_once '../control/coopordertab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="6"><?php include_once '../control/copickuploctab.php'; ?></td>
            </tr>
            <tr>
              <td colspan="6"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo CoopOrderPickupLocationOrders::SORT_FIELD_MEMBER_NAME; ?>);">Member</span></td>
              <td class="columntitlelong">Email address</td>
              <td class="columntitletiny">Total</td>
              <td class="columntitletiny">Balance</td>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo CoopOrderPickupLocationOrders::SORT_FIELD_CREATE_DATE; ?>);">Order Date</span></td>
              <td class="columntitlenowidth">Comments</td>
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
                      
                      //member name
                      echo '<td><a class="tooltiplink" href="../orderitems.php?id=' , $recTable["OrderID"] , '" >' ,  
                              htmlspecialchars( $recTable["MemberName"] ) ,  '<span>',
                              sprintf('Login name: %s', htmlspecialchars( $recTable["sLoginName"] )),
                              '</span></a></td>';
                      
                      //Emails
                      echo '<td>' , htmlspecialchars($recTable["sEMail"]);
                      if ( $recTable["sEMail2"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail2"]);
                      if ( $recTable["sEMail3"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail3"]);
                      if ( $recTable["sEMail4"] != NULL )
                        echo ', ', htmlspecialchars($recTable["sEMail4"]);
                      
                      echo '</td>';
                      
                      //order total
                      echo '<td';
                      
                      $mMaxOrder = Member::CalculateMaxOrder(  $recTable["PaymentMethodKeyID"],
                                                  $recTable["mBalance"],
                                                  $recTable["fPercentOverBalance"] );
                      
                      if ( $mMaxOrder != NULL && $recTable["OrderCoopTotal"] > $mMaxOrder )
                        echo ' class="alarmingdata" ';
                      
                      echo '>' , $recTable["OrderCoopTotal"] , '</td>';
                      
                      //balance
                      if ($mMaxOrder != NULL && $recTable["mBalance"] != NULL && $mMaxOrder != $recTable["mBalance"])
                        echo '<td><a href="#" class="tooltip">' , $recTable["mBalance"] , '<span>', 
                              sprintf('Max. Order: %s',$mMaxOrder), '</span></a></td>';
                      else
                        echo '<td>' , $recTable["mBalance"] , '</td>';
                                         
                      //place date    
                      echo "<td><span dir='ltr'>";
                      
                      $dDate = new DateTime($recTable["dCreated"], $g_oTimeZone);
                      //if current year, take current year format
                      if (($dDate->format('Y')+0) == HtmlDateString::GetThisYear())
                        echo $dDate->format('n.j g:i A');
                      else
                        echo $dDate->format('n.j.Y g:i A');
                      
                      echo "</span></td>";
                      
                      //comments
                      echo '<td>' , htmlspecialchars($recTable["sMemberComments"]);
                      
                      if ($recTable["bHasItemComments"])
                      {
                        echo '&nbsp;<a href="../orderitems.php?id=' , $recTable["OrderID"] , 
                                '" class="tooltiphelp" >More...<span>';
                        $oOrderItems = new OrderItems;
                        $rec = $oOrderItems->GetComments($recTable["OrderID"]);
                        while($rec)
                        {
                          echo $rec["sProduct"] , ':&nbsp;' , $rec["sMemberComments"] , '<br/>';
                          $rec = $oOrderItems->fetch();
                        }
                        echo '</span></a>'; 
                      }
                      
                      echo '</td>';
                      
                      
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
        include_once '../control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>