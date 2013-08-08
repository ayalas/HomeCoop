<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates the coop order coordinator's tab
class CoopOrderTabInfo extends SQLBase {
  
  const PAGE_NONE = 0;
  const PAGE_ENTRY = 1;
  const PAGE_PICKUP = 2;
  const PAGE_PRODUCERS = 3;
  const PAGE_PRODUCTS = 4;
  const PAGE_ORDERS = 5;
  const PAGE_EXPORT_DATA = 6;
  
  const PROPERTY_PAGE = "Page";
  const PROPERTY_IS_SUB_PAGE = "IsSubPage";
  const PROPERTY_COOP_TOTAL = "CoopTotal";
  const PROPERTY_CAPACITY = "Capacity";
  const PROPERTY_NAME = "CoopOrderTitle";
  
  const PROPERTY_COOP_ORDER_STATUS_OBJECT = "StatusObj";
  
  const PROPERTY_PERMISSION_COOP_ORDER_COORD = 1;
  const PROPERTY_PERMISSION_COOP_ORDER_COPY = 2;
  const PROPERTY_PERMISSION_COOP_ORDER_PRODUCERS_EDIT = 30;
  const PROPERTY_PERMISSION_COOP_ORDER_PRODUCERS_VIEW = 31;
  const PROPERTY_PERMISSION_COOP_ORDER_PRODUCTS_EDIT = 40;
  const PROPERTY_PERMISSION_COOP_ORDER_PRODUCTS_VIEW = 41;
  const PROPERTY_PERMISSION_COOP_ORDER_ORDERS = 5;
  const PROPERTY_PERMISSION_COOP_ORDER_PAGE_VIEW = 6;
  const PROPERTY_PERMISSION_COOP_ORDER_SUMS = 7;
  
  public function __construct()
  {
    $this->m_aData = array(CoopOrder::PROPERTY_ID => 0,
                            CoopOrder::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            self::PROPERTY_PAGE => self::PAGE_NONE,
                            self::PROPERTY_IS_SUB_PAGE => FALSE,
                            self::PROPERTY_COOP_ORDER_STATUS_OBJECT => NULL,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_CAPACITY => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_NAME => NULL
                            );
  }
  
  public function CheckAccess()
  {
    global $g_oMemberSession;
    if ($g_oMemberSession->IsOnlyMember)
      return FALSE;
    
    $bExistingRecord = ($this->m_aData[CoopOrder::PROPERTY_ID] > 0);
    
    //this function can be called more then once, so check if already has bridges
    if ($this->HasPermissions(array(self::PROPERTY_PERMISSION_COOP_ORDER_COORD,
        self::PROPERTY_PERMISSION_COOP_ORDER_PAGE_VIEW)))
      return TRUE;
    
    $bCoord = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_COORD, 
           Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], !$bExistingRecord);
     
    $bView = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_PAGE_VIEW, 
             Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
             $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], !$bExistingRecord);
     
    return ($bCoord || $bView);
  }
    
  public function CheckCoopOrderCopyPermission()
  {
    return $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_COPY, 
           Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_COPY, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
  }
  
  public function CheckCoopOrderProducersPermission()
  {
    $bEdit =  $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_PRODUCERS_EDIT, 
           Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
    
    $bView =  $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_PRODUCERS_VIEW, 
           Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
    
    return ($bEdit || $bView);
  }
  
  public function CheckCoopOrderOrdersPermission()
  { 
    return $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_ORDERS, 
           Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
  }
  
  public function CheckCoopOrderSetCoordPermission()
  {
    return $this->AddPermissionBridge(self::PERMISSION_COORD_SET, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_COORD_SET, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
  }
  
  public function CheckCoopOrderSumsPermission()
  { 
    return $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_SUMS, 
           Consts::PERMISSION_AREA_COOP_ORDER_SUMS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
  }
  
  public function CheckCoopOrderProductsPermission()
  {
    $bEdit =  $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_PRODUCTS_EDIT, 
           Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
    $bView = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_PRODUCTS_VIEW, 
           Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 
            0, TRUE);
    
    return ($bEdit || $bView);
  }
}

?>
