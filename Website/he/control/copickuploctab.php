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
  
  if ($bIsOnPage && $oPLTabInfo->IsSubPage)
    $sCssClass = 'tabtitleroot';
  else
    $sCssClass = 'tabtitle';
  
  echo '<td nowrap class="' , $sCssClass , '">';
  if ($oPLTabInfo->PickupLocationID > 0 && (!$bIsOnPage || $oPLTabInfo->IsSubPage))
    echo '<a href="' , $sLink , '" >' , $sText , '</a>';
  else
    echo '<span>' , $sText , '</span>';

  echo '<span>&nbsp;' , $sTabSeparator , '&nbsp;</span>',
  
   '</td>';
} 

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<?php

  WritePLTabElement($oPLTabInfo->MainTabName,'&gt;&gt;', $g_sRootRelativePath . 'coord/copickuploc.php?coid=' . 
        $oPLTabInfo->CoopOrderID . '&plid=' .  $oPLTabInfo->PickupLocationID , 
        $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PICKUP_LOCATION );

  if ($oPLTabInfo->CheckProducersPermission())
    WritePLTabElement('יצרנים','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/copickuplocproducers.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PRODUCERS);

  if ($oPLTabInfo->CheckProductsPermission())
    WritePLTabElement('מוצרים','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/copickuplocproducts.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_PRODUCTS);

  if ($oPLTabInfo->CheckOrdersPermission())
    WritePLTabElement('הזמנות חברות/ים','', 
          $g_sRootRelativePath . 'coord/copickuplocorders.php?coid=' . $oPLTabInfo->CoopOrderID . '&plid=' .  
          $oPLTabInfo->PickupLocationID , $oPLTabInfo->Page == CoopOrderPickupLocationTabInfo::PAGE_ORDERS);
  
?>
<td width="100%" ></td>    
</tr>
</table>