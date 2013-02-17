<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//base class for coop order pickup location sub tables - loads general data and checks permissions
class CoopOrderPickupLocationSubBase extends CoopOrderSubBase {
   
  const PERMISSION_COOP_ORDER_PICKUP_LOCATION_SUBTABLE_VIEW = 300;
  const PROPERTY_PICKUP_LOCATION_NAME = "PickupLocationName";
  const PROPERTY_PICKUP_LOCATION_COORD_GROUP_ID = "PickupLocationCoordGroupID";
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_PICKUP_LOCATION_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID => 0,
                            self::PROPERTY_PICKUP_LOCATION_COORD_GROUP_ID => 0
                            );
  }
  
  protected function LoadCoopOrderPickupLocationData($nPermissionArea)
  {
    if ( $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID] <=0 ||
         $this->m_aData[self::PROPERTY_COOP_ORDER_ID] <= 0
       )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_SUBTABLE_VIEW, 
            $nPermissionArea, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    $sSQL = "SELECT PL.CoordinatingGroupID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
            " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = COPL.PickupLocationKeyID " .
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
            " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " AND COPL.PickupLocationKeyID = " . $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID] . ";";
    
    $this->RunSQL($sSQL);
    
    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    //add group check by pickup location
    if (!$this->SetRecordGroupID(self::PERMISSION_COOP_ORDER_PICKUP_LOCATION_SUBTABLE_VIEW,$rec["CoordinatingGroupID"], FALSE))
    {
       $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
       return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COORD_GROUP_ID] = $rec["CoordinatingGroupID"];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_NAME] = $rec["sPickupLocation"];
    
    return TRUE;
  }
  
  
}

?>
