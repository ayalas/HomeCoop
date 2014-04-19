<?php

include_once 'settings.php';
include_once 'authenticate.php';

$oRecord = new Order;
$oTable = new OrderItems;
$arrItems = NULL;
$oTabInfo = new CoopOrderTabInfo;
$oOrderTabInfo = NULL;
$oPLTabInfo = NULL;

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->ID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oTable->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    $oRecord->SuppressMessages = TRUE; //so messages won't displayed twice
    //get order for permissions check
    if (!$oRecord->LoadRecord( $oRecord->ID ) )
    {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
    }
    
    $oTable->SetOrder($oRecord);
    
    if ( isset( $_POST['selProductsView'] ) )
      $oTable->ProductsViewMode = intval($_POST['selProductsView']);

    if (!$oRecord->CanModify)
      $oTable->ProductsViewMode = OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY;
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          $nMode = $oTable->ProductsViewMode; //get products view mode to see if changed (in first save case) and need to reload items
          $bSuccess = $oTable->Save();  
          
          //always reload order record after save (but suppress messages)
          $oRecord->LoadRecord( $oRecord->ID );
          
          if ( $bSuccess )
          {
            $g_oError->PushError('המוצרים המוזמנים נשמרו בהצלחה.', 'ok');
            //always reload table to get latest storage areas values
            $oTable->LoadTable();
          }
        break;
        case OrderItems::POST_ACTION_SWITCH_VIEW_MODE:
          $oTable->LoadTable();
        break;
      }
    }
    
  }
  else 
  {
    if ( isset( $_GET['id'] ) )
      $oRecord->ID = intval($_GET['id']);
    
    if (!$oRecord->LoadRecord( $oRecord->ID ) )
    {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
    }
    
    if ( isset( $_GET['mode'] ) )
    {
      $oTable->ProductsViewMode = intval($_GET['mode']);
      if ($oTable->ProductsViewMode != OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY &&
              $oTable->ProductsViewMode != OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL)
        $oTable->ProductsViewMode = OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL;
    }
    
    //even if mode is pre-set, if no products - set mode to all
    if ($oRecord->CoopTotal == 0)
      $oTable->ProductsViewMode = OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL;
    
    $oTable->SetOrder($oRecord);
    $oTable->LoadTable();
  }
  
  switch($oTable->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $arrItems = $oTable->OrderItems;
  
  $oPLTabInfo = new CoopOrderPickupLocationTabInfo( $oRecord->CoopOrderID, $oRecord->PickupLocationID, $oRecord->PickupLocationName, 
        CoopOrderPickupLocationTabInfo::PAGE_ORDERS );
  $oPLTabInfo->CoordinatingGroupID = $oRecord->PickupLocationGroupID;
  $oPLTabInfo->IsSubPage = TRUE;
  
  $oOrderTabInfo = new OrderTabInfo($oRecord->ID, OrderTabInfo::PAGE_ITEMS, $oRecord->CoopTotal, $oRecord->OrderCoopFee);
  $oOrderTabInfo->StatusObj = $oRecord->StatusObj;
  $oPercent = new CoopOrderCapacity($oRecord->MaxBurden, $oRecord->TotalBurden, $oRecord->MaxCoopTotal, $oRecord->CoopOrderCoopTotal,
      $oRecord->CoopOrderMaxStorageBurden, $oRecord->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oOrderTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  
  $oRecord->BuildPageTitle();
  $oOrderTabInfo->MainTabName = $oRecord->PageTitleSuffix;
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->ID = $oRecord->CoopOrderID;
  if ( $oTabInfo->CheckAccess() )
  {
    $oTabInfo->Page = CoopOrderTabInfo::PAGE_ORDERS;
    $oTabInfo->IsSubPage = TRUE;
    $oTabInfo->Status = $oRecord->Status;
    $oTabInfo->CoopOrderTitle = $oRecord->CoopOrderName;
    $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  }
  
  if ( $oRecord->ItemsChangedByCoordinator )
  {
    $g_oError->AddError('הכמות המוזמנת של חלק מהפריטים שונתה ע&quot;י מתאמ/ת קואופרטיב. השורות ששונו צבועות בצבע שונה והכמויות המקוריות מוצגות בסוגריים', 'warning');
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
<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
<?php include_once 'control/headtags.php'; ?>
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $oRecord->PageTitle;  ?></title>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript" >
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
function SwitchViewMode()
{
  var bConfirm = true;
  if (document.getElementById("hidDirty").value == 1)
    bConfirm = confirm(decodeXml('נראה שערכת את מוצרי ההזמנה אך לא ביצעת שמירה. לאחר שינוי התצוגה השינויים האלה יאבדו. האם להמשיך?'));
  
  if (bConfirm)
  {
    document.getElementById("hidPostAction").value = <?php echo OrderItems::POST_ACTION_SWITCH_VIEW_MODE; ?>;
    document.frmMain.submit();
  }
  else
  {
    if (document.getElementById("selProductsView").selectedIndex == <?php echo OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL; ?>)
      document.getElementById("selProductsView").selectedIndex = <?php echo OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY; ?>;
    else
      document.getElementById("selProductsView").selectedIndex = <?php echo OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL; ?>;
  }
}
function SetDirty()
{
  document.getElementById("hidDirty").value = 1;
}

</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oTable->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->ID; ?>" />
<input type="hidden" id="hidDirty" name="hidDirty" value="0" />
<?php include_once 'control/header.php'; ?>
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td><span class="pagename"><?php echo $oRecord->PageTitle;  ?></span></td>
    </tr>
    <tr >
                <td>
                <table cellspacing="0" border="0" cellpadding="0" width="100%">
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
                <?php
                if (!$g_oError->HadError && $oRecord->CanModify)
                {
                ?><tr>
                  <td class="ordercnt" class="nowrapping"><div class="inlineblock"><button type="submit" class="order" onclick="JavaScript:Save();" id="btn_save" name="btn_save" >שמירת מוצרי ההזמנה</button></div>
                    <div class="inlineblock"><select id="selProductsView" name="selProductsView" onchange="JavaScript:SwitchViewMode();" >
                    <?php
                      echo '<option value="' , OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL , '"'; 
                      if ($oTable->ProductsViewMode == OrderItems::PRODUCTS_VIEW_MODE_SHOW_ALL)
                        echo ' selected ';
                      echo '>כל המוצרים</option>',
                       '<option value="' , OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY , '"';
                      if ($oTable->ProductsViewMode == OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY)
                        echo ' selected ';
                      echo '>מוצרים מוזמנים</option>';
                    ?>
                    </select></div>
                  </td>
                </tr>
               <?php
                }
                ?>
                </table>
                </td>
            </tr>
            <tr>
              <td class="resgridparent">
<?php
                if (!is_array($arrItems) || count($arrItems) == 0)
                {
                  ?>
                  <div class="norecords">לא נמצאו רשומות.</div>
                  <?php
                }
                else
                {
                  $bPrintedHeaders = false;
                  $sJoinedItemsTooltipID = '';
                  $bItemReadOnly = FALSE;
                  foreach($arrItems as $oItem)
                  {
                      if (!$oItem->Visible)
                        continue;
                      
                      $bItemReadOnly = $oItem->DisabledProduct || !$oRecord->CanModify;

                      if ($oItem->InvalidEntry)
                      {
                        //show validation message
                        echo '<div class="resgridrow"><span class="message">' , $oItem->ValidationMessage  , '</span></div>',
                         '<div class="resgridrow orderiteminvalidrow">';
                      }
                      else if ($oItem->ChangedByCoordinator)
                      {
                        echo '<div class="resgridrow ';
                        if ($oItem->DisabledProduct)
                          echo 'orderitemcancelledrow" ';
                        else
                          echo 'changedrow" ';
                        echo '>';
                      }
                      else if ($oItem->DisabledProduct)
                        continue;
                      else
                        echo '<div class="resgridrow">';
                      
                      $oProductPackage = new ProductPackage($oItem->ProductItems, $oItem->ProductItemQuantity, 
                                $oItem->ItemUnitAbbrev, $oItem->UnitInterval, $oItem->UnitAbbrev, $oItem->PackageSize, 
                                $oItem->ProductQuantity, $oItem->ProductMaxCoopOrder, $oItem->ProductTotalCoopOrderQuantity,
                           'tooltiphelp', 'ProductPackage' . $oItem->ProductID);
                      
                      //1. ProductName + link to product screen + hidden order item id to identify existing records
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'מוצר');
                      echo '<div class="resgriddatalong">';
                      
                      if ($oProductPackage->HasTooltip)
                      {
                        echo '<a class="tooltiphelprel" href="#" onclick="JavaScript:OpenProductOverview(\'' , $g_sRootRelativePath, '\', ',
                              $oRecord->CoopOrderID, ',', $oItem->ProductID, ');" >', htmlspecialchars($oItem->ProductName), 
                              '<span>';
                        $oProductPackage->EchoTooltip();
                        echo       '</span></a>';
                      }
                      else
                        echo '<span class="link" onclick="JavaScript:OpenProductOverview(\'' , $g_sRootRelativePath, '\', ',
                              $oRecord->CoopOrderID, ',', $oItem->ProductID, ');" >', htmlspecialchars($oItem->ProductName), '</span>';
                      
                      echo  '<input type="hidden" id="' , OrderItems::CTL_PREFIX_ID , $oItem->ProductID , '" name="' ,
                                OrderItems::CTL_PREFIX_ID , $oItem->ProductID , '" value="' , $oItem->OrderItemID , '" />';
                      
                      echo  '</div></div>';
                      
                      //2. Producer
                      $cellProducer = new HtmlGridCellText($oItem->ProducerName, HtmlGridCellText::CELL_TYPE_NORMAL);
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'יצרן');
                      echo '<div class="resgriddatahlong">';
                      echo $cellProducer->EchoHtml();
                      echo "</div></div>";
                      unset($cellProducer);
                      
                      //3. Product Package
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'כמות');
                      echo '<div class="resgriddatashort">';
                      $oProductPackage->SuppressTooltip = TRUE;
                      $oProductPackage->EchoHtml();
                      echo '</div></div>';
                      
                      //4. Coop Price
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'מחיר');
                      echo '<div class="resgriddatatiny">';
                      echo $oItem->ProductCoopPrice , '</div>';
                      echo '</div>';
                      
                      //5. Member Order
                      $txtMemberOrder = new HtmlTextEditNumericRange(OrderItems::CTL_PREFIX_QUANTITY . $oItem->ProductID,
                          'ltr', HtmlTextEdit::TEXTBOX, $oItem->Quantity, $oItem->GetAllowedInterval() );
                      $txtMemberOrder->ReadOnly = $bItemReadOnly;
                      $txtMemberOrder->MaxLength = HtmlTextEditNumeric::NUMBER_DEFAULT_MAX_LENGTH;
                      $txtMemberOrder->CssClass = "orderitemqentry";
                      $txtMemberOrder->OnChange = "JavaScript:SetDirty();";
                      if ($oItem->ChangedByCoordinator)
                      {
                        $txtMemberOrder->SubsequentText = sprintf('&nbsp;(%.2F)', $oItem->MemberLastQuantity);
                      }
                      if ($oItem->ProductMaxUserOrder != NULL)
                        $txtMemberOrder->MaxValue = $oItem->ProductMaxUserOrder;

                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'הזמנה');
                      echo '<div class="resgriddatatiny">';
                      $txtMemberOrder->EchoHtml();
                      echo '</div></div>';
                      
                      //6. Max Fix Addition
                      echo '<div class="resgridcell">';
                      echo '<div class="resgridtitle'; 
                      if ($bPrintedHeaders)
                        echo ' mobiledisplay';
                      echo '"><a id="additionhlp_' , $oItem->ProductID ,'" name="additionhlp_' , 
                        $oItem->ProductID ,'" class="tooltiphelp" href="#additionhlp_' , $oItem->ProductID ,
                        '" >הוספה<span>הכמות המקסימלית שמתאמי הזמנת הקואופרטיב יורשו *להוסיף* להזמנה שלך כדי להשלים הזמנות חלקיות לגודל החבילה. למשל, אם גודל החבילה הוא 2ק&quot;ג, והכמות שהזמנת היא 0.5ק&quot;ג, ע&quot;י השמת הערך 0.5ק&quot;ג בשדה זה, תוכל/י להגדיר שאפשר לעלות עד ל- 1ק&quot;ג כדי להשלים הזמנה חלקית</span></a></div>';
                      
                      
                      echo '<div class="resgriddatatiny">';

                      if (  ($oItem->MemberMaxFixQuantityAddition != NULL && $oItem->MemberMaxFixQuantityAddition != 0)
                        || Product::AllowsPartialOrders($oItem->ProductUnitID, $oItem->ProductQuantity, $oItem->UnitInterval, $oItem->PackageSize))
                      {
                        $txtMemberMaxFixQuantityAddition = new HtmlTextEditNumericRange(OrderItems::CTL_PREFIX_MAX_FIX_QUANTITY_ADDITION . $oItem->ProductID,
                            'ltr', HtmlTextEdit::TEXTBOX, $oItem->MemberMaxFixQuantityAddition, $oItem->GetAllowedInterval() );
                        $txtMemberMaxFixQuantityAddition->ReadOnly = $bItemReadOnly;
                        $txtMemberMaxFixQuantityAddition->MaxLength = HtmlTextEditNumeric::NUMBER_DEFAULT_MAX_LENGTH;
                        if ($oItem->PackageSize != NULL)
                          $txtMemberMaxFixQuantityAddition->MaxValue = $oItem->PackageSize;
                        else
                          $txtMemberMaxFixQuantityAddition->MaxValue = $oItem->ProductQuantity;
                        $txtMemberMaxFixQuantityAddition->CssClass = "orderitemqentry";
                        $txtMemberMaxFixQuantityAddition->OnChange = "JavaScript:SetDirty();";

                        $txtMemberMaxFixQuantityAddition->EchoHtml();
                      }
                      echo '</div></div>';
                      
                      //7. Total Price
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'סה&quot;כ');
                      echo '<div class="resgriddatatiny">';
                      if ($oItem->JoinedItems > 0)
                      {
                        $fOriginalAmount = ($oItem->Quantity/$oItem->ProductQuantity) * $oItem->ProductCoopPrice;
                        $fAmountSaved = $fOriginalAmount - $oItem->CoopTotal;
                        
                        $sJoinedItemsTooltipID = 'joinitemhlp_' . $oItem->OrderItemID;
                        
                        echo '<a id="', $sJoinedItemsTooltipID, '" name="', $sJoinedItemsTooltipID, '" class="tooltiphelprel" href="#', 
                            $sJoinedItemsTooltipID, '" >',$oItem->CoopTotal,'<span>', 
                              sprintf('%1$d מפריטי המוצר צורפו למוצר %2$s. כתוצאה מכך נחסך סכום של %3$s',$oItem->JoinedItems,
                                   $oItem->JoinToProductName, $fAmountSaved),'</span></a>';
                      }
                      else
                        echo $oItem->CoopTotal;
                      
                      echo '</div></div>';
                      
                      //8. Member Comments
                      
                      echo '<div class="resgridcell">';
                      HtmlDivTable::EchoTitle($bPrintedHeaders, 'הערות');
                      if ($oItem->DisabledProduct)
                      {
                        echo '<div class="resgriddatalong">המוצר בוטל</div>';
                      }
                      else
                      {
                        echo '<div class="resgriddatalong">';
                        $txtOrderItemComments = new HtmlTextEdit(OrderItems::CTL_PREFIX_COMMENTS . $oItem->ProductID,
                          NULL, HtmlTextEdit::TEXTAREA, $oItem->MemberComments);
                        $txtOrderItemComments->ReadOnly = $bItemReadOnly;
                        $txtOrderItemComments->MaxLength = OrderItems::MAX_LENGTH_MEMBER_COMMENTS;
                        $txtOrderItemComments->CssClass = "orderitemcentry";
                        $txtOrderItemComments->Rows = 1;
                        $txtOrderItemComments->OnChange = "JavaScript:SetDirty();";
                        $txtOrderItemComments->EncloseInHtmlCell = FALSE;
                        $txtOrderItemComments->EchoEditPartHtml();
                        echo '</div>';
                      }
                      echo '</div>';
                      
                      echo '</div>';
                      
                      $bPrintedHeaders = true;
                  }
                }

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
