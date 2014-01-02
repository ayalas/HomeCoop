<?php
//appears in the home page

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//get active, in date, cooperative orders
$oTable = new ActiveOrders;
$recTable = NULL;
$oPickUpLocs = NULL;
$recPickupLoc = NULL;
$oProducers = NULL;
$recProducer = NULL;
$nCountPickups = 0;
$fBurden = 0;
$dStart = NULL;
$dEnd = NULL;
$dDelivery = NULL;
$oActiveOrderStatus = NULL;
$sActiveOrderStatus = NULL;
$sOrderCssClass = NULL;
$sOrderBoxCssClass = NULL;
$sHistoryButtonCssClass = '';
$oCoopOrderCapacity = NULL;
$oCoopOrderProducerCapacity = NULL;
$oCoopOrderPickupCapacity = NULL;
$oPermissions = NULL;
$bCanCoord = FALSE;
$sProducerSeparator = '';
$bHasOrdersPermission = FALSE;
$bHasProductsPermission = FALSE;
$bHasExportPermission = FALSE;
define('ORDER_DETAILS_ROWS', 2);

try
{
  $recTable = $oTable->GetTable();

  if (!$oTable->HasPermission( SQLBase::PERMISSION_PAGE_ACCESS )) //no permission to make orders
    return;
  
  //this must be set here in order to pass parameter by reference and create it once before the while loop
  $arrCoordPermissions = array(ActiveOrders::PERMISSION_EDIT,  ActiveOrders::PERMISSION_VIEW);
  
  echo '<table class="centerregion" cellspacing="0" ><tr><td><div id="activeOrdersCnt">';
  
  //go through the active cooperative orders to output a "box" (html table with border) for each one
  while ( $recTable )
  {
      $oPickUpLocs = new CoopOrderPickupLocations;
      $oProducers = new CoopOrderProducers;
      $recPickupLoc = $oPickUpLocs->LoadFacet($recTable["CoopOrderKeyID"], $g_oMemberSession->MemberID);
      $recProducer = $oProducers->LoadFacet($recTable["CoopOrderKeyID"], $g_oMemberSession->MemberID);
      if (!$recPickupLoc || !$recProducer) //if this order is filtered out/blocked
      {
        $recTable = $oTable->fetch();
        continue;
      }

      if (!$g_oMemberSession->IsOnlyMember)
      {
        $oPermissions = $oTable->GetCoordPermissions($recTable["CoordinatingGroupID"]);
        $bCanCoord = $oPermissions->HasPermissions( $arrCoordPermissions );
        $bHasOrdersPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_ORDERS);
        $bHasProductsPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_PRODUCTS);
        $bHasExportPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_EXPORT);
      }
      
      $dDelivery = new DateTime($recTable["dDelivery"], $g_oTimeZone);
      $dStart = new DateTime($recTable["dStart"], $g_oTimeZone);
      $dEnd = new DateTime($recTable["dEnd"], $g_oTimeZone);
      $oActiveOrderStatus = new ActiveCoopOrderStatus($dEnd, $dDelivery, $recTable["nStatus"] );
      $sActiveOrderStatus = $oActiveOrderStatus->StatusName;
      
      $sHistoryButtonCssClass = '';
      
      $oCoopOrderCapacity = new CoopOrderCapacity($recTable["fMaxBurden"], $recTable["fBurden"], 
              $recTable["mMaxCoopTotal"], $recTable["mCoopTotal"],
              $recTable["fMaxStorageBurden"], $recTable["fStorageBurden"]);
      
      if ($recTable["nStatus"] == CoopOrder::STATUS_LOCKED)
      {
        $sOrderCssClass = ' class="closedorder" ';
        $sOrderBoxCssClass = ' class="orderbox closedorder" ';
      }
      else
      {
        switch($oActiveOrderStatus->Status)
        {
          case ActiveCoopOrderStatus::Open:
            $sOrderCssClass = ' class="openorder" ';
            $sOrderBoxCssClass = ' class="orderbox openorder" ';
            if ($oCoopOrderCapacity->Percent >= 100) //show order as open only if capacity is less then 100%
              $sActiveOrderStatus = '';
            break;
          case ActiveCoopOrderStatus::Closed:
            $sOrderCssClass = ' class="closedorder" ';
            $sOrderBoxCssClass = ' class="orderbox closedorder" ';
            $sHistoryButtonCssClass = ' HistoryButton';
            break;
          case ActiveCoopOrderStatus::Arrived:
            $sOrderCssClass = ' class="arrivedorder" ';
            $sOrderBoxCssClass = ' class="orderbox arrivedorder" ';
            $sHistoryButtonCssClass = ' HistoryButton';
           break;
          case ActiveCoopOrderStatus::ArrivingToday:
            $sOrderCssClass = ' class="arrivedorder" ';
            $sOrderBoxCssClass = ' class="orderbox arrivedorder" ';
            $sHistoryButtonCssClass = ' HistoryButton';
            break;
          default:
            $sOrderCssClass = '';
            $sOrderBoxCssClass = ' class="orderbox" ';
            $sHistoryButtonCssClass = ' HistoryButton';
            break;
        }
      }
      
      echo '<div ', $sOrderBoxCssClass, ' ><table cellpadding="0" cellspacing="0" width="100%">',     
          
      //row: order title
      "<tr><td width='100%'>";
      
      if ($bCanCoord)
      {
        if ($bHasOrdersPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/orders.php?coid=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
        else
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/cooporder.php?id=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
      }
      else
        echo htmlspecialchars($recTable["sCoopOrder"]);
      
      echo '</td></tr>';
      
      //row: closing date
      echo '<tr><td>',
        '<div class="normalcolor" >Closing‏:‏‎&nbsp;</span><span ',
               $sOrderCssClass , ' >&nbsp;' , $dEnd->format('n.j.Y') , '</div><div ' , $sOrderCssClass , ' >&nbsp;' ,
            'at&nbsp;' , $dEnd->format('g:i A') , '</div>',
        '</td></tr>';
      
      //row: delivery date
      echo "<tr><td><span class='normalcolor' >Delivery‏:‏‎&nbsp;" ,
         $dDelivery->format('n.j.Y') , "</span></td></tr>";
      
      
      //row: order status, button + capacity
      
      //order status and button
       echo '<tr><td ' , $sOrderCssClass , ' height="100%" ><div><div><div>',
       $sActiveOrderStatus , '</div>';
      
      //existing order button
      if ($recTable["OrderID"] != NULL)
      {
        echo '<button type="button" id="btnOrder" class="order" name="btnOrder" onclick="JavaScript:OpenOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["OrderID"] , ');" >My Order</button>';
      }
      //new order button
      else if ($oActiveOrderStatus->Status == ActiveCoopOrderStatus::Open && $oCoopOrderCapacity->Percent < 100)
      {
        echo '<button type="button" id="btnOrder" class="order" name="btnOrder" onclick="JavaScript:NewOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["CoopOrderKeyID"] , ');" >Order Now</button>';
      }
      
      echo "</div>";
      
      //capacity
      echo '<div class="capacitypercentcnt">';
      
      if ($oCoopOrderCapacity->SelectedType != CoopOrderCapacity::TypeNone)
      {      
       echo '<span class="capacitypercent">' , $oCoopOrderCapacity->PercentRounded , 
               '%</span><br/><span class="listareatitle">Full</span>';
      }
      
      echo "</div>";
      
      echo '</div></td></tr>';

      //row: pickup locations
      echo '<tr><td><span class="listareatitle">Pickup:‎</span><span class="normalcolor" >';
      
      $nCountRecs = 0;
      
      while($recPickupLoc)
      {
        if ($nCountRecs > 0)
          echo ',‎&nbsp';
        
        $nCountRecs++;
         
         if ($bCanCoord && $oTable->CheckPickupLocationCoordPermissions($recPickupLoc["CoordinatingGroupID"]))
         {
           echo '<a class="LinkButton', $sHistoryButtonCssClass, '" href="coord/copickuplocorders.php?coid=' , $recTable["CoopOrderKeyID"] , 
                   '&plid=', $recPickupLoc["PickupLocationKeyID"],
                   '" >' , htmlspecialchars($recPickupLoc["sPickupLocation"]) , '</a>';
         }
         else
          echo htmlspecialchars($recPickupLoc["sPickupLocation"]);
                  
         $oCoopOrderPickupCapacity = new CoopOrderCapacity($recPickupLoc["fMaxBurden"], $recPickupLoc["fBurden"], 
                              $recPickupLoc["mMaxCoopTotal"], $recPickupLoc["mCoopTotal"],
                              $recPickupLoc["fMaxStorageBurden"], $recPickupLoc["fStorageBurden"]);
         
         //% full
         if (HOME_PAGE_SHOW_PICKUP_LOCATION_CAPACITIES && $oCoopOrderPickupCapacity->SelectedType != CoopOrderCapacity::TypeNone)
            LanguageSupport::EchoInFixedOrder('&nbsp;', '(' . $oCoopOrderPickupCapacity->PercentRounded . '%)');
                  
         $recPickupLoc = $oPickUpLocs->fetch();
      }
      
      
      echo '</span></td></tr>';
      
      $nCountRecs = 0;
      
      //row: producers
      echo '<tr><td><span class="listareatitle">Producer:‎</span><span class="normalcolor" >';

      while ( $recProducer )
      {        
        if ($nCountRecs > 0)
          echo ',‎&nbsp';
        
        $nCountRecs++;
        
        if ($bCanCoord && $oTable->CheckProducerCoordPermissions($recPickupLoc["CoordinatingGroupID"]))
          echo '<a class="LinkButton', $sHistoryButtonCssClass, '" href="coord/coproducer.php?coid=' , $recTable["CoopOrderKeyID"] , 
                   '&pid=', $recProducer["ProducerKeyID"],
                   '" >' , htmlspecialchars($recProducer["sProducer"]) , '</a>';
        else
          echo htmlspecialchars($recProducer["sProducer"]);
        
        $oCoopOrderProducerCapacity = new CoopOrderCapacity(
                              $recProducer["fMaxBurden"], $recProducer["fBurden"], 
                              $recProducer["mMaxProducerOrder"], $recProducer["mProducerTotal"] );
        
        //% full
        if (HOME_PAGE_SHOW_PRODUCER_CAPACITIES && $oCoopOrderProducerCapacity->SelectedType != CoopOrderCapacity::TypeNone)
          LanguageSupport::EchoInFixedOrder('&nbsp;', '(' . $oCoopOrderProducerCapacity->PercentRounded . '%)');
                
        $recProducer = $oProducers->fetch();
      }
      
      echo '</span></td></tr>';
        
      
      //row: Coordinator extra links
      if ($bCanCoord)
      {
        echo '<tr><td>';
        if ($bHasProductsPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/coproducts.php?id=' , $recTable["CoopOrderKeyID"] , '" >Products</a>&nbsp;';

        if ($bHasExportPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/cooporderexport.php?coid=' , $recTable["CoopOrderKeyID"] , '" >Export</a>';
        echo '</td></tr>';
      }

      echo '</table></div>';
      
      $recTable = $oTable->fetch();
  }
  
  echo '</div></td></tr></table>';
  
}
catch(Exception $eao)
{
  $g_oError->HandleException($eao);
}

?>
