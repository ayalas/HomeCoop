<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class PickupLocationTabInfo extends SQLBase {
  const PAGE_NONE = 0;
  const PAGE_ENTRY = 1;
  const PAGE_TRANSACTIONS = 2;
  const PAGE_COORD = 3;
  
  const PROPERTY_PAGE = "Page";
  const PROPERTY_MAIN_TAB_NAME = "MainTabName";
  
  const PERMISSION_GLOBAL_TRANSACTIONS_VIEW = 11;
  
  public function __construct($nID, $nGroupID, $nPage)
  {
    $this->m_aData = array( self::PROPERTY_ID => $nID,
                            self::PROPERTY_COORDINATING_GROUP_ID => $nGroupID,
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
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 
        Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) || $bAccess;
        
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_GLOBAL_TRANSACTIONS_VIEW, 
        Consts::PERMISSION_AREA_TRANSACTIONS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    $bAccess = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_COORD_SET, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) || $bAccess;
    
    return ($bAccess);
  }
  
}

?>
