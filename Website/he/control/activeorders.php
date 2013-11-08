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
  
  echo '<table class="centerregion" cellspacing="0" >';
  
  //go through the active cooperative orders to output a "box" (html table with border) for each one
  while ( $recTable )
  {
      $oPickUpLocs = new CoopOrderPickupLocations;
      $recPickupLoc = $oPickUpLocs->LoadFacet($recTable["CoopOrderKeyID"], $g_oMemberSession->MemberID);
      if (!$recPickupLoc) //if this order is filtered out/pickup locations blocked
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
      
      echo '<tr><td><table cellpadding="0" ', $sOrderBoxCssClass,
              ' cellspacing="0" width="100%" ><tr><td><table cellpadding="0" cellspacing="0" width="100%">',
           
         '<tr>', //start
      
      //order summary
       "<td width='100%'>",
      '<table cellpadding="4" border="0" cellspacing="0" width="100%" >',
      
      //order first row
       '<tr>',
       '<td colspan="5" ' , $sOrderCssClass , '>';
      
      if ($bCanCoord)
      {
        if ($bHasOrdersPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/orders.php?coid=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
        else
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/cooporder.php?id=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
        
        if ($bHasProductsPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/coproducts.php?id=' , $recTable["CoopOrderKeyID"] , '" >מוצרים</a>&nbsp;';
        
        if ($bHasExportPermission)
          echo '<a class="LinkButton headdata', $sHistoryButtonCssClass, '" href="coord/cooporderexport.php?coid=' , $recTable["CoopOrderKeyID"] , '" >יצוא</a>';
      }
      else
        echo htmlspecialchars($recTable["sCoopOrder"]);
      
      echo '</td>',
      
      '</tr>',
      
      //end of order first row
      
      //order details and pickup locations
      '<tr>',
      
      //pickup locations
       "<td>",
       '<table cellpadding="0" border="0" cellspacing="0" width="100%" >',
       '<tr><td class="listareatitle" width="100%" >איסוף</td></tr>';
      //loop through pickup locations
      $nCountPickups = 0;
      
      
      while($recPickupLoc)
      {
        $nCountPickups++;
         echo '<tr><td><span class="normalcolor" >'; 
         
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
         
         echo '</span></td></tr>';
         
         $recPickupLoc = $oPickUpLocs->fetch();
      }
      
      if ($nCountPickups < ORDER_DETAILS_ROWS) //if smaller than order details rows, fill with empty rows
      {
        echo '<tr><td rowspan="' , (ORDER_DETAILS_ROWS - $nCountPickups) , '" ></td></tr>';
      }
      
      echo '</table>',
       "</td>"; //end of pickup locations
       
     //producers
     echo  
        "<td>",
        '<table cellpadding="0" border="0" cellspacing="0" width="100%" >',
         '<tr><td class="listareatitle" width="100%" >יצרן</td></tr>';
      
      $oProducers = new CoopOrderProducers;
      $recProducer = $oProducers->LoadList($recTable["CoopOrderKeyID"]);

      while ( $recProducer )
      {        
        echo '<tr><td><span class="normalcolor" >';
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
        
        echo '</span></td></tr>';
      }
      
      echo '</table>',
       "</td>"; //end of producers
      
      //order details
      
     echo  "<td>",
       '<table cellpadding="0" width="100%" cellspacing="0" >',
       '<tr>',
         '<td><span class="normalcolor" >סגירה‏:‏‏&nbsp;</span><span ',
                $sOrderCssClass , ' >&nbsp;' , $dEnd->format('j.n.Y') , '</span><span ' , $sOrderCssClass , ' >&nbsp;' ,
             'בשעה&nbsp;' , $dEnd->format('G:i') , '</span>',
         '</td></tr>',
         "<tr><td><span class='normalcolor' >משלוח‏:‏‏&nbsp;" ,
              $dDelivery->format('j.n.Y') , "</span></td></tr>";
      
      if (ORDER_DETAILS_ROWS < $nCountPickups) //if smaller than order details rows, fill with empty rows
      {
        echo '<tr><td rowspan="' , ($nCountPickups - ORDER_DETAILS_ROWS) , '" ></td></tr>';
      }
      
      echo '</table>',
       "</td>", //end of order details
            
       //order status and button
       '<td ' , $sOrderCssClass , ' width="120px" height="100%" >',
       $sActiveOrderStatus , '<br/>';
     
      
      //existing order button
      if ($recTable["OrderID"] != NULL)
      {
        echo '<button type="button" id="btnOrder" class="order" name="btnOrder" onclick="JavaScript:OpenOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["OrderID"] , ');" >ההזמנה שלי</button>';
      }
      //new order button
      else if ($oActiveOrderStatus->Status == ActiveCoopOrderStatus::Open && $oCoopOrderCapacity->Percent < 100)
      {
        echo '<button type="button" id="btnOrder" class="order" name="btnOrder" onclick="JavaScript:NewOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["CoopOrderKeyID"] , ');" >הזמינו עכשיו</button>';
      }
      
      echo "</td>", //end of order status and button
      
      //capacity
       "<td class='capacitypercentcnt' >";
      
      if ($oCoopOrderCapacity->SelectedType != CoopOrderCapacity::TypeNone)
      {      
       echo '<span class="capacitypercent">' , $oCoopOrderCapacity->PercentRounded , 
               '%</span><br/><span class="listareatitle">תפוסה</span>';
      }
      
      echo '</td>', //end of capacity

       '</tr>';
      //end of order details and pickup locations
      
      
      
      echo '</table>',
       '</td>', //end of order summary
       '</tr>', //end

       '</table></td></tr></table></td></tr>';
      
      $recTable = $oTable->fetch();
  }
  
  echo '</table>';
  
}
catch(Exception $eao)
{
  $g_oError->HandleException($eao);
}

?>
