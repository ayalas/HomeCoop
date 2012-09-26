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
  
  echo '<table cellpadding="10" cellspacing="0" width="100%" >';
  
  //go through the active cooperative orders to output a "box" (html table with border) for each one
  while ( $recTable )
  {
      if (!$g_oMemberSession->IsOnlyMember)
      {
        $oPermissions = $oTable->GetCoordPermissions($recTable["CoordinatingGroupID"]);
        $bCanCoord = $oPermissions->HasPermissions( $arrCoordPermissions );
        $bHasOrdersPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_ORDERS);
        $bHasProductsPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_PRODUCTS);
        $bHasExportPermission = $oPermissions->HasPermission(ActiveOrders::PERMISSION_EXPORT);
      }
      
      $dDelivery = new DateTime($recTable["dDelivery"]);
      $dStart = new DateTime($recTable["dStart"]);
      $dEnd = new DateTime($recTable["dEnd"]);
      $oActiveOrderStatus = new ActiveCoopOrderStatus($dEnd, $dDelivery, $recTable["nStatus"] );
      $sActiveOrderStatus = $oActiveOrderStatus->StatusName;
      
      $oCoopOrderCapacity = new CoopOrderCapacity($recTable["fMaxBurden"], $recTable["fBurden"], 
              $recTable["mMaxCoopTotal"], $recTable["mCoopTotal"]);
      
      if ($recTable["nStatus"] == CoopOrder::STATUS_LOCKED)
        $sOrderCssClass = ' class="closedorder" ';
      else
      {
        switch($oActiveOrderStatus->Status)
        {
          case ActiveCoopOrderStatus::Open:
            $sOrderCssClass = ' class="openorder" ';
            if ($oCoopOrderCapacity->Percent >= 100) //show order as open only if capacity is less then 100%
              $sActiveOrderStatus = '';
            break;
          case ActiveCoopOrderStatus::Closed:
            $sOrderCssClass = ' class="closedorder" ';
            break;
          case ActiveCoopOrderStatus::Arrived:
            $sOrderCssClass = ' class="arrivedorder" ';
           break;
          case ActiveCoopOrderStatus::ArrivingToday:
            $sOrderCssClass = ' class="arrivedorder" ';
            break;
          default:
            $sOrderCssClass = '';
            break;
        }
      }
      
      echo '<tr><td><table cellpadding="4" border="3" ', $sOrderCssClass,
              ' cellspacing="0" width="100%" ><tr><td><table cellpadding="0" cellspacing="0" width="100%">',
           
         '<tr>', //start
      
      //order summary
       "<td width='100%'>",
      '<table cellpadding="0" border="0" cellspacing="0" width="100%" >',
      
      //order first row
       '<tr>',
       '<td colspan="4" ' , $sOrderCssClass , '>';
      
      if ($bCanCoord)
      {
        if ($bHasOrdersPermission)
          echo '<a href="coord/orders.php?coid=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
        else
          echo '<a href="coord/cooporder.php?id=' , $recTable["CoopOrderKeyID"] , '" >' , htmlspecialchars($recTable["sCoopOrder"]) , '</a>&nbsp;';
        
        if ($bHasProductsPermission)
          echo '<a class="headdata" href="coord/coproducts.php?id=' , $recTable["CoopOrderKeyID"] , '" >Products</a>&nbsp;';
        
        if ($bHasExportPermission)
          echo '<a class="headdata" href="coord/cooporderexport.php?coid=' , $recTable["CoopOrderKeyID"] , '" >Export</a>';
      }
      else
        echo htmlspecialchars($recTable["sCoopOrder"]);
      
      echo '</td>',
      
      '</tr>',
      
      //end of order first row
      
      //order details and pickup locations
      '<tr>',
      
      //pickup locations
       "<td width='180px'>",
       '<table cellpadding="0" border="0" cellspacing="0" width="100%" >',
       '<tr><td class="listareatitle" width="100%" >Pickup‏:‏</td></tr>';
      //loop through pickup locations
      $nCountPickups = 0;
      
      $oPickUpLocs = new CoopOrderPickupLocations;
      $recPickupLoc = $oPickUpLocs->LoadList($recTable["CoopOrderKeyID"], $g_oMemberSession->MemberID);
      while($recPickupLoc)
      {
        $nCountPickups++;
         echo '<tr><td><span class="normalcolor" >'; 
         
         if ($bCanCoord && $oTable->CheckPickupLocationCoordPermissions($recPickupLoc["CoordinatingGroupID"]))
         {
           echo '<a href="coord/copickuplocorders.php?coid=' , $recTable["CoopOrderKeyID"] , 
                   '&plid=', $recPickupLoc["PickupLocationKeyID"],
                   '" >' , htmlspecialchars($recPickupLoc["sPickupLocation"]) , '</a>';
         }
         else
          echo htmlspecialchars($recPickupLoc["sPickupLocation"]);
         
         
         $oCoopOrderPickupCapacity = new CoopOrderCapacity($recPickupLoc["fMaxBurden"], $recPickupLoc["fBurden"], 
                              $recPickupLoc["mMaxCoopTotal"], $recPickupLoc["mCoopTotal"]);
         
         //% full
        if ($oCoopOrderPickupCapacity->SelectedType != CoopOrderCapacity::TypeNone)
          echo '&nbsp;(' , $oCoopOrderPickupCapacity->PercentRounded , '%)';
         
         echo '</span></td></tr>';
         
         $recPickupLoc = $oPickUpLocs->fetch();
      }
      
      if ($nCountPickups < ORDER_DETAILS_ROWS) //if smaller than order details rows, fill with empty rows
      {
        echo '<tr><td rowspan="' , (ORDER_DETAILS_ROWS - $nCountPickups) , '" ></td></tr>';
      }
      
      echo '</table>',
       "</td>", //end of pickup locations
      
      //order details
      
       "<td>",
       '<table cellpadding="0" border="0" cellspacing="0" >',
      
       '<tr><td class="oppositealign" ><span class="normalcolor" >Closing‏:‏</span></td><td><span ',
                $sOrderCssClass , ' >&nbsp;' , $dEnd->format('n.j.Y') , '</span><span ' , $sOrderCssClass , ' >&nbsp;' ,
             'at&nbsp;' , $dEnd->format('g:i A') , '</span></td></tr>',
      
       "<tr><td class='oppositealign' ><span class='normalcolor' >Delivery‏:‏",
              "</span></td><td class='regularalign'><span class='normalcolor' >&nbsp;" ,
              $dDelivery->format('n.j.Y') , "</span></td></tr>";
      
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
        echo '<button type="button" id="btnOrder" name="btnOrder" onclick="JavaScript:OpenOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["OrderID"] , ');" >My Order</button>';
      }
      //new order button
      else if ($oActiveOrderStatus->Status == ActiveCoopOrderStatus::Open && $oCoopOrderCapacity->Percent < 100)
      {
        echo '<button type="button" id="btnOrder" name="btnOrder" onclick="JavaScript:NewOrder(\'',
               $g_sRootRelativePath , '\',' ,  $recTable["CoopOrderKeyID"] , ');" >Order Now</button>';
      }
      
      echo "</td>", //end of order status and button
      
      //capacity
       "<td width='80px' >";
      
      if ($oCoopOrderCapacity->SelectedType != CoopOrderCapacity::TypeNone)
      {      
       echo '<span class="capacitypercent">' , $oCoopOrderCapacity->PercentRounded , 
               '%</span><br/><span class="listareatitle">Full</span>';
      }
      
      echo '</td>', //end of capacity

       '</tr>',
      //end of order details and pickup locations
      
      //producers
       '<tr>',
       '<td class="listareatitle" >Producers‏:‏</td>',
       '<td colspan="3" class="regularalign" ><span class="normalcolor" >';
      
      $oProducers = new CoopOrderProducers;
      $recProducer = $oProducers->LoadList($recTable["CoopOrderKeyID"]);
      $sProducerSeparator = ''; //for first element, avoid comma
      while ( $recProducer )
      {
        echo $sProducerSeparator; 
        
        if ($bCanCoord && $oTable->CheckProducerCoordPermissions($recPickupLoc["CoordinatingGroupID"]))
          echo '<a href="coord/coproducer.php?coid=' , $recTable["CoopOrderKeyID"] , 
                   '&pid=', $recProducer["ProducerKeyID"],
                   '" >' , htmlspecialchars($recProducer["sProducer"]) , '</a>';
        else
          echo htmlspecialchars($recProducer["sProducer"]);
        
        $oCoopOrderProducerCapacity = new CoopOrderCapacity(
                              $recProducer["fMaxBurden"], $recProducer["fBurden"], 
                              $recProducer["mMaxProducerOrder"], $recProducer["mProducerTotal"] );
        
        //% full
        if ($oCoopOrderProducerCapacity->SelectedType != CoopOrderCapacity::TypeNone)
          echo ' (' , $oCoopOrderProducerCapacity->PercentRounded , '%)';
        
        $sProducerSeparator = ', ';
        
        $recProducer = $oProducers->fetch();
      }
      
      echo '</span></td>',
       '</tr>', //end
      
       '</table>',
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
