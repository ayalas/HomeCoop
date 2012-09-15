<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class CoopOrderProducts  extends CoopOrderSubBase {
  
  const PERMISSION_COOP_ORDER_PRODUCT_EDIT = 100;
  const PERMISSION_COOP_ORDER_PRODUCT_VIEW = 101;
  
 public function LoadData()
 {
    global $g_oMemberSession;
    if (!$this->LoadCoopOrderData())
      return FALSE;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL =   " SELECT COPRD.ProductKeyID, P.ProducerKeyID, COPRD.nJoinedStatus, COPRD.mProducerPrice, COPRD.mCoopPrice, ". 
            " IfNUll(COPRD.mProducerTotal,0) mProducerTotal," . 
            " NUllIf(PRD.fQuantity,0) ProductQuantity, PRD.nItems ProductItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, " .
            " IfNull(COPRD.fTotalCoopOrder,0) fTotalCoopOrder, COPRD.fMaxUserOrder, COPRD.fMaxCoopOrder, IfNull(COPRD.fBurden,0) fBurden, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
           "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
          "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
          " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " . 
          " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCT_EDIT) != Consts::PERMISSION_SCOPE_COOP_CODE &&
        $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCT_VIEW) != Consts::PERMISSION_SCOPE_COOP_CODE)
            $sSQL .=  " AND P.CoordinatingGroupID IN ( " . implode(",", $g_oMemberSession->Groups) . ") ";
    $sSQL .= " ORDER BY PRD.nSortOrder; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
 
 
}

?>
