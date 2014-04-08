<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class MemberTabInfo extends SQLBase {
  const PAGE_NONE = 0;
  const PAGE_ENTRY = 1;
  const PAGE_ROLES = 2;
  const PAGE_TRANSACTIONS = 3;
  const PAGE_PICKUP_LOCATIONS = 4;
  const PAGE_PRODUCERS = 5;
  
  const PROPERTY_MEMBER_ID = "MemberID";
  const PROPERTY_PAGE = "Page";
  const PROPERTY_MAIN_TAB_NAME = "MainTabName";
  
  const PERMISSION_MEMBER_MODIFY = 1;
  const PERMISSION_MEMBER_VIEW = 2;
  const PERMISSION_MEMBER_ROLES_COORD = 21;
  const PERMISSION_MEMBER_ROLES_VIEW = 22;
  const PERMISSION_MEMBER_PICKUP_LOCATIONS_MODIFY = 30;
  const PERMISSION_MEMBER_PICKUP_LOCATIONS_COORD = 31;
  const PERMISSION_MEMBER_PRODUCERS_MODIFY = 32;
  const PERMISSION_MEMBER_PRODUCERS_COORD = 33;
  const PERMISSION_MEMBER_TRANSACTIONS_VIEW = 44;
  
  public function __construct($nID, $nPage)
  {
    $this->m_aData = array( self::PROPERTY_MEMBER_ID => $nID,
                            self::PROPERTY_PAGE => $nPage,
                            self::PROPERTY_MAIN_TAB_NAME => NULL
                            );
  }
  
  public function CheckAccess()
  {
    global $g_oMemberSession;
    if ($g_oMemberSession->IsOnlyMember)
      return FALSE;   
    
    $bAccess = FALSE;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_MODIFY, Consts::PERMISSION_AREA_MEMBERS, 
        Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_VIEW, Consts::PERMISSION_AREA_MEMBERS, 
        Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_ROLES_COORD, Consts::PERMISSION_AREA_MEMBER_ROLES, 
        Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_ROLES_VIEW, Consts::PERMISSION_AREA_MEMBER_ROLES, 
        Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_PICKUP_LOCATIONS_MODIFY, 
        Consts::PERMISSION_AREA_MEMBER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_PICKUP_LOCATIONS_COORD, 
        Consts::PERMISSION_AREA_MEMBER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_COORD, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_PRODUCERS_MODIFY, 
        Consts::PERMISSION_AREA_MEMBER_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_PRODUCERS_COORD, 
        Consts::PERMISSION_AREA_MEMBER_PRODUCERS, Consts::PERMISSION_TYPE_COORD, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_MEMBER_TRANSACTIONS_VIEW, 
        Consts::PERMISSION_AREA_TRANSACTIONS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    return ($bAccess);
  }
}

?>