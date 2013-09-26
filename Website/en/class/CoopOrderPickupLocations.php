<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//coop order pickup locations, according to the user permissions
class CoopOrderPickupLocations extends CoopOrderSubBase {

 const PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT = 100;
 const PERMISSION_COOP_ORDER_PICKUP_LOCATION_VIEW = 101;
 const PERMISSION_SUMS = 102;
 
 public function __construct()
 {
   parent::__construct();
 } 

 public function LoadData()
 {    
    global $g_oMemberSession;
    
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    //check sums permission
    $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS,
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
        
    $sSQL =   " SELECT COPL.PickupLocationKeyID, COPL.fMaxBurden, IfNull(COPL.fBurden,0) fBurden, COPL.mMaxCoopTotal ,  COPL.mCoopTotal , PL.CoordinatingGroupID," .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
          ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, 'sAddress') .
          " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_PickupLocation PL ON COPL.PickupLocationKeyID = PL.PickupLocationKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
          " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT) == Consts::PERMISSION_SCOPE_GROUP_CODE)
      $sSQL .=  " AND PL.CoordinatingGroupID IN (0, " . implode(",", $g_oMemberSession->Groups) . ") ";
    $sSQL .= " ORDER BY PL_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
 
 //called from both order screen and home page active orders control
 public function LoadList($nCoopOrderID, $MemberID)
 {
    global $g_oMemberSession;
   
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    $bOwnOrder = FALSE;
   
    if ($g_oMemberSession->MemberID == $MemberID)
    {
      if (!$this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
           Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      $bOwnOrder = TRUE;
    }
    else if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }

    $sSQL =   " SELECT COPL.PickupLocationKeyID, COPL.fMaxBurden, COPL.fBurden, COPL.mMaxCoopTotal, COPL.fMaxStorageBurden, COPL.fStorageBurden, " . 
            " PL.sExportFileName, COPL.mCoopTotal, PL.CoordinatingGroupID," .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
          " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_PickupLocation PL ON COPL.PickupLocationKeyID = PL.PickupLocationKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
          " WHERE COPL.CoopOrderKeyID = " . $nCoopOrderID;
    if (!$bOwnOrder && $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_EDIT) != Consts::PERMISSION_SCOPE_COOP_CODE)
    {
      $sSQL .= " AND PL.CoordinatingGroupID IN (0, " . implode(",", $g_oMemberSession->Groups) . ") "; 
    }
      
    $sSQL .= " ORDER BY PL_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
 
}

?>
