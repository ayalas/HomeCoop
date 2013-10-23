<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($oPLTabInfo == NULL ||  $oPLTabInfo->PickupLocationID == 0 || !$oPLTabInfo->IsExistingRecord)
  return;

if (!$oTabInfo->CheckAccess())
  return;

if (!$oPLTabInfo->CheckAccess())
  return;

function WritePLTabElement($sText, $sTabSeparator, $sLink, $bIsOnPage)
{    
  global $oPLTabInfo;
  echo '<li';
  if ($bIsOnPage)
    echo ' class="selected" ';
  
  if (!$bIsOnPage || $oPLTabInfo->IsSubPage)
    echo ' onclick="javascript:location.href = \'', $sLink, '\';"'; 
  echo '>', $sText, '</li>';
} 

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
  <td>
<?php
  echo '<ul id="tabPickupLocation" class="tabrow subtabrow" ';
  if ($bHasOrder)
    echo ' style="display: none;" ';
  
  echo '>';
  
  WritePLTabElement($oPLTabInfo->MainTabName,'<!$TAB_SEPARATOR$!>', $g_sRootRelativePath . 'coord/copickuploc.php?coid=' . 
        $oPLTabInfo->CoopOrderID . '&plid=' .  $oPLTabInfo->PickupLocationID , 
        $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PICKUP_LOCATION );

  if ($oPLTabInfo->CheckProducersPermission())
    WritePLTabElement('<!$TAB_COOP_ORDER_PICKUP_LOCATION_PRODUCERS$!>','<!$TAB_SEPARATOR$!>', 
          $g_sRootRelativePath . 'coord/copickuplocproducers.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PRODUCERS);

  if ($oPLTabInfo->CheckProductsPermission())
    WritePLTabElement('<!$TAB_COOP_ORDER_PICKUP_LOCATION_PRODUCTS$!>','<!$TAB_SEPARATOR$!>', 
          $g_sRootRelativePath . 'coord/copickuplocproducts.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PRODUCTS);

  if ($oPLTabInfo->CheckOrdersPermission())
    WritePLTabElement('<!$TAB_COOP_ORDER_PICKUP_LOCATION_ORDERS$!>','', 
          $g_sRootRelativePath . 'coord/copickuplocorders.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_ORDERS);
  
  echo '</ul>';
?>
</td>    
</tr>
</table>