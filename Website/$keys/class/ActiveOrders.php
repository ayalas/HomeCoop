<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates the home page orders boxes
//caller: control/activeorders.php
class ActiveOrders extends SQLBase {
  
  const PERMISSION_ORDERS = 10;
  const PERMISSION_PRODUCTS = 11;
  const PERMISSION_EXPORT = 12;
  const PERMISSION_PICKUP_LOCATION_ORDERS = 20;
  
  public function GetTable()
  {
      global $g_oMemberSession;
      global $g_dNow;
      
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
      //check member "can order" permission
      if (!$this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      //member has balance (or doesn't need one)?
      if (!$g_oMemberSession->CanOrder)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      $sNow = $g_dNow->format(DATABASE_DATE_FORMAT);
      
      //show active orders (not draft, closed or cancelled) that can the current user can participate in, or already has participated in.
      
      $sSQL =   " SELECT O.OrderID, CO.CoopOrderKeyID, CO.dStart, CO.dEnd, CO.dDelivery, CO.mMaxCoopTotal, CO.fMaxBurden, CO.mCoopTotal, CO.mProducerTotal, " .  
        " CO.fBurden, CO.mTotalDelivery, CO.CoordinatingGroupID, " . 
        $this->ConcatStringsSelect(Consts::PERMISSION_AREA_COOP_ORDERS, 'sCoopOrder') .
        " FROM T_CoopOrder CO LEFT JOIN T_Order O ON O.CoopOrderKeyID = CO.CoopOrderKeyID AND O.MemberID = " . $g_oMemberSession->MemberID .  
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_COOP_ORDERS) .
        " WHERE CO.nStatus = " . CoopOrder::STATUS_ACTIVE . 
        " AND CO.dStart <= ? " .
        " AND (CO.dEnd >= ? OR O.CoopOrderKeyID IS NOT NULL) " .
        " ORDER BY CO.dDelivery desc; ";

      $this->RunSQLWithParams( $sSQL, array($sNow, $sNow) );

      return $this->fetch();
  }
  
  //get all the permissions required for coordinators
  public function GetCoordPermissions($nCoordinatingGroupID)
  {
    $PermissionSet = new PermissionBridgeSet();
    
    $PermissionSet->DefinePermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
            Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    $PermissionSet->DefinePermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
            Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    $PermissionSet->DefinePermissionBridge(self::PERMISSION_ORDERS, Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
            Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    $PermissionSet->DefinePermissionBridge(self::PERMISSION_PRODUCTS, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS,
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $PermissionSet->DefinePermissionBridge(self::PERMISSION_EXPORT, Consts::PERMISSION_AREA_COOP_ORDERS, 
            Consts::PERMISSION_TYPE_EXPORT, Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    return $PermissionSet;
  }
  
  //this permission is checked against any specific pickup location record
  public function CheckPickupLocationCoordPermissions($nCoordinatingGroupID)
  {
    $PermissionSet = new PermissionBridgeSet();
    
    $bView = $PermissionSet->DefinePermissionBridge(self::PERMISSION_PICKUP_LOCATION_ORDERS, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    $PermissionSet = NULL;
    
    return $bView;
  }
  
  //this permission is checked against any specific producer record
  public function CheckProducerCoordPermissions($nCoordinatingGroupID)
  {
    $PermissionSet = new PermissionBridgeSet();
        
    $bEdit = $PermissionSet->DefinePermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    $bView = $PermissionSet->DefinePermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, $nCoordinatingGroupID, FALSE);
    
    return ($bEdit || $bView);
  }
  
}

?>
