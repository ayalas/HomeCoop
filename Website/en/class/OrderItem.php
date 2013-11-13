<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faciliate some single order item related functionality
//currently there is no order item page, so this is very limited
class OrderItem extends SQLBase {
  
  const PROPERTY_ORDER_ITEM_ID = "OrderItemID"; 
  const PROPERTY_PRODUCT_ID = "ProductID";
  const PROPERTY_JOIN_TO_PRODUCT_ID = "JoinToProductID";
  const PROPERTY_JOIN_TO_PRODUCT_NAME = "JoinToProductName";
  const PROPERTY_UNJOINED_QUANTITY = "UnjoinedQuantity";
  const PROPERTY_JOINED_COOP_PRICE = "JoinedCoopPrice";
  const PROPERTY_JOINED_PRODUCER_PRICE = "JoinedProducerPrice";
  const PROPERTY_JOINED_ITEMS = "JoinedItems";
  const PROPERTY_JOINED_PRODUCT_ITEMS = "JoinedProductItems";
  const PROPERTY_PRODUCER_ID = "ProducerID";
  const PROPERTY_QUANTITY = "Quantity";
  const PROPERTY_COOP_TOTAL = "CoopTotal";
  const PROPERTY_PRODUCER_TOTAL = "ProducerTotal";
  const PROPERTY_MEMBER_LAST_QUANTITY = "MemberLastQuantity";
  const PROPERTY_MEMBER_MAX_FIX_QUANTITY_ADDITION = "MemberMaxFixQuantityAddition";
  const PROPERTY_MEMBER_COMMENTS = "MemberComments";
  const PROPERTY_PRODUCT_COOP_PRICE = "ProductCoopPrice";
  const PROPERTY_PRODUCT_PRODUCER_PRICE = "ProductProducerPrice";
  const PROPERTY_PRODUCT_QUANTITY = "ProductQuantity";
  const PROPERTY_PRODUCT_UNIT_ID = "ProductUnitID";
  const PROPERTY_PRODUCT_ITEMS = "ProductItems";
  const PROPERTY_PRODUCT_ITEM_QUANTITY = "ProductItemQuantity";
  const PROPERTY_PRODUCT_MAX_USER_ORDER = "ProductMaxUserOrder";
  const PROPERTY_PRODUCT_MAX_COOP_ORDER = "ProductMaxCoopOrder";
  const PROPERTY_PRODUCT_BURDEN = "ProductBurden";
  const PROPERTY_PRODUCT_TOTAL_COOP_ORDER_QUANTITY = "ProductTotalCoopOrderQuantity";
  const PROPERTY_ITEM_BURDEN = "Burden";
  
  const PROPERTY_STORAGE_AREA_ID = "StorageAreaID";
  const PROPERTY_STORAGE_AREA_BURDEN = "StorageAreaBurden";
  const PROPERTY_STORAGE_AREA_MAX_BURDEN = "StorageAreaMaxBurden";
 
  const PROPERTY_PRODUCT_PACKAGE_SIZE = "PackageSize";
  const PROPERTY_UNIT_INTERVAL = "UnitInterval";
  const PROPERTY_PRODUCT_NAME = "ProductName";
  const PROPERTY_PRODUCER_NAME = "ProducerName";
  const PROPERTY_UNIT_ABBREV = "UnitAbbrev";
  const PROPERTY_ITEM_UNIT_ABBREV = "ItemUnitAbbrev";
  const PROPERTY_ITEM_CHANGED_BY_COORD = "ChangedByCoordinator";
  
  const PROPERTY_INVALID_ENTRY = "InvalidEntry";
  const PROPERTY_VALIDATION_MESSAGE = "ValidationMessage";
  
  const PROPERTY_ORDERS_CHANGED = "OrdersChanged";
  const PROPERTY_VISIBLE = "Visible";
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_ORDER_ITEM_ID => 0,
        self::PROPERTY_PRODUCT_ID => 0,
        self::PROPERTY_PRODUCER_ID => 0,
        self::PROPERTY_QUANTITY => NULL,
        self::PROPERTY_JOIN_TO_PRODUCT_ID => NULL,
        self::PROPERTY_JOIN_TO_PRODUCT_NAME => NULL,
        self::PROPERTY_JOINED_PRODUCT_ITEMS => NULL,
        self::PROPERTY_UNJOINED_QUANTITY => NULL,
        self::PROPERTY_JOINED_COOP_PRICE => NULL,
        self::PROPERTY_JOINED_PRODUCER_PRICE => NULL,
        self::PROPERTY_JOINED_ITEMS => NULL,
        self::PROPERTY_COOP_TOTAL => 0,
        self::PROPERTY_PRODUCER_TOTAL => 0,
        self::PROPERTY_MEMBER_LAST_QUANTITY => NULL,
        self::PROPERTY_MEMBER_MAX_FIX_QUANTITY_ADDITION => NULL,
        self::PROPERTY_MEMBER_COMMENTS => NULL,
        self::PROPERTY_PRODUCT_COOP_PRICE => 0,
        self::PROPERTY_PRODUCT_PRODUCER_PRICE => 0,
        self::PROPERTY_PRODUCT_QUANTITY => NULL,
        self::PROPERTY_PRODUCT_UNIT_ID => 0,
        self::PROPERTY_PRODUCT_ITEMS => NULL,
        self::PROPERTY_PRODUCT_ITEM_QUANTITY => NULL,
        self::PROPERTY_PRODUCT_MAX_USER_ORDER => NULL,
        self::PROPERTY_PRODUCT_MAX_COOP_ORDER => NULL,
        self::PROPERTY_PRODUCT_BURDEN => NULL,
        self::PROPERTY_PRODUCT_TOTAL_COOP_ORDER_QUANTITY => 0,
        self::PROPERTY_ITEM_BURDEN => NULL,
        self::PROPERTY_PRODUCT_PACKAGE_SIZE => NULL,
        self::PROPERTY_UNIT_INTERVAL => NULL,
        self::PROPERTY_PRODUCT_NAME => NULL,
        self::PROPERTY_PRODUCER_NAME => NULL,
        self::PROPERTY_UNIT_ABBREV => NULL,
        self::PROPERTY_ITEM_UNIT_ABBREV => NULL,
        self::PROPERTY_INVALID_ENTRY => FALSE,
        self::PROPERTY_VALIDATION_MESSAGE => '',
        self::PROPERTY_ITEM_CHANGED_BY_COORD => FALSE,
        self::PROPERTY_ORDERS_CHANGED => NULL,
        self::PROPERTY_COORDINATING_GROUP_ID => 0,
        self::PROPERTY_VISIBLE => TRUE,
        self::PROPERTY_STORAGE_AREA_ID => NULL,
        self::PROPERTY_STORAGE_AREA_BURDEN => NULL,
        self::PROPERTY_STORAGE_AREA_MAX_BURDEN => NULL,
       );
  }
  
  //set the order items total fields according to quantity and product prices and burden
  public function SetTotals()
  {    
    if ($this->m_aData[self::PROPERTY_PRODUCT_QUANTITY] == NULL || $this->m_aData[self::PROPERTY_PRODUCT_QUANTITY] == 0)
      throw new Exception('OrderItem.SetTotals failed - product quantity is empty for product id ' . 
              $this->m_aData[self::PROPERTY_PRODUCT_ID]
              );
    $fJoinedQuantity = 0;
    $fUnjoinedQuantity = 0;
    if ( $this->m_aData[self::PROPERTY_JOINED_ITEMS] > 0)
    {
      if ($this->m_aData[self::PROPERTY_JOINED_PRODUCT_ITEMS] == NULL || $this->m_aData[self::PROPERTY_JOINED_PRODUCT_ITEMS] == 0)
        throw new Exception('OrderItem.SetTotals failed - joined product items is empty for product id ' . 
              $this->m_aData[self::PROPERTY_PRODUCT_ID]
              );
      
      $fJoinedQuantity = ($this->m_aData[self::PROPERTY_JOINED_ITEMS] / $this->m_aData[self::PROPERTY_JOINED_PRODUCT_ITEMS]);
      $fUnjoinedQuantity = ($this->m_aData[self::PROPERTY_UNJOINED_QUANTITY] / $this->m_aData[self::PROPERTY_PRODUCT_QUANTITY]);
    }
    else
    {
      $fJoinedQuantity = 0;
      $fUnjoinedQuantity = ($this->m_aData[self::PROPERTY_QUANTITY] / $this->m_aData[self::PROPERTY_PRODUCT_QUANTITY]);
    }

    //for producer price: without joined items
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] =  $fUnjoinedQuantity * $this->m_aData[self::PROPERTY_PRODUCT_PRODUCER_PRICE];
      
    $this->m_aData[self::PROPERTY_COOP_TOTAL] =  Rounding::Round($fJoinedQuantity * $this->m_aData[self::PROPERTY_JOINED_COOP_PRICE] +
       $fUnjoinedQuantity *  $this->m_aData[self::PROPERTY_PRODUCT_COOP_PRICE], ROUND_SETTING_ORDER_ITEM_COOP_TOTAL);
  }
  
  //get product allowed interval
  public function GetAllowedInterval()
  {
    if ($this->m_aData[self::PROPERTY_PRODUCT_UNIT_ID] == Consts::UNIT_ITEMS)
      return 1; //Items can only be sold in whole quantities of course
    
    if ($this->m_aData[self::PROPERTY_UNIT_INTERVAL] != NULL && $this->m_aData[self::PROPERTY_UNIT_INTERVAL] > 0)
      return $this->m_aData[self::PROPERTY_UNIT_INTERVAL];
    
    if ($this->m_aData[self::PROPERTY_PRODUCT_PACKAGE_SIZE] != NULL && $this->m_aData[self::PROPERTY_PRODUCT_PACKAGE_SIZE] > 0)
      return $this->m_aData[self::PROPERTY_PRODUCT_PACKAGE_SIZE];
    
    return $this->m_aData[self::PROPERTY_PRODUCT_QUANTITY];
  }
 
  //calculate an order item's "burden" based on the product's burden and the order item's quanitty
  public function CalculateBurden()
  {
     $this->m_aData[self::PROPERTY_ITEM_BURDEN] = 
             $this->m_aData[self::PROPERTY_PRODUCT_BURDEN] *
             ($this->m_aData[self::PROPERTY_QUANTITY]/$this->m_aData[self::PROPERTY_PRODUCT_QUANTITY]);     
     
     if ($this->m_aData[self::PROPERTY_ITEM_BURDEN] == NULL)
       $this->m_aData[self::PROPERTY_ITEM_BURDEN] = 0;
     
     return $this->m_aData[self::PROPERTY_ITEM_BURDEN];
  }        
}

?>
