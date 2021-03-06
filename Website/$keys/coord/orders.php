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
$sPageTitle = '<!$TAB_ORDER_ORDERS$!>';
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
            $g_oError->AddError('<!$JOIN_PRODUCTS_SUCCESS$!>', 'ok');
            $bOrdersChanged = TRUE;
          }
          else
            $g_oError->AddError('<!$JOIN_PRODUCTS_NO_UPDATE$!>', 'warning');
          break;
        case Orders::POST_ACTION_UNJOIN_PRODUCTS:
          $oData->PreserveSort();
          $oCoopOrderJoinProducts = new CoopOrderJoinProducts($oData->CoopOrderID);
          $oCoopOrderJoinProducts->CoordinatingGroupID = $oData->OriginalGroupID;
          $oCoopOrderJoinProducts->Unjoin();
          $arrOrdersUpdated = $oCoopOrderJoinProducts->OrdersUpdated;
          if (is_array($arrOrdersUpdated) && count($arrOrdersUpdated)>0)
          {
            $g_oError->AddError('<!$UNJOIN_PRODUCTS_SUCCESS$!>', 'ok');
            $bOrdersChanged = TRUE;
          }
          else
            $g_oError->AddError('<!$UNJOIN_PRODUCTS_NO_UPDATE$!>', 'warning');
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

  $sPageTitle = $oData->Name . '<!$PAGE_TITLE_SEPARATOR$!><!$TAB_ORDER_ORDERS$!>';
  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal,
      $oData->CoopOrderMaxStorageBurden, $oData->CoopOrderStorageBurden);
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
<?php include_once '../control/headtags.php'; ?>
<title><!$COOPERATIVE_NAME$!>: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function JoinProducts()
{
  <?php
   if ($oTabInfo->StatusObj->Status == ActiveCoopOrderStatus::Open)
   {
     ?>
     if (!confirm(decodeXml('<!$COOP_ORDER_STILL_OPEN_ARE_YOU_SURE_JOIN_PRODUCTS$!>')))
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
        <td class="fullwidth"><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="7"><?php if ($oTabInfo != NULL) { include_once '../control/coopordertab.php'; } ?></td>
            </tr>
            <tr>
              <td colspan="7"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td colspan="7">                
                <button type="button" <?php if ($bReadOnly) echo ' disabled '; ?>  title="<!$TOOLTIP_JOIN_COOP_ORDER_PRODUCTS$!>"
                        onclick="JavaScript:JoinProducts();" id="btnJoinProducts" name="btnJoinProducts" ><!$BTN_JOIN_COOP_ORDER_PRODUCTS$!></button>&nbsp;
                <?php
                
                if ($oData->HasJoinedProducts)
                {
                  echo '<button type="button" ';
                  if ($bReadOnly) echo ' disabled ';
                  
                  echo ' onclick="JavaScript:UnjoinProducts();" id="btnUnjoinProducts" title="<!$TOOLTIP_UNJOIN_COOP_ORDER_PRODUCTS$!>" '. 
                        ' name="btnUnjoinProducts" ><!$BTN_UNJOIN_COOP_ORDER_PRODUCTS$!></button>';
                }              
                ?>
              </td>
            </tr>
            <tr>
              <td colspan="7"><?php if ($bReadOnly)
              echo '<!$COOP_ORDER_CANNOT_BE_UPDATED_AT_THIS_STATUS$!>';
              else
                echo '<a href="../order.php?coid=' , $oData->CoopOrderID , '" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a>&nbsp;';
                ?>
              </td>
            </tr>
            <tr>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo Orders::SORT_FIELD_MEMBER_NAME; ?>);"><!$FIELD_MEMBER$!></span></td>
              <td class="columntitlelong"><!$FIELD_EMAIL$!></td>
              <td class="columntitle"><!$FIELD_PICKUP_LOCATION_NAME$!></td>
              <td class="columntitletiny"><!$FIELD_ORDER_COOP_TOTAL_SHORT$!></td>
              <td class="columntitletiny"><!$FIELD_BALANCE$!></td>
              <td class="columntitle"><span class="link" onclick="JavaScript:Sort(<?php echo Orders::SORT_FIELD_CREATE_DATE; ?>);"><!$FIELD_ORDER_CREATED$!></span></td>
              <td class="columntitlenowidth"><!$FIELD_MEMBER_COMMENTS$!></td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='7'>&nbsp;</td></tr><tr><td align='center' colspan='7'><!$NO_RECORD_FOUND$!></td></tr>";
                }
                else
                {
                  $sTooltipBalanceID = '';
                  $sThisYear = HtmlDateString::GetThisYear();
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
                              sprintf('<!$TOOLTIP_LOGIN_NAME$!>', htmlspecialchars( $recTable["sLoginName"] )),
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
                      {
                        $sTooltipBalanceID = 'balancehlp_' . $recTable["OrderID"];
                        echo '<td><a id="', $sTooltipBalanceID, '" name="', $sTooltipBalanceID, '" href="#', $sTooltipBalanceID, '" class="tooltip">' , $recTable["mBalance"] , '<span>', 
                              sprintf('<!$TOOLTIP_MAX_ORDER$!>',$mMaxOrder), '</span></a></td>';
                      }
                      else
                        echo '<td>' , $recTable["mBalance"] , '</td>';
                      
                      
                      //place date    
                      echo "<td><span dir='ltr'>";
                      
                      $dDate = new DateTime($recTable["dCreated"], $g_oTimeZone);
                      //if current year, take current year format
                      if (($dDate->format('Y')+0) == $sThisYear)
                        echo $dDate->format('<!$FULL_DATE_FORMAT_CURRENT_YEAR$!>');
                      else
                        echo $dDate->format('<!$FULL_DATE_FORMAT_ANY_YEAR$!>');
                      
                      echo "</span></td>";
                      
                      //comments
                      echo '<td>' , htmlspecialchars($recTable["sMemberComments"]);
                      
                      if ($recTable["bHasItemComments"])
                      {
                        echo '&nbsp;<a href="../orderitems.php?id=' , $recTable["OrderID"] , 
                                '" class="tooltiphelp" ><!$ORDERS_ITEM_COMMENTS_LINK_TEXT$!><span class="helpspan">';
                        $oOrderItems = new OrderItems;
                        $rec = $oOrderItems->GetComments($recTable["OrderID"]);
                        while($rec)
                        {
                          echo '<div>', $rec["sProduct"] , ':&nbsp;' , htmlspecialchars($rec["sMemberComments"]) , '</div>';
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

