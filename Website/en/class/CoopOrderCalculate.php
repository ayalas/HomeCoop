<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//recalculates burden and summaries for the given cooperative order and its sub records 
//(pickup locations, producers, products, etc.)
//all calls are and should always be inside a transaction with the triggering action
class CoopOrderCalculate extends SQLBase {
  
  const PROPERTY_TOTAL_BURDEN = "TotalBurden";
  const PROPERTY_TOTAL_COOP = "TotalCoop";
  const PROPERTY_TOTAL_PRODUCERS = "TotalProducers";
  const PROPERTY_TOTAL_DELIVERY = "TotalDelivery";
  const PROPERTY_PRODUCTS_LIST_TO_CALCULATE = "ProductsListToCalculate";
  const PROPERTY_ORDERS_LIST_TO_CALCULATE = "OrdersListToCalculate";
  
  protected $m_recPickupLocations = NULL;
  protected $m_aPickupLocationsFees = NULL;
  protected $m_recProducers = NULL;
  protected $m_recProducts = NULL;
  protected $m_recPickupLocsProducts = NULL;
  protected $m_recPickupLocsProducers = NULL;
  protected $m_bCalculateProducersOnly = FALSE;
    
  public function __construct($nCoopOrderID)
  {
    $this->m_aData = array(CoopOrder::PROPERTY_ID => $nCoopOrderID,
        self::PROPERTY_TOTAL_BURDEN => 0,
        self::PROPERTY_TOTAL_COOP => 0,
        self::PROPERTY_TOTAL_PRODUCERS => 0,
        self::PROPERTY_TOTAL_DELIVERY => 0,
        self::PROPERTY_PRODUCTS_LIST_TO_CALCULATE => NULL,
        self::PROPERTY_ORDERS_LIST_TO_CALCULATE => NULL,
        CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => NULL,
        CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE => NULL,
        CoopOrder::PROPERTY_SMALL_ORDER => NULL,
        CoopOrder::PROPERTY_COOP_FEE => NULL,
        CoopOrder::PROPERTY_COOP_FEE_PERCENT => NULL
        );
    
    if ($nCoopOrderID == 0)
      throw new Exception('Error in CoopOrderCalculate.Run: ID provided is 0');
  }
  
  //recalcualte entire coop order
  public function Run()
  {
    global $g_oError;

    $this->CalculateProducts();

    $this->CalculateOrders();

    $this->CalculatePickupLocs(FALSE);

    $this->CalculateProducers();

    $this->UpdateCoopOrder();

  }
  
  protected function CalculateProducts()
  {   
    $sSQL =   " SELECT COPRD.ProductKeyID, PRD.nItems, SUM(OI.mCoopPrice) mCoopTotal, SUM(OI.mProducerPrice) mProducerTotal,COPRD.mCoopPrice, COPRD.mProducerPrice, ". 
          " SUM(OI.fQuantity) as SumQuantity, SUM(OI.fUnjoinedQuantity) as TotalCoopOrder, (SELECT SUM(OI_J.nJoinedItems) " . 
                          " FROM T_Order O_J INNER JOIN T_OrderItem OI_J ON OI_J.OrderId = O_J.OrderID " .
                          " WHERE OI_J.ProductKeyID = JPRD.ProductKeyID AND O_J.CoopOrderKeyID = O.CoopOrderKeyID ) as TotalJoined " . 
          " FROM T_CoopOrderProduct COPRD INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
          " LEFT JOIN T_Order O ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID  " .
          " LEFT JOIN T_OrderItem OI ON O.OrderID = OI.OrderID AND OI.ProductKeyID = COPRD.ProductKeyID " . 
          " LEFT JOIN T_Product JPRD ON JPRD.JoinToProductKeyID = COPRD.ProductKeyID " .
          " WHERE COPRD.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID];
    if ($this->m_aData[self::PROPERTY_PRODUCTS_LIST_TO_CALCULATE] != NULL)
      $sSQL .= " AND COPRD.ProductKeyID IN (" . $this->m_aData[self::PROPERTY_PRODUCTS_LIST_TO_CALCULATE] . " ) ";
    $sSQL .=  " GROUP BY COPRD.ProductKeyID, PRD.nItems, COPRD.mProducerPrice;";

    $this->RunSQL( $sSQL );

    $this->m_recProducts = $this->fetch();
    
    while($this->m_recProducts)
    {
      $this->UpdateProduct();
      
      $this->m_recProducts = $this->fetch();
    }
  }
  
  //causes to update orders: changes in products prices, un/joining of products, delivery costs, coop fees
  protected function CalculateOrders()
  {
    if ($this->m_aData[self::PROPERTY_ORDERS_LIST_TO_CALCULATE] == NULL)
      return;
    
    $sSQL =  " SELECT O.OrderID, SUM(OI.mProducerPrice) mProducerTotal, SUM(OI.mCoopPrice) mCoopTotal " .
             " FROM T_Order O INNER JOIN T_OrderItem OI ON O.OrderID = OI.OrderID " .
             " WHERE O.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] .
             " AND O.OrderID IN (" . $this->m_aData[self::PROPERTY_ORDERS_LIST_TO_CALCULATE] . " ) " .
             " GROUP BY O.OrderID;";
    $this->RunSQL( $sSQL );
    
    $recOrder = $this->fetch();
    
    while($recOrder)
    {
      $oOrder = new Order();
      
      $oOrder->CoopTotal = Rounding::Round( $recOrder["mCoopTotal"], ROUND_SETTING_ORDER_COOP_TOTAL );

      $oOrder->CoopFee = $this->m_aData[CoopOrder::PROPERTY_COOP_FEE];
      $oOrder->SmallOrder = $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER];
      $oOrder->SmallOrderCoopFee = $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE];
      $oOrder->CoopFeePercent = $this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT];
      $oOrder->CalculateCoopFee();

      //Save Order details
      $sSQL =   " UPDATE T_Order SET mCoopTotal = ?, mCoopTotalIncFee = ?, mProducerTotal = ?, mCoopFee = ? WHERE OrderID = " . 
           $recOrder["OrderID"] . ';';
      
      $this->m_bUseSecondSqlPreparedStmt = TRUE;

      $this->RunSQLWithParams( $sSQL, array(
              $oOrder->CoopTotal, 
              $oOrder->CoopTotalIncludingFee,
              $recOrder["mProducerTotal"], 
              $oOrder->OrderCoopFee));
      
      $this->m_bUseSecondSqlPreparedStmt = FALSE;
      
      $recOrder = $this->fetch();
    }
  }
  
  //called when changing a pickup location or placing/updating an order
  //updates also pickup location sub tables: products and producers
  public function CalculatePickupLocs($bCalculateAll)
  {
    $sJoin = NULL;
    if ($bCalculateAll)
      $sJoin = " LEFT JOIN ";
    else
      $sJoin = " INNER JOIN ";
    
    //get total fee for each pickup location as a key-pair array
    
    $sSQL = " SELECT COPL.PickupLocationKeyID, IfNull(SUM(O.mCoopFee),0) TotalFee " .
            " FROM T_CoopOrderPickupLocation COPL" .
          $sJoin . " T_Order O ON O.CoopOrderKeyID = COPL.CoopOrderKeyID AND COPL.PickupLocationKeyID = O.PickupLocationKeyID " .
          " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] .
          " GROUP BY COPL.PickupLocationKeyID; ";
    
    $this->RunSQL( $sSQL );
    $this->m_aPickupLocationsFees = $this->fetchAllKeyPair();    
    
    $sSQL =   " SELECT COPL.PickupLocationKeyID, SUM(OI.mCoopPrice) as TotalCoopPrice, " . 
          " IfNull(SUM(IfNull(COPRD.fBurden,0) * IfNull( OI.fQuantity/NullIf(PRD.fQuantity,0),0) ),0) as TotalBurden " .
          " FROM T_CoopOrderPickupLocation COPL " . 
          $sJoin . " T_Order O ON O.CoopOrderKeyID = COPL.CoopOrderKeyID AND COPL.PickupLocationKeyID = O.PickupLocationKeyID " .
          $sJoin . " T_OrderItem OI ON O.OrderID = OI.OrderID " .
          $sJoin . " T_CoopOrderProduct COPRD ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID AND OI.ProductKeyID = COPRD.ProductKeyID " .
          $sJoin . " T_Product PRD ON OI.ProductKeyID = PRD.ProductKeyID " .
          " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] .
          " GROUP BY COPL.PickupLocationKeyID; ";

    $this->RunSQL( $sSQL );

    $this->m_recPickupLocations = $this->fetch();
    
    while($this->m_recPickupLocations)
    {
      $this->UpdatePickupLocation();
      
      $this->m_recPickupLocations = $this->fetch();
    }
    
    $this->CalculatePickupLocsProducts();
    $this->CalculatePickupLocsProducers();
  }
  
  //update coop order summaries
  public function CalculateCoopOrder()
  {
    $this->m_bCalculateProducersOnly = TRUE;
    $this->CalculateProducers();
    
    $this->UpdateCoopOrder();
  }
  
  protected function CalculateProducers()
  {  
    $sSQL =   " SELECT COP.ProducerKeyID, SUM(COPRD.mCoopTotal) mCoopTotal, SUM(COPRD.mProducerTotal) mProducerTotal, " . 
          " SUM(COPRD.fBurden * COPRD.fTotalCoopOrder) as TotalBurden, " .
          " COP.mMaxDelivery, COP.mMinDelivery, COP.mDelivery, COP.fDelivery " .  
          " FROM T_CoopOrderProducer COP INNER JOIN T_CoopOrderProduct COPRD ON COP.CoopOrderKeyID = COPRD.CoopOrderKeyID " . 
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID AND PRD.ProducerKeyID = COP.ProducerKeyID " .
          " WHERE COP.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] . 
          " GROUP BY COP.ProducerKeyID, COP.mMaxDelivery, COP.mMinDelivery, COP.mDelivery, COP.fDelivery ;";

    $this->RunSQL( $sSQL );

    $this->m_recProducers = $this->fetch();
    
    while($this->m_recProducers)
    {
      $this->UpdateProducer();
      
      $this->m_recProducers = $this->fetch();
    }
  }
  
  protected function CalculatePickupLocsProducts()
  {

    $sSQL =   " SELECT COPL.PickupLocationKeyID, COPRD.ProductKeyID, COPLPRD.PickupLocationKeyID HasRecord, PRD.nItems, SUM(OI.mCoopPrice) mCoopTotal, " .             
          " SUM(OI.mProducerPrice) mProducerTotal,COPRD.mCoopPrice, COPRD.mProducerPrice, COPLPRD.mProducerTotal mOriginalProducerTotal ,". 
          " COPLPRD.mCoopTotal mOriginalCoopTotal , COPLPRD.fTotalCoopOrder fOriginalTotalCoopOrder, " .
          " SUM(OI.fQuantity) as SumQuantity, SUM(OI.fUnjoinedQuantity) as TotalCoopOrder, (SELECT SUM(OI_J.nJoinedItems) " . 
                          " FROM T_Order O_J INNER JOIN T_OrderItem OI_J ON OI_J.OrderId = O_J.OrderID " .
                          " WHERE O_J.CoopOrderKeyID = O.CoopOrderKeyID AND OI_J.ProductKeyID = JPRD.ProductKeyID " . 
                          " AND O.PickupLocationKeyID = COPL.PickupLocationKeyID ) as TotalJoined " . 
          " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_CoopOrderProduct COPRD ON COPL.CoopOrderKeyID = COPRD.CoopOrderKeyID " .
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
          " LEFT JOIN T_CoopOrderPickupLocationProduct COPLPRD ON COPLPRD.CoopOrderKeyID = COPL.CoopOrderKeyID " .
          " AND COPLPRD.ProductKeyID = COPRD.ProductKeyID " .
          " AND COPLPRD.PickupLocationKeyID = COPL.PickupLocationKeyID " .
          " LEFT JOIN T_Order O ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID AND O.PickupLocationKeyID = COPL.PickupLocationKeyID " .
          " LEFT JOIN T_OrderItem OI ON O.OrderID = OI.OrderID AND OI.ProductKeyID = COPRD.ProductKeyID " . 
          " LEFT JOIN T_Product JPRD ON JPRD.JoinToProductKeyID = COPRD.ProductKeyID " .
          " WHERE COPL.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] . 
          " GROUP BY COPL.PickupLocationKeyID, COPRD.ProductKeyID, COPLPRD.PickupLocationKeyID, PRD.nItems, COPRD.mProducerPrice;";
    
    $this->RunSQL( $sSQL );

    $this->m_recPickupLocsProducts = $this->fetch();
    
    while($this->m_recPickupLocsProducts)
    {
      $this->ModifyPickupLocProduct();
      
      $this->m_recPickupLocsProducts = $this->fetch();
    }
    
  }
  
  protected function CalculatePickupLocsProducers()
  {
    $sSQL =   " SELECT COPL.PickupLocationKeyID, COP.ProducerKeyID, COPLP.PickupLocationKeyID HasRecord, SUM(COPLPRD.mCoopTotal) mCoopTotal, " .
          " SUM(COPLPRD.mProducerTotal) mProducerTotal, COPLP.mCoopTotal mOriginalCoopTotal, COPLP.mProducerTotal mOriginalProducerTotal " . 
          " FROM T_CoopOrderPickupLocation COPL INNER JOIN T_CoopOrderProducer COP ON COPL.CoopOrderKeyID = COP.CoopOrderKeyID " . 
          " INNER JOIN T_CoopOrderPickupLocationProduct COPLPRD ON COP.CoopOrderKeyID = COPLPRD.CoopOrderKeyID " . 
          " AND COPLPRD.PickupLocationKeyID = COPL.PickupLocationKeyID " .
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPLPRD.ProductKeyID AND PRD.ProducerKeyID = COP.ProducerKeyID " .
          " LEFT JOIN T_CoopOrderPickupLocationProducer COPLP ON COPLP.CoopOrderKeyID = COP.CoopOrderKeyID " .
          " AND COPLP.ProducerKeyID = COP.ProducerKeyID AND COPLP.PickupLocationKeyID = COPL.PickupLocationKeyID " .
          " WHERE COP.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] . 
          " GROUP BY COPL.PickupLocationKeyID, COP.ProducerKeyID, COPLP.PickupLocationKeyID;";

    $this->RunSQL( $sSQL );

    $this->m_recPickupLocsProducers = $this->fetch();
    
    while($this->m_recPickupLocsProducers)
    {
      $this->ModifyPickupLocProducer();
      
      $this->m_recPickupLocsProducers = $this->fetch();
    }
    
  }
  
  protected function UpdateCoopOrder()
  {
    //get total fee for the coop order
    $sSQL = " SELECT IfNull(SUM(O.mCoopFee),0) TotalFee " .
            " FROM T_Order O WHERE O.CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID];
    $this->RunSQL( $sSQL );
    $rec = $this->fetch();
    
    $mCoopTotal = Rounding::Round($this->m_aData[self::PROPERTY_TOTAL_COOP] + $rec["TotalFee"], ROUND_SETTING_COOP_TOTAL);
    
    $sSQL = " UPDATE T_CoopOrder SET fBurden = ?, mCoopTotal = ?, mProducerTotal =?, mTotalDelivery = ? ";
    
    if ($this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] !== NULL)
    {
      if ($this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS])
        $sSQL .= " ,bHasJoinedProducts = 1 ";
      else
        $sSQL .= " ,bHasJoinedProducts = 0 ";
    }
    
    $sSQL .= " WHERE CoopOrderKeyID = " . 
            $this->m_aData[CoopOrder::PROPERTY_ID] . " ;";
    $this->RunSQLWithParams($sSQL, array(
        $this->m_aData[self::PROPERTY_TOTAL_BURDEN],
        $mCoopTotal,
        $this->m_aData[self::PROPERTY_TOTAL_PRODUCERS],
        $this->m_aData[self::PROPERTY_TOTAL_DELIVERY]
            )
        );
  }
  
  protected function UpdatePickupLocation()
  {
    $this->m_bUseSecondSqlPreparedStmt = TRUE; //to allow fetch operations to continue on original one
    
    $mCoopTotal = $this->m_recPickupLocations["TotalCoopPrice"] + 
            $this->m_aPickupLocationsFees[$this->m_recPickupLocations["PickupLocationKeyID"]]["TotalFee"];
    
    $sSQL = " UPDATE T_CoopOrderPickupLocation SET fBurden = ?, mCoopTotal = ? WHERE CoopOrderKeyID = " . $this->m_aData[CoopOrder::PROPERTY_ID] .
          " AND PickupLocationKeyID = " . $this->m_recPickupLocations["PickupLocationKeyID"] . ";";
    $this->RunSQLWithParams($sSQL, array(
        $this->m_recPickupLocations["TotalBurden"],
        $mCoopTotal
            )
        );
    
    $this->m_bUseSecondSqlPreparedStmt = FALSE;
  }
  
  protected function UpdateProducer()
  {   
    $mProducerTotal = Rounding::Round( $this->m_recProducers["mProducerTotal"], ROUND_SETTING_PRODUCER_TOTAL );
    $mCoopTotal = Rounding::Round( $this->m_recProducers["mCoopTotal"], ROUND_SETTING_PRODUCER_COOP_TOTAL );
    
    //collect data for coop order totals
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] += $this->m_recProducers["TotalBurden"];
    $this->m_aData[self::PROPERTY_TOTAL_COOP] += $this->m_recProducers["mCoopTotal"];
    $this->m_aData[self::PROPERTY_TOTAL_PRODUCERS] += $mProducerTotal;
    $nTotalDelivery = CoopOrderProducer::GetDeliveryTotal($this->m_recProducers["fDelivery"], 
                $this->m_recProducers["mDelivery"], 
                $this->m_recProducers["mMinDelivery"], 
                $this->m_recProducers["mMaxDelivery"], 
                $this->m_recProducers["mProducerTotal"]);
    if ($nTotalDelivery != NULL)
      $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] += $nTotalDelivery;

    if (!$this->m_bCalculateProducersOnly)
    {
      $this->m_bUseSecondSqlPreparedStmt = TRUE; //to allow fetch operations to continue on original one

      //update
      $sSQL = " UPDATE T_CoopOrderProducer SET fBurden = ?, mProducerTotal = ?, mCoopTotal =?, mTotalDelivery = ? WHERE CoopOrderKeyID = " . 
              $this->m_aData[CoopOrder::PROPERTY_ID] .
            " AND ProducerKeyID = " . $this->m_recProducers["ProducerKeyID"] . ";";
      $this->RunSQLWithParams($sSQL, array(
          $this->m_recProducers["TotalBurden"],
          $mProducerTotal,
          $mCoopTotal,
          $nTotalDelivery
              )
          );

      $this->m_bUseSecondSqlPreparedStmt = FALSE;
    }
  }
  
  //update or insert pickup location producer based on actual member orders data
  protected function ModifyPickupLocProducer()
  {
    $mProducerTotal = Rounding::Round( $this->m_recPickupLocsProducers["mProducerTotal"], ROUND_SETTING_PRODUCER_TOTAL );
    $mCoopTotal = Rounding::Round( $this->m_recPickupLocsProducers["mCoopTotal"], ROUND_SETTING_PRODUCER_COOP_TOTAL );
    
    $this->m_bUseSecondSqlPreparedStmt = TRUE; //to allow fetch operations to continue on original one

    //update
    if ($this->m_recPickupLocsProducers["HasRecord"] > 0)
    {
      //skip update if no changes were made, to speed up performances
      if ( $this->m_recPickupLocsProducers["mOriginalProducerTotal"] != $mProducerTotal ||
           $this->m_recPickupLocsProducers["mOriginalCoopTotal"] != $mCoopTotal )
      {
        $sSQL = " UPDATE T_CoopOrderPickupLocationProducer SET mProducerTotal = ?, mCoopTotal =? WHERE CoopOrderKeyID = " . 
                $this->m_aData[CoopOrder::PROPERTY_ID] .
              " AND ProducerKeyID = " . $this->m_recPickupLocsProducers["ProducerKeyID"] . 
              " AND PickupLocationKeyID = " . $this->m_recPickupLocsProducers["PickupLocationKeyID"] . ";";
        $this->RunSQLWithParams($sSQL, array(
                  $mProducerTotal,
                  $mCoopTotal
                )
            );
      }
    }
    else
    {
      $sSQL = " INSERT INTO T_CoopOrderPickupLocationProducer (CoopOrderKeyID, ProducerKeyID, PickupLocationKeyID, " . 
             " mProducerTotal,mCoopTotal) VALUES(?, ?, ?, ?, ? );";
      
      $this->RunSQLWithParams($sSQL, array(
        $this->m_aData[CoopOrder::PROPERTY_ID], $this->m_recPickupLocsProducers["ProducerKeyID"], 
          $this->m_recPickupLocsProducers["PickupLocationKeyID"], $mProducerTotal, $mCoopTotal
            )
        );
      
    }

    $this->m_bUseSecondSqlPreparedStmt = FALSE;
    
  }
  
  
  protected function UpdateProduct()
  {   
    $this->m_bUseSecondSqlPreparedStmt = TRUE; //to allow fetch operations to continue on original one
        
    $mProducerTotal = Rounding::Round($this->m_recProducts["mProducerTotal"],ROUND_SETTING_PRODUCT_PRODUCER_TOTAL);
    $mCoopTotal = Rounding::Round($this->m_recProducts["mCoopTotal"],ROUND_SETTING_PRODUCT_COOP_TOTAL);
    
    $fTotalCoopOrder = $this->m_recProducts["TotalCoopOrder"] + 0;
    
    $nJoinStatus = CoopOrderProduct::JOIN_STATUS_NONE;
    
    if ($this->m_recProducts["SumQuantity"] > $fTotalCoopOrder)
      $nJoinStatus = CoopOrderProduct::JOIN_STATUS_JOINED;

    if ($this->m_recProducts["TotalJoined"] > 0 && $this->m_recProducts["nItems"] > 0)
    {
      $fAdded = floor($this->m_recProducts["TotalJoined"] / $this->m_recProducts["nItems"]);
      $fTotalCoopOrder += $fAdded;
      $mProducerTotal += $fAdded * $this->m_recProducts["mProducerPrice"];
      $mCoopTotal += $fAdded * $this->m_recProducts["mCoopPrice"];
      $nJoinStatus = CoopOrderProduct::JOIN_STATUS_JOINED_BY;
    }

    //update
    $sSQL = " UPDATE T_CoopOrderProduct SET mProducerTotal = ?, mCoopTotal = ?, fTotalCoopOrder = ?,  nJoinedStatus = " . $nJoinStatus . " WHERE CoopOrderKeyID = " . 
            $this->m_aData[CoopOrder::PROPERTY_ID] .
          " AND ProductKeyID = " . $this->m_recProducts["ProductKeyID"] . ";";
    $this->RunSQLWithParams($sSQL, array(
        $mProducerTotal, $mCoopTotal, $fTotalCoopOrder
            )
        );
    
    $this->m_bUseSecondSqlPreparedStmt = FALSE;
  }
  
  //update or insert pickup location product based on actual member orders data
  protected function ModifyPickupLocProduct()
  {
    $this->m_bUseSecondSqlPreparedStmt = TRUE; //to allow fetch operations to continue on original one
        
    $mProducerTotal = Rounding::Round($this->m_recPickupLocsProducts["mProducerTotal"],ROUND_SETTING_PRODUCT_PRODUCER_TOTAL);
    $mCoopTotal = Rounding::Round($this->m_recPickupLocsProducts["mCoopTotal"],ROUND_SETTING_PRODUCT_COOP_TOTAL);
    
    $fTotalCoopOrder = $this->m_recPickupLocsProducts["TotalCoopOrder"] + 0;
    
    if ($this->m_recPickupLocsProducts["TotalJoined"] > 0 && $this->m_recPickupLocsProducts["nItems"] > 0)
    {
      $fAdded = floor($this->m_recPickupLocsProducts["TotalJoined"] / $this->m_recPickupLocsProducts["nItems"]);
      $fTotalCoopOrder += $fAdded;
      $mProducerTotal += $fAdded * $this->m_recPickupLocsProducts["mProducerPrice"];
      $mCoopTotal += $fAdded * $this->m_recPickupLocsProducts["mCoopPrice"];
    }

    //update
    if ($this->m_recPickupLocsProducts["HasRecord"] > 0)
    {
      //skip update if no changes were made, to speed up performances
      if ( $this->m_recPickupLocsProducts["mOriginalProducerTotal"] != $mProducerTotal ||
           $this->m_recPickupLocsProducts["mOriginalCoopTotal"] != $mCoopTotal ||
           $this->m_recPickupLocsProducts["fOriginalTotalCoopOrder"] != $fTotalCoopOrder )
      {
      $sSQL = " UPDATE T_CoopOrderPickupLocationProduct SET mProducerTotal = ?, mCoopTotal = ?, fTotalCoopOrder = ?  WHERE CoopOrderKeyID = " . 
              $this->m_aData[CoopOrder::PROPERTY_ID] .
            " AND ProductKeyID = " . $this->m_recPickupLocsProducts["ProductKeyID"] . 
            " AND PickupLocationKeyID = " . $this->m_recPickupLocsProducts["PickupLocationKeyID"] . ";";
      
       $this->RunSQLWithParams($sSQL, array(
        $mProducerTotal, $mCoopTotal, $fTotalCoopOrder
            )
        );
      }
    }
    else
    {
     $sSQL = " INSERT INTO T_CoopOrderPickupLocationProduct (CoopOrderKeyID, ProductKeyID, PickupLocationKeyID, " . 
             " mProducerTotal,mCoopTotal,fTotalCoopOrder) VALUES(?, ?, ?, ?, ? ,?);";
      
      $this->RunSQLWithParams($sSQL, array(
        $this->m_aData[CoopOrder::PROPERTY_ID], $this->m_recPickupLocsProducts["ProductKeyID"], 
          $this->m_recPickupLocsProducts["PickupLocationKeyID"], $mProducerTotal, $mCoopTotal, $fTotalCoopOrder
            )
        );
    }
        
    $this->m_bUseSecondSqlPreparedStmt = FALSE;
    
  }
}

?>
