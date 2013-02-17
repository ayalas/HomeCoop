<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//gets data from coop order pickup location products table
//the table is updated in CoopOrderCalculate, when actual member orders are placed
class CoopOrderPickupLocationProducts extends CoopOrderPickupLocationSubBase {
  
  const PERMISSION_COOP_ORDER_PRODUCT_VIEW = 400;
  const PERMISSION_COOP_ORDER_PRODUCT_MODIFY = 401;
  
  public function __construct()
  {
     parent::__construct();
  }
  
  public function LoadData()
 {
    global $g_oMemberSession;
    
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    if (!$this->LoadCoopOrderPickupLocationData(Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS))
      return NULL;
    
    //this bridges are not mandatory. If the user has the permission, it might actually limit hir access instead of broadening it - showing only
    //producers ze has access to
    $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_MODIFY, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $sSQL =   " SELECT COPLPRD.ProductKeyID, P.ProducerKeyID, COPLPRD.mCoopTotal, COPLPRD.mProducerTotal, COPLPRD.fTotalCoopOrder, " . 
            " NUllIf(PRD.fQuantity,0) ProductQuantity, PRD.nItems ProductItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, " .
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
          "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
          " FROM T_CoopOrderPickupLocationProduct COPLPRD INNER JOIN T_Product PRD " .
          " ON PRD.ProductKeyID =  COPLPRD.ProductKeyID " .
          " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE COPLPRD.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID] .
          " AND COPLPRD.PickupLocationKeyID = " . $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW) == Consts::PERMISSION_SCOPE_GROUP_CODE &&
        $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCT_MODIFY) == Consts::PERMISSION_SCOPE_GROUP_CODE)
        $sSQL .= " AND P.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . " ) ";
    
    $sSQL .= " AND (COPLPRD.fTotalCoopOrder > 0 OR COPLPRD.mProducerTotal > 0 ) " .
             " ORDER BY PRD.nSortOrder; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
    
 }
}

?>
