<?php


if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//joins products that are defined as joinable into other, larger-packaged and cost saving products
class CoopOrderJoinProducts extends SQLBase {

  const PROPERTY_COOP_ORDER_ID = "CoopOrderID";
  const PROPERTY_ORDERS_UPDATED = "OrdersUpdated";
  
  
  const PERMISSION_JOIN_PRODUCTS = 11;
  
  protected $m_aProducts = NULL;
  protected $m_aItems = NULL;
  
  protected $m_aProductsForUpdate = array();
  
  protected $m_oCalculate = NULL;
  
  public function __construct($nCoopOrderID)
  {
    $this->m_aData = array( self::PROPERTY_COOP_ORDER_ID => $nCoopOrderID,  
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_ORDERS_UPDATED => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0
                            );
  }
  
 
  
  protected function CheckPermission()
  {
    if ($this->HasPermission(self::PERMISSION_JOIN_PRODUCTS))
        return TRUE;
    
    return $this->AddPermissionBridge(self::PERMISSION_JOIN_PRODUCTS, Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, 
         Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
  }

  public function Join()
  {
    if (!$this->CheckPermission())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
        
    //see which products can be joined
    if (!$this->GetProductsForJoining())
      return TRUE; //nothing requires joining
    
    $bUpdate = FALSE;
    
    $this->m_aData[self::PROPERTY_ORDERS_UPDATED] = array();
    $this->m_oCalculate = new CoopOrderCalculate( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] );
    
    try
    {
      $this->BeginTransaction();
      
      //loop through products for joining
      foreach($this->m_aProducts as $aProduct)
      {
        if ($this->JoinProduct($aProduct))
        {
          $this->m_aProductsForUpdate[] = $aProduct["ProductKeyID"];
          $this->m_aProductsForUpdate[] = $aProduct["JoinToProductKeyID"];
          $bUpdate = TRUE;
        }
      }

      //calculations, once, after all unjoinings
      if ($bUpdate)
      {
        $this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] = TRUE;
        $this->UpdateCoopOrder(); 
        
      }
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    return TRUE;
  }
  
  public function Unjoin()
  {    
    if (!$this->CheckPermission())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    //first get products that are joined
    if (!$this->GetProductsForJoining())
      return TRUE; //nothing requires unjoining
    
    $bUpdate = FALSE;
    
    $this->m_aData[self::PROPERTY_ORDERS_UPDATED] = array();
    $this->m_oCalculate = new CoopOrderCalculate( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] );
    
    try
    {
      $this->BeginTransaction();
    
      //for each product changed, rollback items
      foreach($this->m_aProducts as $aProduct)
      {
        if ($this->UnjoinProduct($aProduct))
        {
          $this->m_aProductsForUpdate[] = $aProduct["ProductKeyID"];
          $this->m_aProductsForUpdate[] = $aProduct["JoinToProductKeyID"];
          $bUpdate = TRUE;
        }
      }

      //calculations, once, after all unjoinings
      if ($bUpdate)
      {
        $this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] = FALSE;

        $this->UpdateCoopOrder();
      }
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    return TRUE;
  }
  
  protected function GetProductsForJoining()
  { //mCoopPrice, mProducerPrice
    //get the products and whether joining can occur for them
    $sSQL = " SELECT PRD.ProductKeyID,PRD.JoinToProductKeyID, JPRD.nItems, SUM(OI.fQuantity) TotalQuantity,  " .
            " JCOPRD.mCoopPrice JoinedCoopPrice, JCOPRD.mProducerPrice JoinedProducerPrice, " .
            " COPRD.mCoopPrice ProductCoopPrice, COPRD.mProducerPrice ProductProducerPrice, PRD.fQuantity ProductQuantity " .
            " FROM T_Order O " .
            " INNER JOIN T_OrderItem OI ON OI.OrderID = O.OrderID " .
            " INNER JOIN T_Product PRD ON PRD.ProductKeyID = OI.ProductKeyID " .
            " INNER JOIN T_Product JPRD ON PRD.JoinToProductKeyID = JPRD.ProductKeyID " .
            " INNER JOIN T_CoopOrderProduct COPRD ON COPRD.CoopOrderKeyID = O.CoopOrderKeyID AND COPRD.ProductKeyID = PRD.ProductKeyID " .
            " INNER JOIN T_CoopOrderProduct JCOPRD ON JCOPRD.CoopOrderKeyID = O.CoopOrderKeyID AND JCOPRD.ProductKeyID = JPRD.ProductKeyID " .
            " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " GROUP BY PRD.ProductKeyID, PRD.JoinToProductKeyID, JPRD.nItems, JCOPRD.mCoopPrice, JCOPRD.mProducerPrice, " .
            " COPRD.mCoopPrice, COPRD.mProducerPrice " .
            " HAVING TotalQuantity >= JPRD.nItems;";
    
    $this->RunSQL($sSQL);
    $this->m_aProducts = $this->fetchAll();
    if (!$this->m_aProducts)
      return FALSE;
    return TRUE;
  }
  
  protected function JoinProduct( &$aProduct )
  {
    //updates:
    //OI fUnjoinedQuantity
    //OI nJoinedItems
    //OI mCoopPrice
    //OI mProducerPrice
    
    //updating of order items is done this way:
    //whenever a package is completed, go back to all items participating and update them
    
    $bUpdated = FALSE;
    
    $this->GetProductItems($aProduct["ProductKeyID"]);
    
    $nCountItems = count($this->m_aItems);
    
    $fTotalUnjoinedQuantity = 0;

    //update items
    for( $i=0 ; $i < $nCountItems; $i++)
    {
      $fTotalUnjoinedQuantity += $this->m_aItems[$i]["fQuantity"];
      
      if ($fTotalUnjoinedQuantity >= $aProduct["nItems"])
      {
        $bUpdated = TRUE;
        //get leftover
        $this->m_aItems[$i]["fUnjoinedQuantity"] = $fTotalUnjoinedQuantity % $aProduct["nItems"];
        $fTotalUnjoinedQuantity = $this->m_aItems[$i]["fUnjoinedQuantity"];
        $this->m_aItems[$i]["nJoinedItems"] = $this->m_aItems[$i]["fQuantity"] - $this->m_aItems[$i]["fUnjoinedQuantity"];
        $this->UpdateItem($i, $aProduct);
        
        //go back and update each order item that has unjoined quantity
        for ($b = $i - 1; $b >= 0; $b--)
        {
          if ($this->m_aItems[$b]["fUnjoinedQuantity"] > 0)
          {
            $this->m_aItems[$b]["nJoinedItems"] = $this->m_aItems[$b]["fQuantity"];
            $this->m_aItems[$b]["fUnjoinedQuantity"] = 0;
            $this->UpdateItem($b, $aProduct);
          }
          else //when unjoined is zero, it means operation has ended
            break;
        }
      }
      else
      {
        $this->m_aItems[$i]["fUnjoinedQuantity"] = $this->m_aItems[$i]["fQuantity"];
      }
    }
    
    return $bUpdated;
  }
  
  protected function UnjoinProduct( &$aProduct )
  {
    //updates:
    //OI fUnjoinedQuantity
    //OI nJoinedItems
    //OI mCoopPrice
    //OI mProducerPrice
    
    //updating of order items is done this way:
    //whenever a package is completed, go back to all items participating and update them
    
    $bUpdated = FALSE;
    
    $this->GetProductItems($aProduct["ProductKeyID"]);
    
    $nCountItems = count($this->m_aItems);
    
    //update items
    for( $i=0 ; $i < $nCountItems; $i++)
    {      
      if ($this->m_aItems[$i]["nJoinedItems"] > 0)
      {
        $bUpdated = TRUE;
        //get leftover
        $this->m_aItems[$i]["fUnjoinedQuantity"] = $this->m_aItems[$i]["fQuantity"];
        $this->m_aItems[$i]["nJoinedItems"] = 0;
        $this->UpdateItem($i, $aProduct);
      }
    }
    
    return $bUpdated;
  }
  
  protected function GetProductItems( $ProductID )
  {
    $sSQL = " SELECT O.OrderID, OI.OrderItemID, OI.fQuantity, OI.fUnjoinedQuantity, OI.nJoinedItems " .
            " FROM T_Order O INNER JOIN T_OrderItem OI ON O.OrderID = OI.OrderID " .
            " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .
            " AND OI.ProductKeyID = " . $ProductID .
            " ORDER BY O.dCreated, O.OrderID;";
    
    $this->RunSQL($sSQL);
    $this->m_aItems = $this->fetchAll();
  }
  
  protected function UpdateItem( $nIndex, &$aProduct )
  {
    //updates:
    //OI fUnjoinedQuantity
    //OI nJoinedItems
    //OI mCoopPrice
    //OI mProducerPrice
    
    $oOrderItem = new OrderItem;
    $oOrderItem->ProductQuantity = $aProduct["ProductQuantity"];
    $oOrderItem->ProductID = $aProduct["ProductKeyID"];
    $oOrderItem->JoinedItems = $this->m_aItems[$nIndex]["nJoinedItems"];
    $oOrderItem->UnjoinedQuantity = $this->m_aItems[$nIndex]["fUnjoinedQuantity"];
    $oOrderItem->JoinedProductItems = $aProduct["nItems"];
    $oOrderItem->JoinedProducerPrice = $aProduct["JoinedProducerPrice"];
    $oOrderItem->JoinedCoopPrice = $aProduct["JoinedCoopPrice"];
    $oOrderItem->Quantity = $this->m_aItems[$nIndex]["fQuantity"];
    $oOrderItem->ProductProducerPrice = $aProduct["ProductProducerPrice"];
    $oOrderItem->ProductCoopPrice = $aProduct["ProductCoopPrice"];
    $oOrderItem->SetTotals();
    
    $sSQL = " UPDATE T_OrderItem SET fUnjoinedQuantity = ?, nJoinedItems = ? , " .
           " mCoopPrice = ?, mProducerPrice = ? " .
           " WHERE OrderItemID = " . $this->m_aItems[$nIndex]["OrderItemID"] . ';';

    $this->RunSQLWithParams($sSQL, array( $oOrderItem->UnjoinedQuantity,
       $oOrderItem->JoinedItems, $oOrderItem->CoopTotal,$oOrderItem->ProducerTotal
       ) );
    
    $this->AddOrderAsUpdated($this->m_aItems[$nIndex]["OrderID"]);
  }

  protected function UpdateCoopOrder()
  {
    //updates:
    //COPRD nJoinedStatus
    //COPRD fTotalCoopOrder
    //JCOPRD nJoinedStatus
    //JCOPRD fTotalCoopOrder
    //O mCoopTotal, mProducerTotal
    //O mCoopFee, mCoopTotalIncFee
    //CO mCoopTotal, mProducerTotal, mTotalDelivery, bHasJoinedProducts
    //COP mProducerTotal, mTotalDelivery
    //COPL mCoopTotal

    $this->m_oCalculate->HasJoinedProducts = $this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS];
    $this->m_oCalculate->ProductsListToCalculate = implode(",", $this->m_aProductsForUpdate);
    $this->m_oCalculate->OrdersListToCalculate = implode(",", $this->m_aData[self::PROPERTY_ORDERS_UPDATED]);
    
    //load coop order fee values    
    $sSQL =   " SELECT CO.mCoopFee, CO.mSmallOrder, CO.mSmallOrderCoopFee, CO.fCoopFee " .
              " FROM T_CoopOrder CO " . 
              " WHERE CO.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    $this->m_oCalculate->CoopFee = $rec["mCoopFee"];
    $this->m_oCalculate->SmallOrder = $rec["mSmallOrder"];
    $this->m_oCalculate->SmallOrderCoopFee = $rec["mSmallOrderCoopFee"];
    $this->m_oCalculate->CoopFeePercent = $rec["fCoopFee"];   
    
    $this->m_oCalculate->Run();
  }
 
  protected function AddOrderAsUpdated($OrderID)
  {
    if (!array_key_exists($OrderID, $this->m_aData[self::PROPERTY_ORDERS_UPDATED]))
      $this->m_aData[self::PROPERTY_ORDERS_UPDATED][$OrderID] = $OrderID;
  }
}
?>