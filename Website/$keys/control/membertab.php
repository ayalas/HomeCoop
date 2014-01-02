<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if (!isset($oMemberTabInfo) || $oMemberTabInfo == NULL || $oMemberTabInfo->MemberID == 0 || !$oMemberTabInfo->CheckAccess())
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
  <td><ul id="tabMember" class="tabrow subtabrow"><?php
      WriteMemberTabElement($oMemberTabInfo->MainTabName, $g_sRootRelativePath . 'coord/member.php?id=' . $oMemberTabInfo->MemberID , 
        $oMemberTabInfo->Page == MemberTabInfo::PAGE_ENTRY );

      if ($oMemberTabInfo->HasPermissions(array(MemberTabInfo::PROPERTY_PERMISSION_MEMBER_ROLES_COORD, 
               MemberTabInfo::PROPERTY_PERMISSION_MEMBER_ROLES_VIEW)))
      {
        WriteMemberTabElement('<!$LINK_MEMBER_ROLES$!>',
          $g_sRootRelativePath . 'coord/memberroles.php?id=' . $oMemberTabInfo->MemberID , 
          $oMemberTabInfo->Page == MemberTabInfo::PAGE_ROLES);
      }
      
      if ($oMemberTabInfo->HasPermissions(array(MemberTabInfo::PROPERTY_PERMISSION_MEMBER_PICKUP_LOCATIONS_MODIFY, 
               MemberTabInfo::PROPERTY_PERMISSION_MEMBER_PICKUP_LOCATIONS_COORD)))
      {
        WriteMemberTabElement('<!$TAB_PICKUP_LOCATIONS$!>',
          $g_sRootRelativePath . 'coord/memberpickuplocs.php?id=' . $oMemberTabInfo->MemberID , 
          $oMemberTabInfo->Page == MemberTabInfo::PAGE_PICKUP_LOCATIONS);
      }
      
      if ($oMemberTabInfo->HasPermissions(array(MemberTabInfo::PROPERTY_PERMISSION_MEMBER_PRODUCERS_MODIFY, 
               MemberTabInfo::PROPERTY_PERMISSION_MEMBER_PRODUCERS_COORD)))
      {
        WriteMemberTabElement('<!$TAB_PRODUCERS$!>',
          $g_sRootRelativePath . 'coord/memberproducers.php?id=' . $oMemberTabInfo->MemberID , 
          $oMemberTabInfo->Page == MemberTabInfo::PAGE_PRODUCERS);
      }

?></ul>
  </td>
</tr> 
</table>
