<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;


//gets data from coop order pickup location producers table
//the table is updated in CoopOrderCalculate, when actual member orders are placed
class CoopOrderPickupLocationProducers extends CoopOrderPickupLocationSubBase {
  
  const PERMISSION_COOP_ORDER_PRODUCER_VIEW = 400;
  const PERMISSION_COOP_ORDER_PRODUCER_MODIFY = 401;
  
  public function __construct()
  {
     parent::__construct();
  }
 
  public function LoadData()
 {
    global $g_oMemberSession;
    
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    if (!$this->LoadCoopOrderPickupLocationData(Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS))
      return NULL;
    
    //this bridges are not mandatory. If the user has the permission, it might actually limit hir access instead of broadening it - showing only
    //producers ze has access to
    $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_MODIFY, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $sSQL =   " SELECT COPLP.ProducerKeyID, COPLP.mCoopTotal, COPLP.mProducerTotal,COP.mTotalDelivery, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          " FROM T_CoopOrderPickupLocationProducer COPLP INNER JOIN T_Producer P ON COPLP.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_CoopOrderProducer COP ON COP.CoopOrderKeyID = COPLP.CoopOrderKeyID AND COP.ProducerKeyID = COPLP.ProducerKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          " WHERE COPLP.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] .
          " AND COPLP.PickupLocationKeyID = " . $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW) == Consts::PERMISSION_SCOPE_GROUP_CODE &&
        $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_MODIFY) == Consts::PERMISSION_SCOPE_GROUP_CODE)
            $sSQL .= " AND P.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . " ) ";
    $sSQL .= " ORDER BY P_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
    
 }
}

?>
