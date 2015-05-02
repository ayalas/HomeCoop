<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//handles products grid and products table (for coop order products defaults)
class Products extends SQLBase {
  
  const PERMISSION_PRODUCTS = 100;
  
  public function GetTable()
  {
      global $g_oMemberSession;

      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
      $bEdit = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      if (!$bEdit && !$bView)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }

      $sSQL =   " SELECT PRD.ProductKeyID, PRD.ProducerKeyID, PRD.UnitKeyID, PRD.fUnitInterval, PRD.fMaxUserOrder, PRD.mProducerPrice, PRD.mCoopPrice, " .
                " PRD.fQuantity, PRD.nItems, PRD.nSortOrder, PRD.ItemUnitKeyID, PRD.fItemQuantity, PRD.fPackageSize,  " .
                " PRD.fBurden, UT.nFloatingPoint, UT.MeasureKeyID, " . 
                      $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_SPECIFICATION, 'sSpec') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNITS, 'sUnit') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNITS, 'sItemUnit') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
                "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_MEASURES, 'sMeasure');

      $sSQL .=  " , PRD.bDisabled, P.CoordinatingGroupID " .
                " FROM T_Product PRD INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " . 
                " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
                " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
                $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_SPECIFICATION, Consts::PERMISSION_AREA_PRODUCTS) .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_UNITS) .
                $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ITEM_UNITS) .
                $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
                $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_MEASURES, Consts::PERMISSION_AREA_UNITS);
     
      if ( ($this->GetPermissionScope(self::PERMISSION_COORD) != Consts::PERMISSION_SCOPE_COOP_CODE)  &&
           ($this->GetPermissionScope(self::PERMISSION_VIEW) != Consts::PERMISSION_SCOPE_COOP_CODE) )
          $sSQL .=  " WHERE P.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";

      $this->RunSQL( HomeCoopPager::Process($sSQL, " ORDER BY bDisabled ASC, nSortOrder ASC,sProduct ASC ") );

      return $this->fetch();
  }
  
  //get products that come in large package and can be joined
  public function GetJoinToProductList($CurrentProductID)
  {
    global $g_oMemberSession;
                
    if ( !$this->AddPermissionBridge(self::PERMISSION_PRODUCTS, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }

    $sSQL = " SELECT PRD.ProductKeyID, " .
       $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
         " FROM T_Product PRD INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " .
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
         " WHERE PRD.nItems > 1 AND PRD.bDisabled = 0 ";
    if ($CurrentProductID > 0)
      $sSQL .= " AND PRD.ProductKeyID != " . $CurrentProductID;
    if ( $this->GetPermissionScope(self::PERMISSION_PRODUCTS) == Consts::PERMISSION_SCOPE_GROUP_CODE )
      $sSQL .=  " AND P.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";
    $sSQL .= " ORDER BY PRD.nSortOrder ,PRD_S.sString; ";
    
    $this->RunSQL( $sSQL );
    
    return $this->fetchAllKeyPair();
  }
  
  //get list of products that are not yet in the given coop order - and hence their defaults can be retrieved
  public function GetListForCoopOrder($nProducerID, $nCurrentProductID, $nCoopOrderID)
    {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
      if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCTS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      if ($nCoopOrderID <= 0 )
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return NULL;
      }
      
      if ($nCurrentProductID === NULL)
        $nCurrentProductID = 0;
      
      if ($nProducerID === NULL)
        $nProducerID = 0;

      $sSQL =   " SELECT PRD.ProductKeyID, " . 
               $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
        " FROM T_Product PRD INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " .
        " LEFT JOIN T_CoopOrderProduct COPRD ON COPRD.ProductKeyID = PRD.ProductKeyID AND COPRD.CoopOrderKeyID = " 
        .  $nCoopOrderID . " " .
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
        " WHERE ( (COPRD.ProductKeyID IS NULL AND PRD.bDisabled = 0) OR (PRD.ProductKeyID = " . $nCurrentProductID . " ) )";
     
      if ($nProducerID > 0)
        $sSQL .=  " AND P.ProducerKeyID = " . $nProducerID;
      
      if ( $this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE )
          $sSQL .=  " AND P.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";

      $sSQL .= " ORDER BY PRD.nSortOrder ,PRD_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetchAllKeyPair();
  }
}

?>
