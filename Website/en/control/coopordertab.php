<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($oTabInfo == NULL)
  return;

if (!$oTabInfo->CheckAccess())
  return;

function WriteTabElement($sText, $sTabSeparator, $sLink, $bIsOnPage)
{
  global $oTabInfo;
  
  if ($sText == '')
      return;
  
  if ($bIsOnPage && $oTabInfo->IsSubPage)
    $sCssClass = 'tabtitleroot';
  else
    $sCssClass = 'tabtitle';
  
  echo '<td nowrap class="' , $sCssClass , '">';
  if ($oTabInfo->ID > 0 && (!$bIsOnPage || $oTabInfo->IsSubPage))
    echo '<a href="' , $sLink , '" >' , $sText , '</a>';
  else
    echo '<span>' , $sText , '</span>';

  echo '<span>&nbsp;' , $sTabSeparator , '&nbsp;</span>',
  
   '</td>';
} 
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" >
<?php 
  if ($oTabInfo->ID > 0)
  {
    echo '<tr><td align="center">',
     '<table cellpadding="2" cellspacing="0" border="0" >',
     '<tr>';

    if ($oTabInfo->CheckCoopOrderSumsPermission())
      echo '<td class="headmainlabel">Total Coop:&nbsp;</td><td class="headmaindata">' , $oTabInfo->CoopTotal , '</td>';
    
    echo '<td>&nbsp;&nbsp;</td>';
    if ($oTabInfo->Capacity != NULL)
      echo '<td class="headmaindata">' , $oTabInfo->Capacity , '&nbsp;Full</td>';
    echo '<td>&nbsp;&nbsp;</td>';
    if ($oTabInfo->StatusObj != NULL)
    {
      echo '<td nowrap class="headmaindata">' ,  $oTabInfo->StatusObj->StatusName , '</td>';
    }
    echo '</tr>',
     '</table>',
     '</td></tr>';
  }
?>
  <tr><td>
      <table cellpadding="0" cellspacing="0" border="0" width="100%" >
        <tr>

<?php


WriteTabElement($oTabInfo->CoopOrderTitle,'&gt;&gt;', $g_sRootRelativePath . 'coord/cooporder.php?id=' . $oTabInfo->ID, 
        $oTabInfo->Page == CoopOrderTabInfo::PAGE_ENTRY );

if ($oTabInfo->ID > 0)
{
  WriteTabElement('Pickup Locations','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/copickuplocs.php?id=' . $oTabInfo->ID , $oTabInfo->Page == CoopOrderTabInfo::PAGE_PICKUP);
  
  if ($oTabInfo->CheckCoopOrderProducersPermission())
    WriteTabElement('Producers','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/coproducers.php?id=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_PRODUCERS);
  
  if ($oTabInfo->CheckCoopOrderProductsPermission())
    WriteTabElement('Products','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/coproducts.php?id=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_PRODUCTS);
  
  if ($oTabInfo->CheckCoopOrderOrdersPermission())
    WriteTabElement('Member Orders','&gt;&gt;', 
          $g_sRootRelativePath . 'coord/orders.php?coid=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_ORDERS);
  
  WriteTabElement('Export Data','', 
          $g_sRootRelativePath . 'coord/cooporderexport.php?coid=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_EXPORT_DATA);
  
  if ($oTabInfo->HasPermission(CoopOrderTabInfo::PROPERTY_PERMISSION_COOP_ORDER_COORD))
  {
    if ($oTabInfo->CheckCoopOrderCopyPermission())
      WriteTabElement('Copy','', $g_sRootRelativePath . 'coord/coopordercopy.php?id=' . $oTabInfo->ID , FALSE);
  } 
    
  if ($oTabInfo->CheckCoopOrderSetCoordPermission())
  {
    $sUrl = $g_sRootRelativePath . 'coord/coordinate.php?rid=' . $oTabInfo->ID
        . '&pa=' . Consts::PERMISSION_AREA_COOP_ORDERS;

    if ($oTabInfo->CoordinatingGroupID > 0)
        $sUrl .= '&id=' . $oTabInfo->CoordinatingGroupID;

    WriteTabElement('Coordination','', $sUrl , FALSE);
  }
}
?>
<td width="100%" ></td>
</tr>

</table></td>
</tr>

</table>
