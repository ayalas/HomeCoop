<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faciliate member order items page (orderitems.php)
class OrderItems extends SQLBase {
  
  const PRODUCTS_VIEW_MODE_SHOW_ALL = 0;
  const PRODUCTS_VIEW_MODE_ITEMS_ONLY = 1;
  
  const POST_ACTION_SWITCH_VIEW_MODE = 10;
  
  const PERMISSION_MODIFY_ORDER_ITEMS = 500;
  const PERMISSION_VIEW_ORDER_ITEMS = 501;
  const PERMISSION_VIEW_PICKUP_LOCATION_ORDER = 502;
  
  const MAX_LENGTH_MEMBER_COMMENTS = 100;
  const PROPERTY_ORDER_ITEMS = "OrderItems";
  const PROPERTY_PRODUCTS_VIEW_MODE = "ProductsViewMode";
  
  const CTL_PREFIX_COMMENTS = "txtOrderItemComments";
  const CTL_PREFIX_MAX_FIX_QUANTITY_ADDITION = "txtMemberMaxFixQuantityAddition";
  const CTL_PREFIX_QUANTITY = "txtOrderQuantity";
  const CTL_PREFIX_ID = "hidOrderItemID";
  
  protected $m_oOrder = NULL;
  protected $m_aProducerTotals = NULL;
  protected $m_aProductsChanged = NULL;
  protected $m_bFirstSave = TRUE;

  public function __construct()
  {
    $this->m_aDefaultData = array( Order::PROPERTY_ID => 0,
        self::PROPERTY_ORDER_ITEMS => array(),
        self::PROPERTY_PRODUCTS_VIEW_MODE => self::PRODUCTS_VIEW_MODE_ITEMS_ONLY
       );
    
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
   
  //set the Order class to be used for calculations and validations
  public function SetOrder(&$oOrder)
  {
    if ($oOrder instanceof Order)
    {
      $this->m_oOrder = $oOrder;   
      //$this->m_fOrderOriginalCoopTotal = $this->m_oOrder->CoopTotal;
      //$this->m_fOrderOriginalOrderBurden = $this->m_oOrder->OrderBurden;
    }
  }
  
  public function LoadTable()
  {   
    global $g_oMemberSession;
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    if ($this->m_oOrder == NULL || !$this->m_oOrder->HasAnyPermission())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
        
    if ($this->m_oOrder->ID <= 0)
      throw new Exception('Error in OrderItems.LoadTable - Invalid OrderID provided');
    
    $this->m_aData[Order::PROPERTY_ID] = $this->m_oOrder->ID;
    
    if ($g_oMemberSession->MemberID != $this->m_oOrder->MemberID)
    {
      //check basic permissions for order items
      if (!$this->AddPermissionBridge(self::PERMISSION_VIEW_ORDER_ITEMS, Consts::PERMISSION_AREA_ORDER_ITEMS, 
              Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }

      //set modify permissions when not self order
      if (!$this->AddPermissionBridge(self::PERMISSION_MODIFY_ORDER_ITEMS, Consts::PERMISSION_AREA_ORDER_ITEMS, 
              Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) &&
             $g_oMemberSession->MemberID !=  $this->m_oOrder->MemberID)
        $this->m_oOrder->CanModify = FALSE;
    }
    
    //show existing items only when can't modify
    if (!$this->m_oOrder->CanModify)
      $this->m_aData[self::PROPERTY_PRODUCTS_VIEW_MODE] = OrderItems::PRODUCTS_VIEW_MODE_ITEMS_ONLY;
    
    //clear the items array before load
    $this->m_aData[self::PROPERTY_ORDER_ITEMS] = array();

    $sSQL =   " SELECT OI.OrderItemID, PRD.ProductKeyID, P.ProducerKeyID, PRD.UnitKeyID, OI.fQuantity, OI.mCoopPrice ,  OI.mProducerPrice, " .
            " OI.fOriginalQuantity, OI.fMaxFixQuantityAddition, OI.sMemberComments, OI.fUnjoinedQuantity, COPRD.mProducerPrice ProductProducerPrice, "  .
            " COPRD.fBurden, COPRD.fMaxCoopOrder, IfNull(COPRD.fTotalCoopOrder,0) fTotalCoopOrder, COPRD.mCoopPrice ProductCoopPrice, " .
            " COPRD.fMaxUserOrder, PRD.JoinToProductKeyID, NullIf(JPRD.nItems,0) JoinedProductItems, P.CoordinatingGroupID,  " .
            " IfNull(JCOPRD.mCoopPrice,0) JoinedCoopPrice,  IfNUll(JCOPRD.mProducerPrice,0) JoinedProducerPrice, OI.nJoinedItems, " .
            " NUllIf(PRD.fQuantity,0) ProductQuantity, PRD.nItems ProductItems, PRD.fItemQuantity, PRD.fPackageSize, PRD.fUnitInterval, " .
            " COSA.fBurden StorageAreaBurden, COSA.fMaxBurden StorageAreaMaxBurden, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
          ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_JOINED_PRODUCTS, 'sJoinedProduct') .
          ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, 'sUnitAbbrev') .
          "," . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, 'sItemUnitAbbrev') .
          " FROM T_Order O INNER JOIN T_CoopOrderProduct COPRD ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID " . 
          " INNER JOIN T_CoopOrderProductStorage COPS ON COPS.CoopOrderKeyID = O.CoopOrderKeyID " .
          " AND COPS.ProductKeyID = COPRD.ProductKeyID " .
          " AND COPS.PickupLocationKeyID = O.PickupLocationKeyID " .
          " INNER JOIN T_CoopOrderStorageArea COSA ON COSA.CoopOrderKeyID =  COPS.CoopOrderKeyID " .
          " AND COSA.StorageAreaKeyID = COPS.StorageAreaKeyID " .
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
          " INNER JOIN T_Producer P ON P.ProducerKeyID = PRD.ProducerKeyID " .
          " INNER JOIN T_Unit UT ON UT.UnitKeyID = PRD.UnitKeyID " .
          " LEFT JOIN T_Product JPRD ON JPRD.ProductKeyID = PRD.JoinToProductKeyID " .
          " LEFT JOIN T_CoopOrderProduct JCOPRD ON O.CoopOrderKeyID = JCOPRD.CoopOrderKeyID AND JCOPRD.ProductKeyID = JPRD.ProductKeyID " .
          " LEFT JOIN T_Unit IUT ON IUT.UnitKeyID = PRD.ItemUnitKeyID ";
    if ($this->m_aData[self::PROPERTY_PRODUCTS_VIEW_MODE] == self::PRODUCTS_VIEW_MODE_SHOW_ALL)
      $sSQL .= " LEFT JOIN ";
    else
      $sSQL .= " INNER JOIN ";
    
    $sSQL .= " T_OrderItem OI ON OI.OrderID = O.OrderID AND OI.ProductKeyID = COPRD.ProductKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_JOINED_PRODUCTS) .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_UNITS) .
          $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION, Consts::PERMISSION_AREA_ITEM_UNITS) .
          " WHERE O.OrderID = " . $this->m_aData[Order::PROPERTY_ID] . 
          " ORDER BY PRD.nSortOrder ,PRD_S.sString; ";

    $this->RunSQL( $sSQL );

    $recItem = $this->fetch();
    
    while ($recItem)
    {
      $oItem = new OrderItem;
      
      $oItem->OrderItemID = $recItem["OrderItemID"];
      $oItem->ProductID = $recItem["ProductKeyID"];
      $oItem->ProducerID = $recItem["ProducerKeyID"];
      $oItem->CoordinatingGroupID = $recItem["CoordinatingGroupID"];
      
      //filter view when not self order
      if ( $g_oMemberSession->MemberID !=  $this->m_oOrder->MemberID &&
          !$this->SetRecordGroupID(self::PERMISSION_VIEW_ORDER_ITEMS, $oItem->CoordinatingGroupID, FALSE))
        $oItem->Visible = FALSE;
      
      $oItem->Quantity = $recItem["fQuantity"];
      $oItem->UnjoinedQuantity = $recItem["fUnjoinedQuantity"];
      $oItem->JoinToProductName = $recItem["sJoinedProduct"];
      $oItem->JoinToProductID= $recItem["JoinToProductKeyID"];
      $oItem->JoinedCoopPrice = $recItem["JoinedCoopPrice"];
      $oItem->JoinedProducerPrice = $recItem["JoinedProducerPrice"];
      $oItem->JoinedItems = $recItem["nJoinedItems"]; 
      $oItem->JoinedProductItems = $recItem["JoinedProductItems"];      
      $oItem->CoopTotal = $recItem["mCoopPrice"];
      $oItem->ProducerTotal = $recItem["mProducerPrice"];
      $oItem->MemberLastQuantity = $recItem["fOriginalQuantity"];
      $oItem->MemberMaxFixQuantityAddition = $recItem["fMaxFixQuantityAddition"];
      $oItem->MemberComments = $recItem["sMemberComments"];
      $oItem->ProductCoopPrice = $recItem["ProductCoopPrice"];
      $oItem->ProductProducerPrice = $recItem["ProductProducerPrice"];
      $oItem->ProductQuantity = $recItem["ProductQuantity"];
      $oItem->ProductUnitID = $recItem["UnitKeyID"];
      $oItem->ProductItems = $recItem["ProductItems"];
      $oItem->ProductItemQuantity = $recItem["fItemQuantity"];
      $oItem->PackageSize = $recItem["fPackageSize"];
      $oItem->UnitInterval = $recItem["fUnitInterval"];
      $oItem->ProductName = $recItem["sProduct"];
      $oItem->ProducerName = $recItem["sProducer"];
      $oItem->UnitAbbrev = $recItem["sUnitAbbrev"];
      $oItem->ItemUnitAbbrev = $recItem["sItemUnitAbbrev"];
      $oItem->ProductMaxUserOrder = $recItem["fMaxUserOrder"];
      $oItem->ProductMaxCoopOrder = $recItem["fMaxCoopOrder"];
      $oItem->ProductBurden = $recItem["fBurden"];
      $oItem->ProductTotalCoopOrderQuantity = $recItem["fTotalCoopOrder"];
      $oItem->StorageAreaBurden  = $recItem["StorageAreaBurden"];
      $oItem->StorageAreaMaxBurden = $recItem["StorageAreaMaxBurden"];
      
      $oItem->CalculateBurden();
      
      $this->SetItemChangedByCoordinator($oItem);
      
      $this->m_aData[self::PROPERTY_ORDER_ITEMS][$recItem["ProductKeyID"]] = $oItem;

      
      $recItem = $this->fetch();
    }
    
    $this->m_aOriginalData = $this->m_aData;
    
    return $this->m_aData[self::PROPERTY_ORDER_ITEMS];
 }

 //outmost save operation
 public function Save()
 {
    global $g_oMemberSession;
        
    //check permissions
    if ($this->m_oOrder == NULL || !$this->m_oOrder->HasAnyPermission())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ($g_oMemberSession->MemberID != $this->m_oOrder->MemberID)
    {
      if (!$this->AddPermissionBridge(self::PERMISSION_MODIFY_ORDER_ITEMS, Consts::PERMISSION_AREA_ORDER_ITEMS, 
              Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, NULL, TRUE) &&
             $g_oMemberSession->MemberID !=  $this->m_oOrder->MemberID)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
      }
    }
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    $this->m_aData[Order::PROPERTY_ID] = $this->m_oOrder->ID;
    
    //collect data for save
    $this->CollectData();

    //exit if order cannot be modified 
    //(save button shouldn't have been displayed, so a "data not saved" - issued in orderitems.php when FALSE is returned here
    // will suffice in such case)
    //the CanModify flag is updated in Order::LoadRecord, already called before passing the object to this class
    if (!$this->m_oOrder->CanModify)
      return FALSE;
    
    //validate data, EXIT if invalid
    if (!$this->ValidateData())
      return FALSE;
    
    //actual save data
    $this->SaveData();
    
    //if just inserting for the first time, change view to saved products
    if ($this->m_bFirstSave)
      $this->m_aData[self::PROPERTY_PRODUCTS_VIEW_MODE] = self::PRODUCTS_VIEW_MODE_ITEMS_ONLY;
    
    return TRUE;
 }
 
 //for coordinators - get the entire member comments for the order in a separate recordset 
 // - read in coord/orders.php and coord/copickuplocorders.php
 public function GetComments($OrderID)
 {
   global $g_oMemberSession;
   
   if ($OrderID <= 0)
      throw new Exception('Error in OrderItems.GetOrderItemsComments - Invalid OrderID provided');

   $bEdit = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
   
   $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
   
   $bPLView = $this->AddPermissionBridge(self::PERMISSION_VIEW_PICKUP_LOCATION_ORDER, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS, 
           Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
   //Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS
   
   if (!$bEdit && !$bView && !$bPLView)
   {
     $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
     return NULL;
   }

   $sSQL =   " SELECT OI.sMemberComments, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCTS, 'sProduct') .
          " FROM T_Order O INNER JOIN T_CoopOrderProduct COPRD ON O.CoopOrderKeyID = COPRD.CoopOrderKeyID " . 
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPRD.ProductKeyID " .
          " INNER JOIN T_OrderItem OI ON OI.OrderID = O.OrderID AND OI.ProductKeyID = COPRD.ProductKeyID " .
          " INNER JOIN T_PickupLocation PL ON O.PickupLocationKeyID = PL.PickupLocationKeyID " .
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCTS) .
          " WHERE O.OrderID = " . $OrderID . ' AND OI.sMemberComments IS NOT NULL ';
   
    if (!$bEdit && !$bView ) //if just pickup location permission
    {
      if ($this->GetPermissionScope(self::PERMISSION_VIEW_PICKUP_LOCATION_ORDER) != Consts::PERMISSION_SCOPE_COOP_CODE)
        $sSQL .= " AND PL.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";
      else //must be of some pickup location with only pickup location orders permission
        $sSQL .= " AND PL.PickupLocationKeyID IS NOT NULL ";
    }
   
    $sSQL .= " ORDER BY PRD.nSortOrder ,PRD_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
 
 protected function GetProducersListForModify()
 {
    global $g_oMemberSession;
   
    $sSQL =   " SELECT COP.ProducerKeyID, COP.mTotalDelivery, COP.mMaxProducerOrder, IfNull(COP.mProducerTotal,0) mProducerTotal,  " .
          " P.sExportFileName, IfNull(COP.fBurden,0) fBurden, COP.fMaxBurden, P.CoordinatingGroupID, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          " FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          " WHERE COP.CoopOrderKeyID = " . $this->m_oOrder->CoopOrderID;
    if ($g_oMemberSession->MemberID != $this->m_oOrder->MemberID)
    {
      if ($this->GetPermissionScope(self::PERMISSION_MODIFY_ORDER_ITEMS) == Consts::PERMISSION_SCOPE_GROUP_CODE &&
             $g_oMemberSession->MemberID !=  $this->m_oOrder->MemberID)
      {
        $sSQL .= " AND P.CoordinatingGroupID IN ( " . implode(",", $g_oMemberSession->Groups) . " ) ";
      }
    }
    
    $sSQL .= " ORDER BY P_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch(); 
 }

 //collect data after postback from global $_POST variable
 protected function CollectData()
 {
   global $_POST, $g_oMemberSession;
   
   $nProductID = 0;
   $bCurrentUser = ($this->m_oOrder->MemberID == $g_oMemberSession->MemberID);
   $oOriginalItem = NULL;
   $nIDPrefixLen = strlen(self::CTL_PREFIX_ID);
   $nQuantityPrefixLen = strlen(self::CTL_PREFIX_QUANTITY);
   $nAdditionPrefixLen = strlen(self::CTL_PREFIX_MAX_FIX_QUANTITY_ADDITION);
   $nCommentsPrefixLen = strlen(self::CTL_PREFIX_COMMENTS);
   $oItem = NULL;
   $this->m_aProducerTotals = array();
   $this->m_aProductsChanged = array();
   
   $this->m_oOrder->CoopTotal = 0;
   $this->m_oOrder->ProducerTotal = 0;
   $this->m_oOrder->OrderBurden = 0;
   $this->m_oOrder->HasItemComments = FALSE;
   
   foreach($_POST as $key => $value)
   {
     //if found in position 0
     if (strpos($key, self::CTL_PREFIX_ID) === 0)
     {
       $nProductID = $this->InitItemFromPostKey($key, $nIDPrefixLen);

       $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->OrderItemID = intval($value);
     }
     else if (strpos($key, self::CTL_PREFIX_QUANTITY) === 0)
     {
       $nProductID = $this->InitItemFromPostKey($key, $nQuantityPrefixLen);
                
       if (0 + $value > 0)
       {
        $oItem = $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID];
        $oOriginalItem = $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$nProductID];
        $oItem->Quantity = 0 + $value;
        
        if ($oItem->JoinedItems > 0)
        {
          //when joined, can only add quantity (anything else will fail validation, so no need to deal with it here)
          $oItem->UnjoinedQuantity = $oOriginalItem->UnjoinedQuantity + ($oItem->Quantity - $oOriginalItem->Quantity);
        }
        else
          $oItem->UnjoinedQuantity = $oItem->Quantity;
        
        $oItem->SetTotals();
        $this->m_oOrder->CoopTotal += $oItem->CoopTotal;
        $this->m_oOrder->ProducerTotal += $oItem->ProducerTotal;
        $this->m_oOrder->OrderBurden += $oItem->CalculateBurden();
        if ($bCurrentUser)
          $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->MemberLastQuantity = $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->Quantity;
        else
          $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->MemberLastQuantity = $oOriginalItem->MemberLastQuantity;       
        
        //update producer totals
        $this->CollectProducerTotals($oItem);
       }
     }
     else if (strpos($key, self::CTL_PREFIX_MAX_FIX_QUANTITY_ADDITION) === 0)
     {
       $nProductID = $this->InitItemFromPostKey($key, $nAdditionPrefixLen);

       if (0 + $value > 0)
        $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->MemberMaxFixQuantityAddition = 0 + $value;
     }
     else if (strpos($key, self::CTL_PREFIX_COMMENTS) === 0)
     {
       $nProductID = $this->InitItemFromPostKey( $key, $nCommentsPrefixLen );

       $value = trim($value);
       if (!empty($value))
       {
        $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID]->MemberComments = $value;
        $this->m_oOrder->HasItemComments = TRUE;
       }
     }
   }
   
   //add invisble items
   foreach($this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS] as $nProductID => $oOriginalItem)
   {
     if (!$oOriginalItem->Visible)
     {
       $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID] = clone $oOriginalItem;
       $this->m_oOrder->CoopTotal += $oOriginalItem->CoopTotal;
       $this->m_oOrder->ProducerTotal += $oOriginalItem->ProducerTotal;
       $this->m_oOrder->OrderBurden += $oOriginalItem->Burden;
     }
   }
   
   //rounding collected data
   $this->m_oOrder->CoopTotal = Rounding::Round( $this->m_oOrder->CoopTotal, ROUND_SETTING_ORDER_COOP_TOTAL );
 }
 
 //validate collected data against products, producers, coop order, pickup location, member balance, etc.
 protected function ValidateData()
 {
   global $g_oError;
   global $g_oMemberSession;
   
   $bValid = TRUE;
   $bValidItems = TRUE;
   
   //update coop order total and total burden as if it included the new values
   $this->m_oOrder->SetCoopOrderTotalsForValidations();
   $bOrigSuppressMessages = $this->m_oOrder->SuppressMessages;
   $this->m_oOrder->SuppressMessages = FALSE;  //do not suppress messages for save validation
   $this->m_oOrder->RunTotalsValidations();
   $this->m_oOrder->SuppressMessages = $bOrigSuppressMessages;
   //if cannot modify, or cannot enlarge and order is larger
   if (!$this->m_oOrder->CanModify || 
       (!$this->m_oOrder->CanEnlarge && $this->m_oOrder->IsLarger() ) )
   {
     $bValid = FALSE;
   }

   //validate pickup location
   $this->m_oOrder->LoadCoopOrderPickupLocation();
   if (!$this->m_oOrder->ValidatePickupLocation(TRUE))
      $bValid = FALSE;
   
   //validate producers
   $recProducer = $this->GetProducersListForModify();
   while($recProducer)
   {
     if (!$this->ValidateProducer($recProducer))
       $bValid = FALSE;
     
     $recProducer = $this->fetch();
   }
   
   //go through all order items
   foreach($this->m_aData[self::PROPERTY_ORDER_ITEMS] as $nProductID => $oOrderItem)
   {
     $oOriginalItem = $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$nProductID];
     
     if (!$oOriginalItem->Visible)
       continue;
     
     //validate that user has permission to modify item when not her own order
     if ($g_oMemberSession->MemberID != $this->m_oOrder->MemberID)
     {
       if (!$this->SetRecordGroupID(self::PERMISSION_MODIFY_ORDER_ITEMS, $oOriginalItem->CoordinatingGroupID, FALSE))
       {
         $oOrderItem->InvalidEntry = TRUE;
         $oOrderItem->ValidationMessage .= 'Access Denied<br/>'; 
       }
     }
     
     if ( $oOrderItem->Quantity == NULL || $oOrderItem->Quantity == 0)
     {  
       //cannot delete joined
       if ($oOriginalItem->JoinedItems > 0)
       {
         $oOrderItem->InvalidEntry = TRUE;
         $oOrderItem->ValidationMessage .= 'Cannot remove order for this product at this time, because quantities were joined to a cost-saving larger package. Please contact the cooperative order coordinator for help<br/>'; 
       }
       if ($oOrderItem->MemberMaxFixQuantityAddition != NULL)
       {
          $oOrderItem->InvalidEntry = TRUE;
          $oOrderItem->ValidationMessage .= 'Cannot save a value in the &quot;Add&quot; column without an order.<br/>';  
       }
       if ($oOrderItem->MemberComments != NULL)
       {
          $oOrderItem->InvalidEntry = TRUE;
          $oOrderItem->ValidationMessage .= 'Cannot save a comment without an order. Such comments should be left in the Order Header<br/>';  
       }
     }
     else //order item product related validations
       $this->ValidateProduct($oOrderItem, $oOriginalItem);

     if ($oOrderItem->InvalidEntry)
        $bValidItems = FALSE;
   }
   if (!$bValidItems)
   {
     $bValid = FALSE;
     $g_oError->AddError('Products order is invalid. Detailed message appears above each row in red color.');
   }
   
   if (!$bValid)
     $g_oError->PushError('Data was not saved.');
   
   return $bValid;
 }
 
 //main actual save function. Save is done in a PDO transaction with all calculations
 protected function SaveData()
 {
  global $g_oMemberSession;
  global $g_dNow;
  $dNow = $g_dNow;
  $sNow = $dNow->format(DATABASE_DATE_FORMAT);

  try
  {
    $this->BeginTransaction();
    //go over each OrderItem
    foreach($this->m_aData[self::PROPERTY_ORDER_ITEMS] as $ProductID => $oOrderItem)
    {
      //if empty and viewing only existing, remove item
      if ($this->SaveItem($oOrderItem) && $this->m_aData[self::PROPERTY_PRODUCTS_VIEW_MODE] == self::PRODUCTS_VIEW_MODE_ITEMS_ONLY)
        unset($this->m_aData[self::PROPERTY_ORDER_ITEMS][$ProductID]);
    }

    //must save new values to original data, because otherwise Update operations may be skipped
    $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS] = $this->m_aData[self::PROPERTY_ORDER_ITEMS];
  
    $this->m_oOrder->CalculateCoopFee();

    //Save Order details
    $sSQL =   " UPDATE T_Order SET mCoopTotal = ?, mCoopTotalIncFee = ?, mProducerTotal = ?, bHasItemComments = ?, dModified = ?, " . 
            " ModifiedByMemberID = ?, mCoopFee = ?, fBurden = ? WHERE OrderID = " . 
            $this->m_oOrder->ID . ';';

    $this->RunSQLWithParams( $sSQL, array(
              $this->m_oOrder->CoopTotal, 
              $this->m_oOrder->CoopTotalIncludingFee,
              $this->m_oOrder->ProducerTotal, 
              $this->m_oOrder->HasItemComments,
              $sNow,
              $g_oMemberSession->MemberID,
              $this->m_oOrder->OrderCoopFee,
              $this->m_oOrder->OrderBurden ));

    //Recalculate totals for coop order, pickup locations and producers
    $oCalc = new CoopOrderCalculate($this->m_oOrder->CoopOrderID);
    if (count($this->m_aProductsChanged) > 0)
      $oCalc->ProductsListToCalculate = implode(",", $this->m_aProductsChanged);  

    $oCalc->Run();
    
    $this->CommitTransaction();
  }
  catch(Exception $e)
  {
    $this->RollbackTransaction();
    throw $e;
  }
 }
 
 //called in SaveData to determine which action (if any) should be taken for a given item - Save, Update, Delete or None
 protected function SaveItem(&$oOrderItem)
 {
  $bExisting = ($oOrderItem->OrderItemID > 0);
  $bEmpty = ($oOrderItem->Quantity == NULL || $oOrderItem->Quantity == 0);

  if (!$bExisting && !$bEmpty)
    $this->InsertItem($oOrderItem);
  else if ($bExisting)
  {
    $this->m_bFirstSave = FALSE; //remove flag that says we are on the first save
    if ($bEmpty)
      $this->DeleteItem($oOrderItem);
    else
      $this->UpdateItem($oOrderItem);
  }
  
  return $bEmpty;
 }
 
 //called in SaveData to insert a new item record
 protected function InsertItem(&$oOrderItem)
 {
   $sSQL = " INSERT T_OrderItem (OrderID, ProductKeyID, fQuantity, fUnjoinedQuantity, fMaxFixQuantityAddition, sMemberComments, mCoopPrice, " . 
          " mProducerPrice, fOriginalQuantity) VALUES ( " . 
          $this->m_aData[Order::PROPERTY_ID] . "," .  $oOrderItem->ProductID . "," . $oOrderItem->Quantity . "," . 
           $oOrderItem->UnjoinedQuantity .
           " , :MaxFixQuantityAddition ,  :MemberComments, :CoopPrice, :ProducerPrice, :OriginalQuantity  );";
   
   $this->RunSQLWithParams($sSQL, array( "MaxFixQuantityAddition" => $oOrderItem->MemberMaxFixQuantityAddition, 
       "MemberComments" => $oOrderItem->MemberComments,
       "CoopPrice" => $oOrderItem->CoopTotal,"ProducerPrice" => $oOrderItem->ProducerTotal, "OriginalQuantity" => $oOrderItem->MemberLastQuantity
       ) );
   
   $oOrderItem->OrderItemID = $this->GetLastInsertedID();
   
   $this->m_aProductsChanged[] = $oOrderItem->ProductID;
 }
 
 //called in SaveData to update an existing item
 protected function UpdateItem(&$oOrderItem)
 {  
   //skip update if original values are the same
   if ($oOrderItem->Quantity == $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->Quantity &&
      $oOrderItem->CoopTotal == $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->CoopTotal &&
      $oOrderItem->ProducerTotal == $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->ProducerTotal &&
       $oOrderItem->MemberMaxFixQuantityAddition == 
           $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->MemberMaxFixQuantityAddition &&
       $oOrderItem->MemberComments == $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->MemberComments )
     return;
   
   $sSQL = " UPDATE T_OrderItem SET fQuantity = " . $oOrderItem->Quantity .
           " ,fMaxFixQuantityAddition = :MaxFixQuantityAddition , sMemberComments = :MemberComments, mCoopPrice = :CoopPrice, " . 
           " mProducerPrice = :ProducerPrice, fOriginalQuantity = :OriginalQuantity, fUnjoinedQuantity = :UnjoinedQuantity " .
           " WHERE OrderItemID = " . $oOrderItem->OrderItemID . ';';

   $this->RunSQLWithParams($sSQL, array( "MaxFixQuantityAddition" => $oOrderItem->MemberMaxFixQuantityAddition, "MemberComments" => $oOrderItem->MemberComments,
       "CoopPrice" => $oOrderItem->CoopTotal,"ProducerPrice" => $oOrderItem->ProducerTotal,"OriginalQuantity" => $oOrderItem->MemberLastQuantity, "UnjoinedQuantity" => $oOrderItem->UnjoinedQuantity
       ) );
   
   //check if calculation is required for this product (only quantities matters)
   if ($oOrderItem->Quantity != $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$oOrderItem->ProductID]->Quantity)
    $this->m_aProductsChanged[] = $oOrderItem->ProductID;

 }
 
 //called in SaveData, when quantity was set to 0/null
 protected function DeleteItem(&$oOrderItem)
 {
   $sSQL = " DELETE FROM T_OrderItem WHERE OrderItemID = " . $oOrderItem->OrderItemID  . ';';
   
   $this->RunSQL($sSQL);
   $oOrderItem->OrderItemID = 0;
   $this->m_aProductsChanged[] = $oOrderItem->ProductID;
 }
 
 //used for collecting postback data and rebuilding the item instance
 protected function InitItemFromPostKey($key, $nPrefixLen)
 {
   $nProductID = 0 + substr($key, $nPrefixLen );
   if ($nProductID == 0)
     throw new Exception('Error in OrderItems.CollectData: ProductID 0 for post key ' . $key);
   
   if (!array_key_exists($nProductID, $this->m_aData[self::PROPERTY_ORDER_ITEMS]))
   {
    $oOriginalItem = $this->m_aOriginalData[self::PROPERTY_ORDER_ITEMS][$nProductID];
    
    $oItem = new OrderItem;
    $oItem->ProductID = $nProductID;
    $oItem->OrderItemID = $oOriginalItem->OrderItemID;
    //copy constant data from original item
    $oItem->ProducerID = $oOriginalItem->ProducerID;
    $oItem->CoordinatingGroupID = $oOriginalItem->CoordinatingGroupID;
    $oItem->Visible = $oOriginalItem->Visible;
    
    $oItem->ProductProducerPrice  = $oOriginalItem->ProductProducerPrice;
    $oItem->ProductCoopPrice = $oOriginalItem->ProductCoopPrice;
    $oItem->ProductQuantity = $oOriginalItem->ProductQuantity;
    
    $oItem->JoinToProductName  = $oOriginalItem->JoinToProductName;
    $oItem->JoinToProductID  = $oOriginalItem->JoinToProductID;
    $oItem->JoinedCoopPrice  = $oOriginalItem->JoinedCoopPrice;  
    $oItem->JoinedProducerPrice  = $oOriginalItem->JoinedProducerPrice;     
    $oItem->JoinedItems = $oOriginalItem->JoinedItems;
    $oItem->JoinedProductItems = $oOriginalItem->JoinedProductItems;
    
    $oItem->ProductBurden  = $oOriginalItem->ProductBurden;
    $oItem->ProductUnitID  = $oOriginalItem->ProductUnitID;
    $oItem->ProductItems  = $oOriginalItem->ProductItems;
    $oItem->ProductItemQuantity  = $oOriginalItem->ProductItemQuantity;
    $oItem->ProductMaxUserOrder  = $oOriginalItem->ProductMaxUserOrder;
    $oItem->ProductMaxCoopOrder  = $oOriginalItem->ProductMaxCoopOrder;
    $oItem->PackageSize  = $oOriginalItem->PackageSize;
    $oItem->UnitInterval  = $oOriginalItem->UnitInterval;
    $oItem->ProductName  = $oOriginalItem->ProductName;
    $oItem->ProducerName  = $oOriginalItem->ProducerName;
    $oItem->UnitAbbrev  = $oOriginalItem->UnitAbbrev;
    $oItem->ItemUnitAbbrev  = $oOriginalItem->ItemUnitAbbrev;
    $oItem->ChangedByCoordinator  = $oOriginalItem->ChangedByCoordinator;
    $oItem->ProductTotalCoopOrderQuantity = $oOriginalItem->ProductTotalCoopOrderQuantity;
    
    $oItem->StorageAreaBurden  = $oOriginalItem->StorageAreaBurden;
    $oItem->StorageAreaMaxBurden = $oOriginalItem->StorageAreaMaxBurden;
        
    $this->m_aData[self::PROPERTY_ORDER_ITEMS][$nProductID] = $oItem;
   }
   
   return $nProductID;
 }
 
 protected function CollectProducerTotals(&$oItem)
 {
   $oProducer = NULL;
   
   if (!array_key_exists($oItem->ProducerID, $this->m_aProducerTotals))
   {
     $oProducer = new CoopOrderProducer;
     $oProducer->ProducerID = $oItem->ProducerID;
   }
   else
     $oProducer = $this->m_aProducerTotals[$oItem->ProducerID];
   
   $oProducer->ProducerTotal = $oProducer->ProducerTotal + $oItem->ProducerTotal;
   $oProducer->TotalBurden = $oProducer->TotalBurden + $oItem->Burden;
   
   $this->m_aProducerTotals[$oItem->ProducerID] = $oProducer;
 }
 
 //producer related validations
 protected function ValidateProducer(&$recProducer)
 {
   global $g_oError;

   $bValid = TRUE;
   
   //exit if producer is not to be validated, due to no totals
   if (!array_key_exists($recProducer["ProducerKeyID"], $this->m_aProducerTotals))
     return $bValid;
   
   //exit if producer is not to be validated, due to no restrictions
   if ($recProducer["mMaxProducerOrder"] == NULL && $recProducer["fMaxBurden"] == NULL)
     return $bValid;
   
   $mChangedTotal = 0;
   $mOriginalProducerTotal = 0;
   $fOriginalBurden = 0;
   
   $oProducer = $this->m_aProducerTotals[$recProducer["ProducerKeyID"]];
   
   //get values for order
   $recOrderOriginalTotals = $this->GetOrderProducerTotals($recProducer["ProducerKeyID"]);
   if ($recOrderOriginalTotals) //for empty order it would be null
   {
      $mOriginalProducerTotal = $recOrderOriginalTotals["ProducerTotal"];
      $fOriginalBurden = $recOrderOriginalTotals["TotalBurden"];
   }
   //total producer validation
   if ($recProducer["mMaxProducerOrder"] != NULL)
   {
     //change producer total
     $mChangedTotal = $recProducer["mProducerTotal"] 
      - $mOriginalProducerTotal
      + $oProducer->ProducerTotal;

     //ignore if producer total was not enlarged
     if ($mChangedTotal > $recProducer["mProducerTotal"])
     {
        //validate producer 
        if ($mChangedTotal > $recProducer["mMaxProducerOrder"])
        {
          //add error reason for coordinators
          if ($this->m_oOrder->HasPermission(SQLBase::PERMISSION_COORD))
            $g_oError->AddError(sprintf('The field Total Coop for the cooperative order&#x27;s producer %s has exeeded the value of the producer&#x27;s Max. Prodcuer Order.',$recProducer["sProducer"]));
          $bValid = FALSE;
        }
     }
   }
   //burden validation
   if ($recProducer["fMaxBurden"] != NULL)
   {
     //change producer total
     $mChangedTotal = $recProducer["fBurden"] 
      - $fOriginalBurden
      + $oProducer->TotalBurden;

     //ignore if producer total was not enlarged
     if ($mChangedTotal > $recProducer["fBurden"])
     {
        //validate producer 
        if ($mChangedTotal > $recProducer["fMaxBurden"])
        {
          //add error reason for coordinators
          if ($this->m_oOrder->HasPermission(SQLBase::PERMISSION_COORD))
            $g_oError->AddError(sprintf('The field Total Burden for the cooperative order&#x27;s producer %s has exeeded the value of the producer&#x27;s Delivery Capacity.',$recProducer["sProducer"]));
          $bValid = FALSE;
        }
     }
   }
   
   if (!$bValid)
     $g_oError->AddError(sprintf('This order cannot be enlarged beyond the full capacity of the cooperative order&#x27;s producer %s.',$recProducer["sProducer"]));
   
   return $bValid;
 }
 
 protected function GetOrderProducerTotals($nProducerID)
 {
    $sSQL =   " SELECT IfNull(SUM(OI.mProducerPrice),0) ProducerTotal, " . 
          " IfNull(SUM(IfNull(COPRD.fBurden,0) * IfNull( OI.fQuantity/NullIf(PRD.fQuantity,0),0) ),0) as TotalBurden " .
          " FROM T_Order O INNER JOIN T_OrderItem OI ON O.OrderID = OI.OrderID " .
          " INNER JOIN T_CoopOrderProduct COPRD ON COPRD.CoopOrderKeyID = O.CoopOrderKeyID AND COPRD.ProductKeyID = OI.ProductKeyID " .
          " INNER JOIN T_Product PRD ON PRD.ProductKeyID = OI.ProductKeyID " .
          " WHERE O.OrderID = " . $this->m_oOrder->ID . "  AND PRD.ProducerKeyID = " . $nProducerID;
    
    $this->m_bUseSecondSqlPreparedStmt = TRUE;
   
    $this->RunSQL( $sSQL );
    
    $recSums = $this->fetch();
    
    $this->m_bUseSecondSqlPreparedStmt = FALSE;    
    
    return $recSums;
 }
 
 //product related validations
 protected function ValidateProduct(&$oOrderItem, &$oOriginalItem)
 {
   //validate gap between possible values
   $fGap = 1;
   if ($oOrderItem->ProductUnitID != Consts::UNIT_ITEMS)
     $fGap = $oOrderItem->UnitInterval;

   if (fmod($oOrderItem->Quantity,$fGap) != 0)
   {
     $oOrderItem->InvalidEntry = TRUE;
     $oOrderItem->ValidationMessage .= sprintf('Quantity entered is not valid. Must enter values in multiples of %s<br/>', $fGap);
   }
   
   //validate joined product - cannot reduce quantity
   if ($oOrderItem->UnjoinedQuantity < 0)
   {
     $oOrderItem->InvalidEntry = TRUE;
     $oOrderItem->ValidationMessage .= 'Cannot reduce quantities for this product at this time, because quantities were joined to a cost-saving larger package. Please contact the cooperative order coordinator for help<br/>';
   }
   
   //validate max user order per product
   if ($oOrderItem->ProductMaxUserOrder != NULL)
   {
     if ($oOrderItem->Quantity > $oOrderItem->ProductMaxUserOrder)
     {
        $oOrderItem->InvalidEntry = TRUE;
        $oOrderItem->ValidationMessage .= 'Quantity entered exceeds the maximum order per member for this product<br/>';
     }
   }
   //validate max coop order per product
   if ($oOrderItem->ProductMaxCoopOrder != NULL)
   {
     
     //change to include_once this new item
     $fChangedQuantity = $oOrderItem->ProductTotalCoopOrderQuantity 
         - $oOriginalItem->Quantity
         + $oOrderItem->Quantity;
     
     //validate only if greater
     if ($fChangedQuantity > $oOrderItem->ProductTotalCoopOrderQuantity)
     {
       if ($fChangedQuantity > $oOrderItem->ProductMaxCoopOrder)
       {
          $oOrderItem->InvalidEntry = TRUE;
          $oOrderItem->ValidationMessage .= 'Quantity entered causes the entire cooperative to exceed the maximum order for this product<br/>';
       }
     }
   }
   
   //validate storage area burden, if there's been an increase
   if ($oOrderItem->StorageAreaMaxBurden > 0 && $oOrderItem->Burden > $oOriginalItem->Burden)
   {
     $fAddedBurden = $oOrderItem->Burden - $oOriginalItem->Burden;
     if ($oOrderItem->StorageAreaBurden + $fAddedBurden > $oOrderItem->StorageAreaMaxBurden)
     {
       $oOrderItem->InvalidEntry = TRUE;
       $oOrderItem->ValidationMessage .= 'Cannot save the requested quantity for this item since there is not enough free storage area.<br/>';
     }
   }
 }
 
 //helper function to raise flag that an item's quantity was modified by a ccordinator
 protected function SetItemChangedByCoordinator(&$oOrderItem)
 {
   //values are FALSE by default
   
   //item's quantity was never set by member
   if ($oOrderItem->MemberLastQuantity == NULL)
     return;
   
   if ($oOrderItem->MemberLastQuantity != $oOrderItem->Quantity)
   {
    $oOrderItem->ChangedByCoordinator = TRUE;
    $this->m_oOrder->ItemsChangedByCoordinator = TRUE;
   }
 }
   
}

?>
