<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//handles catalog.php data
//catalog.php only calls this class when not reading result from cache
class ProductCatalog extends SQLBase
{
  public function GetTable()
  {
    //this is the only permission check needed here (has any permission)
    if (isset($g_oMemberSession))
    {
        global $g_oMemberSession;
        if (!$g_oMemberSession->IsLoggedIn)
        {
          $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
          return FALSE; 
        }
    }
    
    $sSQL =   " SELECT PRD.ProductKeyID, PRD.fQuantity, PRD.nItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, P.ProducerKeyID , " . 
              " PRD.mProducerPrice, PRD.mCoopPrice, " . 
              " P.CoordinatingGroupID, PRD.sImage1FileName, PRD.sImage2FileName,PRD.UnitKeyID, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_SPECIFICATION, 'sSpec') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNITS, 'sUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNITS, 'sItemUnit') .
            "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .              
          " FROM T_Product PRD " . 
          " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " . 
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_SPECIFICATION, Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ITEM_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE PRD.bDisabled = 0 ORDER BY PRD.nSortOrder ,PRD_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
  }
}
?>
