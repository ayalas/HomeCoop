<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($oTabInfo == NULL)
  return;

if (!$oTabInfo->CheckAccess())
  return;

$bHasOrder = FALSE;
$bHasPickupLocation = FALSE;

function WriteTabElement($sText, $sLink, $bIsOnPage)
{
  global $oTabInfo;
  if ($sText == '')
      return;
  
  echo '<li';
  if ($bIsOnPage)
    echo ' class="selected" ';
 
  if (!$bIsOnPage || $oTabInfo->IsSubPage)
    echo ' onclick="javascript:location.href = \'', $sLink, '\';"';
  echo '>', $sText, '</li>';
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
  <tr>
    <td>
          <?php             
            //Main TAB
            $sCoopOrderTabSelected = '';
            $sPickupLocationTabSelected = '';
            $sOrderTabSelected = '';
            $sCurrentMainTab = '';
            $sCurrentMainTabItem = '';
            
            $bHasOrder = (isset($oOrderTabInfo) && $oOrderTabInfo != NULL);
            $bHasPickupLocation = (isset($oPLTabInfo) && $oPLTabInfo != NULL &&  $oPLTabInfo->PickupLocationID > 0 
                && $oPLTabInfo->IsExistingRecord);
            
            if ($bHasOrder) {
              $sOrderTabSelected = 'class="selected"';
              $sCurrentMainTab = 'tabOrder';
            }
            elseif ($bHasPickupLocation) {
              $sPickupLocationTabSelected = 'class="selected"';
              $sCurrentMainTab = 'tabPickupLocation';
            }
            else {
              $sCoopOrderTabSelected = 'class="selected"';
              $sCurrentMainTab = 'tabCoopOrder';
            }
            
            echo '<input type="hidden" id="hidCurrentMainTab" name="hidCurrentMainTab" value="', $sCurrentMainTab,  '" />';
            
            echo '<ul class="tabrow">';
            
            echo '<li id="tabCoopOrderItem" onclick="javascript:ToggleTabDisplay(\'tabCoopOrder\');" ', $sCoopOrderTabSelected , ' title="Cooperative Order">', 
                $oTabInfo->CoopOrderTitle, '</li>';
            if ($bHasPickupLocation) {
              echo '<li id="tabPickupLocationItem" onclick="javascript:ToggleTabDisplay(\'tabPickupLocation\');"  ', $sPickupLocationTabSelected , ' title="Location Name">' ,$oPLTabInfo->MainTabName, '</li>';
            }

            if ($bHasOrder) {
              echo '<li id="tabOrderItem" onclick="javascript:ToggleTabDisplay(\'tabOrder\');"  ', $sOrderTabSelected , '>',$oOrderTabInfo->MainTabName, '</li>';
            }
            
            echo '</ul>';
          ?>
    </td>
  </tr>
  <tr><td>
<table cellpadding="0" cellspacing="0" border="0" width="100%" >
<tr>
<td>
<?php
  echo '<ul id="tabCoopOrder" class="tabrow subtabrow"' ;
  if ($bHasOrder || $bHasPickupLocation)
    echo ' style="display: none;" ';
  echo '>';

 WriteTabElement('Header', $g_sRootRelativePath . 'coord/cooporder.php?id=' . $oTabInfo->ID, 
        $oTabInfo->Page == CoopOrderTabInfo::PAGE_ENTRY );

if ($oTabInfo->ID > 0)
{
  WriteTabElement('Pickup Locations',
          $g_sRootRelativePath . 'coord/copickuplocs.php?id=' . $oTabInfo->ID , $oTabInfo->Page == CoopOrderTabInfo::PAGE_PICKUP);
  
  if ($oTabInfo->CheckCoopOrderProducersPermission())
    WriteTabElement('Producers',
          $g_sRootRelativePath . 'coord/coproducers.php?id=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_PRODUCERS);
  
  if ($oTabInfo->CheckCoopOrderProductsPermission())
    WriteTabElement('Products', 
          $g_sRootRelativePath . 'coord/coproducts.php?id=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_PRODUCTS);
  
  if ($oTabInfo->CheckCoopOrderOrdersPermission())
    WriteTabElement('Member Orders', 
          $g_sRootRelativePath . 'coord/orders.php?coid=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_ORDERS);
  
  WriteTabElement('Export Data',
          $g_sRootRelativePath . 'coord/cooporderexport.php?coid=' . $oTabInfo->ID, $oTabInfo->Page == CoopOrderTabInfo::PAGE_EXPORT_DATA);
  
  if ($oTabInfo->HasPermission(CoopOrderTabInfo::PROPERTY_PERMISSION_COOP_ORDER_COORD))
  {
    if ($oTabInfo->CheckCoopOrderCopyPermission())
      WriteTabElement('Copy', $g_sRootRelativePath . 'coord/coopordercopy.php?id=' . $oTabInfo->ID , FALSE);
  } 
    
  if ($oTabInfo->CheckCoopOrderSetCoordPermission())
  {
    $sUrl = $g_sRootRelativePath . 'coord/coordinate.php?rid=' . $oTabInfo->ID
        . '&pa=' . Consts::PERMISSION_AREA_COOP_ORDERS;

    if ($oTabInfo->CoordinatingGroupID > 0)
        $sUrl .= '&id=' . $oTabInfo->CoordinatingGroupID;

    WriteTabElement('Coordination', $sUrl , FALSE);
  }
}

  echo '</ul>';
?>
</td>
</tr>
</table></td>
</tr>

</table>