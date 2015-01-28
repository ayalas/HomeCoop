<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//used in partialorder.php popup screen (opened from coproducts.php)
//used to manage large packages orders that the cooperative allows ordering parts of,
//in case they complete eachother to whole packages
//the screen only displays the information and suggests coorections
//actual coorection is to be performed manually by the coordinator
class PartialOrders extends SQLBase {
  
  const PROPERTY_COOP_ORDER_ID = "CoopOrderID";
  const PROPERTY_PRODUCT_ID = "ProductID";
  const PROPERTY_PRODUCT_NAME = "ProductName";
  const PROPERTY_PRODUER_NAME = "ProducerName";
  const PROPERTY_TOTAL_ORDER = "TotalOrder";
  const PROPERTY_PACKAGE_SIZE = "PackageSize";
  const PROPERTY_QUANTITY = "Quantity";
  const PROPERTY_IS_PARTIAL = "IsPartial";
  const PROPERTY_DELETED_ITEMS = "DeletedItems";
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_COOP_ORDER_ID => 0,
        self::PROPERTY_PRODUCT_ID => 0,
        self::PROPERTY_PRODUCT_NAME => NULL,
        self::PROPERTY_PRODUER_NAME => NULL,
        self::PROPERTY_TOTAL_ORDER => 0,
        self::PROPERTY_PACKAGE_SIZE => NULL,
        self::PROPERTY_QUANTITY => 0,
        self::PROPERTY_IS_PARTIAL => FALSE,
        self::PROPERTY_DELETED_ITEMS => NULL,
       );
  }
  
  protected function CheckAccess()
  {
    return $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
    
  public function LoadData()
  {
    if (!$this->CheckAccess())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    if ($this->m_aData[self::PROPERTY_COOP_ORDER_ID] <= 0 || $this->m_aData[self::PROPERTY_PRODUCT_ID] <= 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return NULL;
    }
    
    if (!$this->LoadProductData())
      return NULL;
    
    $this->LoadDeletedData();
    
    $sSQL = " SELECT O.OrderID, M.sName MemberName, OI.fOriginalQuantity, OI.fMaxFixQuantityAddition, OI.fQuantity, O.dCreated " . 
            " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " .
            " INNER JOIN T_OrderItem OI ON OI.OrderID = O.OrderID " .
            " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " AND OI.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] .
            " ORDER BY O.dCreated desc, O.dModified desc; ";
    
    $this->RunSQL($sSQL);
        
    return $this->fetch();
  }
  
  protected function LoadProductData()
  {
    $sSQL = " SELECT PRD.UnitKeyID, PRD.fUnitInterval, PRD.ProductKeyID, IfNull(COPRD.fTotalCoopOrder,0) fTotalCoopOrder,PRD.fQuantity, PRD.fPackageSize,  " . 
               $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
            ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            " FROM T_CoopOrderProduct COPRD " .
            " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
            " INNER JOIN T_Producer P ON PRD.ProducerKeyID = P.ProducerKeyID " .
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
            " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " AND COPRD.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] . ";";
    
    $this->RunSQL($sSQL);
        
    $recProduct = $this->fetch();
    
    if (!$recProduct)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_IS_PARTIAL] = 
        Product::AllowsPartialOrders($recProduct["UnitKeyID"], $recProduct["fQuantity"], $recProduct["fUnitInterval"], $recProduct["fPackageSize"]);

    $this->m_aData[self::PROPERTY_PRODUCT_NAME] = $recProduct["sProduct"];
    $this->m_aData[self::PROPERTY_PRODUER_NAME] = $recProduct["sProducer"];
    $this->m_aData[self::PROPERTY_TOTAL_ORDER] = $recProduct["fTotalCoopOrder"];
    if ($recProduct["fPackageSize"] == NULL)
      $this->m_aData[self::PROPERTY_PACKAGE_SIZE] = $recProduct["fQuantity"];
    else
      $this->m_aData[self::PROPERTY_PACKAGE_SIZE] = $recProduct["fPackageSize"];
    $this->m_aData[self::PROPERTY_QUANTITY] = $recProduct["fQuantity"];
    
    return TRUE;
  }
  
  protected function LoadDeletedData()
  {
    $sSQL = " SELECT O.OrderID, M.sName MemberName, OI.fOriginalQuantity, OI.fMaxFixQuantityAddition, OI.fQuantity, O.dCreated " . 
            " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " .
            " INNER JOIN T_OrderItem_Deleted OI ON OI.OrderID = O.OrderID " .
            " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " AND OI.ProductKeyID = " . $this->m_aData[self::PROPERTY_PRODUCT_ID] .
            " ORDER BY O.dCreated desc, O.dModified desc; ";
    
    $this->RunSQL($sSQL);
    $this->m_aData[self::PROPERTY_DELETED_ITEMS] = $this->fetchAll();
  }
  
}

?>
