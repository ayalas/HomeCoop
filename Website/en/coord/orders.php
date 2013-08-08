<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new Orders;
$recTable = NULL;
$oTabInfo = NULL;
$bReadOnly = FALSE;
$oCoopOrderJoinProducts = NULL;
$arrOrdersUpdated = NULL;
$bOrdersChanged = FALSE;
$sPageTitle = 'Member Orders';
$mMaxOrder = 0;

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {   
    if ( isset( $_POST['hidOriginalData'] ) )
      $oData->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oData->CoopOrderID = intval($_POST['hidPostValue']);
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SORT:
          if ( isset( $_POST['hidSortField'] ) && !empty($_POST['hidSortField']) )
            $oData->SwitchSort(intval($_POST['hidSortField']));
          break;
        case Orders::POST_ACTION_JOIN_PRODUCTS:
          $oData->PreserveSort();
          $oCoopOrderJoinProducts = new CoopOrderJoinProducts($oData->CoopOrderID);
          $oCoopOrderJoinProducts->CoordinatingGroupID = $oData->OriginalGroupID;
          $oCoopOrderJoinProducts->Join();
          $arrOrdersUpdated = $oCoopOrderJoinProducts->OrdersUpdated;
          if (is_array($arrOrdersUpdated) && count($arrOrdersUpdated)>0)
          {
            $g_oError->AddError('Products items were joined successfully into larger cost-saving packages. The following orders, marked in color, were modified.');
            $bOrdersChanged = TRUE;
          }
          else
            $g_oError->AddError('No products required joining.');
          break;
        case Orders::POST_ACTION_UNJOIN_PRODUCTS:
          $oData->PreserveSort();
          $oCoopOrderJoinProducts = new CoopOrderJoinProducts($oData->CoopOrderID);
          $oCoopOrderJoinProducts->CoordinatingGroupID = $oData->OriginalGroupID;
          $oCoopOrderJoinProducts->Unjoin();
          $arrOrdersUpdated = $oCoopOrderJoinProducts->OrdersUpdated;
          if (is_array($arrOrdersUpdated) && count($arrOrdersUpdated)>0)
          {
            $g_oError->AddError('Products items were unjoined successfully into smaller more costly packages. The following orders, marked in color, were modified.');
            $bOrdersChanged = TRUE;
          }
          else
            $g_oError->AddError('No products required unjoining.');
          break;
      }
    }
  }
  else if (isset($_GET['coid']))
    $oData->CoopOrderID = intval($_GET['coid']);
  
  $recTable = $oData->LoadDataByCoopOrder();
  $oTabInfo = new CoopOrderTabInfo;
  $oTabInfo->Page = CoopOrderTabInfo::PAGE_ORDERS;
  
  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $sPageTitle = $oData->Name . ' - Member Orders';
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  $bReadOnly = ($oData->Status != CoopOrder::STATUS_ACTIVE 
          && $oData->Status != CoopOrder::STATUS_DRAFT
          && $oData->Status != CoopOrder::STATUS_LOCKED );

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
<title>Enter Your Cooperative Name: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function JoinProducts()
{
  <?php
   if ($oTabInfo->StatusObj->Status == ActiveCoopOrderStatus::Open)
   {
     ?>
     if (!confirm(decodeXml('The cooperative order is still open for changes, according to its dates. At this stage it is not recommended to join products, as this action will lock reducing quantities for the products that were joined, and there may be a need to run it again, if quantities have increased. Proceed?')))
       return;
     <?php
   }      
  ?>
  document.getElementById("hidPostAction").value = <?php echo Orders::POST_ACTION_JOIN_PRODUCTS; ?>;
  document.frmMain.submit();
}
function UnjoinProducts()
{
  document.getElementById("hidPostAction").value = <?php echo Orders::POST_ACTION_UNJOIN_PRODUCTS; ?>;
  document.frmMain.submit();
}
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
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oData->CoopOrderID; ?>" />
<input type="hidden" id="hidSortField" name="hidSortField" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
            <td width="780" height="100%" >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="7"><?php if ($oTabInfo != NULL) { include_once '../control/coopordertab.php'; } ?></td>
            </tr>
            <tr>
              <td colspan="7"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td colspan="7">                
                <button type="button" <?php if ($bReadOnly) echo ' disabled '; ?>  title="Join products that the field Join to Product is defined for them, to save costs by ordering larger packages"
                        onclick="JavaScript:JoinProducts();" id="btnJoinProducts" name="btnJoinProducts" >Join Products</button>&nbsp;
                <?php
                
                if ($oData->HasJoinedProducts)
                {
                  echo '<button type="button" ';
                  if ($bReadOnly) echo ' disabled ';
                  
                  echo ' onclick="JavaScript:UnjoinProducts();" id="btnUnjoinProducts" title="Rollback join products operation to allow reducing quantities for them" '. 
                        ' name="btnUnjoinProducts" >Unjoin Products</button>';
                }              
                ?>
              </td>
            </tr>
            <tr>
              <td colspan="7"><?php if ($bReadOnly)
              echo 'Cooperative order cannot be updated at its current status';
              else
                echo '<a href="../order.php?coid=' , $oData->CoopOrderID , '" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a>&nbsp;';
                ?>
              </td>
            </tr>
            <tr>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo Orders::SORT_FIELD_MEMBER_NAME; ?>);">Member</span></td>
              <td class="columntitlelong">Email address</td>
              <td class="columntitle">Location Name</td>
              <td class="columntitletiny">Total</td>
              <td class="columntitletiny">Balance</td>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo Orders::SORT_FIELD_CREATE_DATE; ?>);">Order Date</span></td>
              <td class="columntitlenowidth">Comments</td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='7'>&nbsp;</td></tr><tr><td align='center' colspan='7'>No records.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                    if ($bOrdersChanged && array_key_exists($recTable["OrderID"], $arrOrdersUpdated))
                    {
                      echo '<tr class ="changedrow" >';
                    }
                    else
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
                      
                      //pickup location
                      echo '<td>' , htmlspecialchars($recTable["sPickupLocation"]) , '</td>';
                      
                      //total
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
                          echo $rec["sProduct"] , ':&nbsp;' , htmlspecialchars($rec["sMemberComments"]) , '<br/>';
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

