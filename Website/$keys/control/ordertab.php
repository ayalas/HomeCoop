<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($oOrderTabInfo == NULL)
  return;

function WriteOrderTabElement($sText, $sTabSeparator, $sLink, $bIsOnPage)
{
  global $oOrderTabInfo;
  $sCssClass = 'tabtitle';
  
  echo '<td nowrap class="' , $sCssClass , '">';
  if ($oOrderTabInfo->ID > 0 && !$bIsOnPage)
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

$nColSpan = 2;
$nCoopTotal = $oOrderTabInfo->CoopTotal;

WriteOrderTabElement($oOrderTabInfo->MainTabName,'<!$TAB_SEPARATOR$!>', $g_sRootRelativePath . 'order.php?id=' . $oOrderTabInfo->ID , 
        $oOrderTabInfo->Page == OrderTabInfo::PAGE_ENTRY );

if ($oOrderTabInfo->ID > 0)
{
  WriteOrderTabElement('<!$TAB_ORDER_ITEMS$!>','', 
          $g_sRootRelativePath . 'orderitems.php?id=' . $oOrderTabInfo->ID , $oOrderTabInfo->Page == OrderTabInfo::PAGE_ITEMS);
  
  $nColSpan++;
}

//<!$TAB_SEPARATOR_NO_PROGRESS$!>
?>
<td width="100%" ></td>
</tr>
<tr><td width="100%" align="center" colspan="<?php echo $nColSpan; ?>"><?php

echo '<table cellpadding="2" cellspacing="0" border="0"><tr>';
if ($oOrderTabInfo->OrderCoopFee != NULL && $oOrderTabInfo->OrderCoopFee != 0)
{
  $nCoopTotal += $oOrderTabInfo->OrderCoopFee;

  echo '<td class="headlabel" ><!$FIELD_ORDER_COOP_TOTAL$!><!$FIELD_DISPLAY_NAME_SUFFIX$!></td>',
   '<td class="headdata" >', $oOrderTabInfo->CoopTotal , '</td>',
  
   '<td>&nbsp;&nbsp;</td>',

   '<td class="headlabel" ><!$FIELD_ORDER_COOP_FEE$!><!$FIELD_DISPLAY_NAME_SUFFIX$!></td>',
   '<td class="headdata" >', $oOrderTabInfo->OrderCoopFee , '</td>',
 
   '<td>&nbsp;&nbsp;</td>';
}
  
echo '<td class="headmainlabel" ><!$FIELD_ORDER_COOP_TOTAL_INCLUDE_FEE$!><!$FIELD_DISPLAY_NAME_SUFFIX$!></td>',
 '<td class="headmaindata" >', $nCoopTotal , '</td>',

 '<td>&nbsp;&nbsp;</td>';

if ($oOrderTabInfo->StatusObj->StatusName != NULL)
{
  echo '<td nowrap class="headmaindata">' ,  $oOrderTabInfo->StatusObj->StatusName , '</td>',
   '<td>&nbsp;&nbsp;</td>';
}

if ($oOrderTabInfo->Capacity != NULL)
 echo '<td class="headmaindata">' , $oOrderTabInfo->Capacity , '&nbsp;<!$ORDER_CAPACITY_PERCENT_FULL$!></td>';

echo '</tr></table>';

?></td></tr>
</table>
