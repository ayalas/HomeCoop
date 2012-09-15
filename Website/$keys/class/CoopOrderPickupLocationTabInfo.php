<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates the pickup location coordinator's tab
class CoopOrderPickupLocationTabInfo extends SQLBase {

  const PERMISSION_PRODUCERS = 10;
  const PERMISSION_PRODUCTS = 11;
  const PERMISSION_ORDERS = 12;
  
  const PAGE_NONE = 0;
  const PAGE_PICKUP_LOCATION = 1;
  const PAGE_PRODUCERS = 2;
  const PAGE_PRODUCTS = 3;
  const PAGE_ORDERS = 4;
  
  const PROPERTY_MAIN_TAB_NAME = "MainTabName";
  const PROPERTY_PAGE = "Page";
  const PROPERTY_IS_SUB_PAGE = "IsSubPage";
  const PROPERTY_IS_EXISTING_RECORD = "IsExistingRecord";
  
  public function __construct($nCoopOrderID, $nPickupLocationID, $sMainTabName, $nPage)
  {
    $this->m_aData = array( CoopOrderPickupLocation::PROPERTY_COOP_ORDER_ID => $nCoopOrderID,
                            CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID => $nPickupLocationID,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_MAIN_TAB_NAME => $sMainTabName,
                            self::PROPERTY_PAGE => $nPage,
                            self::PROPERTY_IS_SUB_PAGE => FALSE,
                            self::PROPERTY_IS_EXISTING_RECORD => ($nPickupLocationID > 0)
                            );
  }
  
  public function CheckAccess()
  {
    global $g_oMemberSession;
    if ($g_oMemberSession->IsOnlyMember)
      return FALSE;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
          Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);

    $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
          Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);

    return ($bEdit || $bView);
  }
  
  //check permissions
  public function CheckProducersPermission()
  {
    return $this->AddPermissionBridge(self::PERMISSION_PRODUCERS, 
           Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
  }
  
  public function CheckProductsPermission()
  {
    return $this->AddPermissionBridge(self::PERMISSION_PRODUCTS, 
           Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
  }
  
  public function CheckOrdersPermission()
  {
    return $this->AddPermissionBridge(self::PERMISSION_ORDERS, 
           Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
  }
  
}

?>
