<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if (!isset($oPickupLocationTabInfo) || $oPickupLocationTabInfo == NULL || $oPickupLocationTabInfo->ID == 0 
   || !$oPickupLocationTabInfo->CheckAccess())
  return;

function WriteMemberTabElement($sText, $sLink, $bIsOnPage)
{
  echo '<li';
  if ($bIsOnPage)
    echo ' class="selected" ';

  echo ' onclick="javascript:location.href = \'', $sLink, '\';"'; 
  
  echo ' >', $sText, '</li>';
}

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>
  <td><ul id="tabPickupLocation" class="tabrow subtabrow"><?php
      
      if ($oPickupLocationTabInfo->HasPermissions(array(PickupLocationTabInfo::PERMISSION_EDIT)))
      {
        WriteMemberTabElement($oPickupLocationTabInfo->MainTabName, $g_sRootRelativePath . 'coord/pickuploc.php?id=' . $oPickupLocationTabInfo->ID , 
          $oPickupLocationTabInfo->Page == PickupLocationTabInfo::PAGE_ENTRY );
      }

      if ($oPickupLocationTabInfo->HasPermissions(array(PickupLocationTabInfo::PERMISSION_EDIT, 
               PickupLocationTabInfo::PERMISSION_GLOBAL_TRANSACTIONS_VIEW)))
      {
        WriteMemberTabElement('Transactions',
          $g_sRootRelativePath . 'coord/pickuploctransactions.php?id=' . $oPickupLocationTabInfo->ID , 
          $oPickupLocationTabInfo->Page == PickupLocationTabInfo::PAGE_TRANSACTIONS);
      }
      
      if ($oPickupLocationTabInfo->HasPermissions(array(SQLBase::PERMISSION_COORD_SET))) 
      {
        $sUrl = $g_sRootRelativePath . 'coord/coordinate.php?rid=' . $oPickupLocationTabInfo->ID .
            "&pa=" . Consts::PERMISSION_AREA_PICKUP_LOCATIONS;
        
        if ($oPickupLocationTabInfo->CoordinatingGroupID > 0)
          $sUrl .= "&id=" .  $oPickupLocationTabInfo->CoordinatingGroupID;
        
        WriteMemberTabElement('Coordination',
          $sUrl, 
          $oPickupLocationTabInfo->Page == PickupLocationTabInfo::PAGE_COORD);
      }
      
?></ul>
  </td>
</tr> 
</table>