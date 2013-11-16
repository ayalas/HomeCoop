<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($oOrderTabInfo == NULL)
  return;

function WriteOrderTabElement($sText, $sLink, $bIsOnPage)
{
  global $oOrderTabInfo;
      
  echo '<li';
  if ($bIsOnPage)
    echo ' class="selected" ';
  elseif ($oOrderTabInfo->ID > 0)
    echo ' onclick="javascript:location.href = \'', $sLink, '\';"'; 
  
  echo ' >', $sText, '</li>';
} 
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>
  <td><ul id="tabOrder" class="tabrow subtabrow"><?php
      WriteOrderTabElement('כותר ההזמנה', $g_sRootRelativePath . 'order.php?id=' . $oOrderTabInfo->ID , 
        $oOrderTabInfo->Page == OrderTabInfo::PAGE_ENTRY );

    if ($oOrderTabInfo->ID > 0)
    {
      WriteOrderTabElement('פריטים מוזמנים',
              $g_sRootRelativePath . 'orderitems.php?id=' . $oOrderTabInfo->ID , $oOrderTabInfo->Page == OrderTabInfo::PAGE_ITEMS);
    }

?></ul>
  </td>
</tr> 
</table>
<?php

$nCoopTotal = $oOrderTabInfo->CoopTotal;
$sOrderSumClass = '';
if ($oOrderTabInfo->StatusObj->Status == ActiveCoopOrderStatus::Open)
  $sOrderSumClass = " opensum";
else
  $sOrderSumClass = " closedsum";

echo '<div class="ordersummary', $sOrderSumClass, '">';
if ($oOrderTabInfo->OrderCoopFee != NULL && $oOrderTabInfo->OrderCoopFee != 0)
{
  $nCoopTotal += $oOrderTabInfo->OrderCoopFee;

  echo '<div class="headlabel" >סה&quot;כ מוצרים‏:‏‏&nbsp;',
      $oOrderTabInfo->CoopTotal, '</div>',
   '<div class="headlabel" >תוספת לקואופ‏:‏‏&nbsp;',
      $oOrderTabInfo->OrderCoopFee, '</div>';
}
  
echo '<div class="headlabel" >סה&quot;כ ההזמנה‏:‏‏&nbsp;',
    $nCoopTotal , '</div>';

if ($oOrderTabInfo->StatusObj->StatusName != NULL)
{
  echo '<div class="headlabel">' ,  $oOrderTabInfo->StatusObj->StatusName , '</div>';
}

if ($oOrderTabInfo->Capacity != NULL)
 echo '<div class="headlabel">' , $oOrderTabInfo->Capacity , '&nbsp;תפוסה</div>';

echo '</div>';

?>

