<?php

include_once 'settings.php';
include_once 'authenticate.php';
include_once 'facet.php';

$oRecord = new Order;
$oPickupLocs = NULL;
$oMembers = NULL;
$recPickupLocs = NULL;
$arrMembers = NULL;
$oTabInfo = NULL;
$oOrderTabInfo = NULL;
$oPLTabInfo = NULL;
$arrCOContacts = NULL;
$arrPLContacts = NULL;
$arrPaymentMethods = NULL;
$bHasCoordPermission = FALSE;

try
{  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->ID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    $oRecord->CopyOriginalDataWhenUnsaved();
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      //collect data
      $sCtl = HtmlSelectPDO::PREFIX . 'PickupLocationKeyID';
      if ( isset( $_POST[$sCtl] ))
        $oRecord->PickupLocationID = intval($_POST[$sCtl]);

      $sCtl = HtmlSelectArray::PREFIX . 'MemberID';
      if ( isset( $_POST[$sCtl] ))
        $oRecord->MemberID = intval($_POST[$sCtl]);
      else if ($oRecord->ID == 0)
        $oRecord->MemberID = $g_oMemberSession->MemberID;

      if ( isset( $_POST['txtPercentOverBalance'] ) && !empty($_POST['txtPercentOverBalance']))
        $oRecord->PercentOverBalance = 0 + trim($_POST['txtPercentOverBalance']);

      $sCtl = HtmlSelectPDO::PREFIX . 'PaymentMethodKeyID';
      if ( isset( $_POST[$sCtl] ))
        $oRecord->PaymentMethodID = intval($_POST[$sCtl]);

      $oRecord->MemberComments = NULL;
      if ( isset( $_POST['txtMemberComments'] ))
      {
        $sMemberComments = trim($_POST['txtMemberComments']);
        if (!empty($sMemberComments))
          $oRecord->MemberComments = $sMemberComments;
      }

      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          $bSuccess = FALSE; //trigger var declare
          if ($oRecord->ID > 0)
            $bSuccess = $oRecord->Edit();
          else
          {
            $bSuccess = $oRecord->Add();
            if ($bSuccess)
            {
              //when just entering order items - show all
              RedirectPage::To( $g_sRootRelativePath . 'orderitems.php?mode=' . OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL .  '&id=' . $oRecord->ID );
              return;
            }
          }
          
          if ( $bSuccess )
              $g_oError->PushError('Record saved successfully.', 'ok');
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->PushError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          else
            $g_oError->PushError('Data was not saved.');
          break;
        case Order::POST_ACTION_MEMBER_CHANGE:
          //load new member and coop order data
          $oRecord->LoadCoopOrder($oRecord->CoopOrderID, $oRecord->MemberID);
          break;
        case SQLBase::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
              $g_oError->AddError('The record was deleted successfully.', 'ok');
          else
              $g_oError->AddError('The record was not deleted.');
          
          break;
      }
    }
  }
  else 
  {
    if (isset($_GET['id']))
    {
      if(!$oRecord->LoadRecord( intval($_GET['id'])) )
      {
          RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
          exit;
      }
    }
    else if (isset($_GET['coid']))
    {
      $oRecord->LoadCoopOrder(intval($_GET['coid']), $g_oMemberSession->MemberID);
    }
  }
  
  switch($oRecord->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $bHasCoordPermission = $oRecord->HasPermission(SQLBase::PERMISSION_COORD);
    
  if ( $oRecord->CanModify )
  {
    $oPickupLocs = new CoopOrderPickupLocations;
    if ($bHasCoordPermission)
      $recPickupLocs = $oPickupLocs->LoadList($oRecord->CoopOrderID, $oRecord->MemberID);
    else
    {
      //fix facet to include current pickup location
      if ($oRecord->ID > 0 && $oRecord->PickupLocationID > 0 && !isset($g_aMemberPickupLocationIDs[$oRecord->PickupLocationID]))
        $g_aMemberPickupLocationIDs[$oRecord->PickupLocationID] = $oRecord->PickupLocationID;
      
      $recPickupLocs = $oPickupLocs->LoadFacet($oRecord->CoopOrderID, $g_oMemberSession->MemberID);
    }
    
    if ( $bHasCoordPermission )
    {
      $arrPaymentMethods = $oRecord->GetPaymentMethods();
      $oMembers = new Members;
      $arrMembers = $oMembers->GetMembersListForOrder($oRecord->CoopOrderID, $oRecord->ID);
      //if there are no members, can't modify
      if (!is_array($arrMembers) || count($arrMembers) == 0)
      {
        $oRecord->CanModify = FALSE;
        $g_oError->AddError('There are no members left to add to this cooperative order. All members are already ordering.');
      }
    }
  }

  $oTabInfo = new CoopOrderTabInfo;
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->ID = $oRecord->CoopOrderID;
  if ( $oTabInfo->CheckAccess() )
  {
    $oTabInfo->Page = CoopOrderTabInfo::PAGE_ORDERS;
    $oTabInfo->CoopOrderTitle = $oRecord->CoopOrderName;
    $oTabInfo->IsSubPage = TRUE;
    $oTabInfo->Status = $oRecord->Status;
    $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  }
  
  $oPLTabInfo = new CoopOrderPickupLocationTabInfo( $oRecord->CoopOrderID, $oRecord->PickupLocationID, $oRecord->PickupLocationName, 
        CoopOrderPickupLocationTabInfo::PAGE_ORDERS );
  $oPLTabInfo->CoordinatingGroupID = $oRecord->PickupLocationGroupID;
  $oPLTabInfo->IsSubPage = TRUE;

  $oOrderTabInfo = new OrderTabInfo($oRecord->ID, OrderTabInfo::PAGE_ENTRY, $oRecord->CoopTotal, $oRecord->OrderCoopFee);
  $oOrderTabInfo->StatusObj = $oRecord->StatusObj;
  $oPercent = new CoopOrderCapacity($oRecord->MaxBurden, $oRecord->TotalBurden, $oRecord->MaxCoopTotal, $oRecord->CoopOrderCoopTotal,
      $oRecord->CoopOrderMaxStorageBurden, $oRecord->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oOrderTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);

  $oRecord->BuildPageTitle();
  $oOrderTabInfo->MainTabName = $oRecord->PageTitleSuffix;

  //get contacts
  $oRecord->GetContacts($arrCOContacts, $arrPLContacts);
  
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
<title>Enter Your Cooperative Name: <?php echo $oRecord->PageTitle;  ?></title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" >
function Delete()
{
  if (confirm(decodeXml('Please confirm or cancel the delete operation')))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function MemberChange()
{
  <?php
  if ($oRecord->ID > 0) { ?>
    if (!confirm(decodeXml('Attention: you have chosen to move this order from one member to another. To complete the operation confirm this message box and save the order')))
    {
      document.getElementById("selMemberID").value = <?php echo $oRecord->MemberID; ?>;
      return;
    }
  <?php } ?>
  
  document.getElementById("hidPostAction").value = <?php echo Order::POST_ACTION_MEMBER_CHANGE; ?>;
  document.frmMain.submit();
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->ID; ?>" />
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="948"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename"><?php echo $oRecord->PageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr><td>
              <table cellspacing="0" cellpadding="0" width="100%">
              <tr>
                <td><?php include_once 'control/coopordertab.php'; ?></td>
              </tr>
              <tr>
                <td><?php include_once 'control/copickuploctab.php'; ?></td>
              </tr>
              <tr>
                <td><?php include_once 'control/ordertab.php'; ?></td>
              </tr>
              <tr>
                <td><?php include_once 'control/error/ctlError.php'; ?></td>
              </tr>
              <tr>
                <td>
                  <button type="submit" class="order" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                <?php if ($g_oError->HadError || !$oRecord->CanModify) echo ' disabled="disabled" '; ?>><?php
                if ($oRecord->ID > 0)
                  echo 'Save Order Header';
                else
                  echo 'Create Order';  
                ?></button>&nbsp;<?php 
                  if ($oRecord->CanModify && $oRecord->ID > 0 && $oRecord->HasPermission(SQLBase::PERMISSION_DELETE)) 
                  {
                        echo '<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" ';
                        if ($g_oError->HadError) 
                          echo ' disabled="disabled" '; 
                        echo '>' , sprintf('Delete %s', $oRecord->PageTitleSuffix ) , '</button>';
                  } 
                ?></td>
              </tr>
              <tr><td>
              <table cellspacing="0" cellpadding="2" width="100%">
                
                <tr>
                  <?php
                  if ($oRecord->CanModify)
                  {
                    $selPickupLoc = new HtmlSelectPDO('Location Name', $recPickupLocs, $oPickupLocs, 
                            $oRecord->PickupLocationID, 'sPickupLocation', 'PickupLocationKeyID');
                    $selPickupLoc->Required = TRUE;
                    $selPickupLoc->SelectFirstIfOneOption = TRUE;
                    $selPickupLoc->EchoHtml();
                  }
                  else
                  {
                    //show pickup location
                    $txtPickupLocation = new HtmlTextLabel('Location Name', 'PickupLocation', $oRecord->PickupLocationName);
                    $txtPickupLocation->EchoHtml();
                  }

                ?>
                <td width="100%"></td>
                </tr>
                <?php if ($oRecord->ID > 0) { ?>
                <tr>
                  <?php                                       
                    $lblPickupLocationAddress = 
                      new HtmlTextLabel('Address', 'txtPickupLocationAddress', $oRecord->PickupLocationAddress);
                    $lblPickupLocationAddress->EchoHtml();
                    unset($lblPickupLocationAddress);
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                                       
                    $lblPickupLocationComments = 
                      new HtmlTextLabel('Pickup Instructions', 'txtPickupLocationComments', $oRecord->PublishedComments);
                    $lblPickupLocationComments->EchoHtml();
                    unset($lblPickupLocationComments);
                  ?>
                  <td></td>
                </tr>
                <?php } 
                
                  if ($oRecord->CanModify && $bHasCoordPermission && 
                      $oRecord->HasPermission(Order::PERMISSION_SET_MAX_ORDER) )
                  {
                    echo '<tr>';
                   
                    $txtBalance = new HtmlTextLabel('Balance', 'txtBalance', $oRecord->Balance);
                    $txtBalance->EchoHtml();
                    unset($txtBalance);
                 
                    echo '<td></td>';
                    echo '</tr>';
                
                    echo '<tr>';
                
                    $selPaymentMethod = new HtmlSelectArray('PaymentMethodKeyID', 'Payment Method', $arrPaymentMethods,
                          $oRecord->PaymentMethodID);
                    $selPaymentMethod->Required = TRUE;
                    $selPaymentMethod->ReadOnly = !$oRecord->CanModify;
                    $selPaymentMethod->EchoHtml();
                    unset($selPaymentMethod);

                    echo '<td></td>';
                    echo '</tr>';

                    echo '<tr>';
                    $txtPOBalance = new HtmlTextEditNumeric('% Over Balance', 'txtPercentOverBalance', 
                            $oRecord->PercentOverBalance);
                    $txtPOBalance->ReadOnly = !$oRecord->CanModify;
                    $txtPOBalance->EchoHtml();
                    unset($txtPOBalance);
                  
                    echo '<td><a class="tooltiphelp" href="#" >‏?‏<span style="width: 200px;">The percentage in which a member&#x27;s order can exceed hir balance. This rule is being applied only when the member&#x27;s payment method allows a percentage over balance.</span></a></td>';
                    echo '</tr>';

                  }
                ?>
                <tr>
                  <?php
                    $lblMaxOrder = new HtmlTextLabel('Max. Order', 'lblMaxOrder',$oRecord->MaxOrder);
                    $lblMaxOrder->EchoHtml();
                    unset($lblMaxOrder);
                  ?>
                  <td></td>
                </tr>

                <tr>
                  <td><label for="txtMemberComments">Comments‏:‏</label></td>
                  <?php 
                    $txtMemberComments = new HtmlTextEdit('txtMemberComments', NULL , 
                            HtmlTextEdit::TEXTAREA, $oRecord->MemberComments);
                    $txtMemberComments->ReadOnly = !$oRecord->CanModify;
                    $txtMemberComments->MaxLength = Order::MAX_LENGTH_MEMBER_COMMENTS;
                    $txtMemberComments->EchoEditPartHtml();
                  ?>
                  <td></td>
                </tr>
                <?php 
                  if ($bHasCoordPermission && $oRecord->CanModify)
                  {
                    echo '<tr>';
                    
                    $sMemberFieldLabel = NULL;
                    
                    //change the label of the member field, according to whether a new order or existing one
                    if ($oRecord->ID > 0)
                      $sMemberFieldLabel = 'Move order to';
                    else
                      $sMemberFieldLabel = 'Member';
                    
                    //select member
                    $selMember = new HtmlSelectArray('MemberID', $sMemberFieldLabel, $arrMembers, $oRecord->MemberID);
                    $selMember->OnChange = "JavaScript:MemberChange();";
                    $selMember->Required = TRUE;
                    $selMember->EmptyText = NULL; //remove empty row
                    $selMember->EchoHtml();
                    
                    echo '<td><span id="spLoginName" name="spLoginName">';
                    if ($selMember->ValueFound) //if member not found, don't show incorrect data
                        echo sprintf('Login name: %s', htmlspecialchars( $oRecord->LoginName ));
                    echo '</span></td>';
                    
                    echo '</tr>';
                    
                    echo '<tr>',
                          '<td><label>Email address‏:‏</label></td>';
                    
                    echo '<td>';
                    
                    if ($selMember->ValueFound) //if member not found, don't show incorrect data
                    {
                      echo htmlspecialchars($oRecord->EMail);
                      if ( $oRecord->EMail2 != NULL )
                        echo ', ', htmlspecialchars($oRecord->EMail2);
                      if ( $oRecord->EMail3 != NULL )
                        echo ', ', htmlspecialchars($oRecord->EMail3);
                      if ( $oRecord->EMail4 != NULL )
                        echo ', ', htmlspecialchars($oRecord->EMail4);
                    }
                     
                    echo '</td>';
                   
                    echo '<td></td>';
                    
                    echo '</tr>';
                  }
                ?>
                <?php if ($oRecord->ID > 0) { 
                  
                  $sContacts = '';
                  
                  if ($arrCOContacts != NULL)
                  {
                    
                    foreach($arrCOContacts as $arrCOValues)
                    {
                      $sContacts .= sprintf('%1$s, %2$s ', $arrCOValues["sName"], $arrCOValues["sEMail"]);
                    }
                    
                    if ($sContacts != '')
                    {
                      $lblCOContacts = new HtmlTextLabel('Order Coordinator(s)','lblCOContacts', $sContacts);
                      echo '<tr>';
                      $lblCOContacts->EchoHtml(); 
                      echo '<td></td></tr>';
                    }
                  }
                  
                  
                  if ($arrPLContacts != NULL)
                  {
                    $sContacts = '';
                    foreach($arrPLContacts as $arrPLValues)
                    {
                      $sContacts .= sprintf('%1$s, %2$s ', $arrPLValues["sName"], $arrPLValues["sEMail"]);
                      
                    }
                    if ($sContacts != '')
                    {
                      $lblPLContacts = new HtmlTextLabel('Pickup Location Coordinator(s)','lblPLContacts', $sContacts);
                      echo '<tr>';
                      $lblPLContacts->EchoHtml();
                      echo '<td></td></tr>';
                    }
                  }
                  
                  ?>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                  <td colspan="3">
                    <?php
                        echo(sprintf('Last update by %1$s on %2$s at %3$s',$oRecord->ModifiedByMemberName,
                                $oRecord->DateModified->format('n.j.Y'),
                                $oRecord->DateModified->format('g:i A')
                                ));
                    ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="3">
                    <?php
                        echo(sprintf('Created by %1$s on %2$s at %3$s',$oRecord->CreatedByMemberName,
                                $oRecord->DateCreated->format('n.j.Y'),
                                $oRecord->DateCreated->format('g:i A')
                                ));
                    ?>
                  </td>
                </tr>
                <?php } ?>
                </table>
                </td></tr></table>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    <tr>
      <td>
        <?php include_once 'control/footer.php'; ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>