<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faciliates coord/orders.php page for a specific coop order member orders (coordinator view only)
class Orders extends CoopOrderSubBase {
  
 const POST_ACTION_JOIN_PRODUCTS = 10;
 const POST_ACTION_UNJOIN_PRODUCTS = 11;
 
 const SORT_FIELD_CREATE_DATE = 1;
 const SORT_FIELD_MEMBER_NAME = 2;
 
 const PERMISSION_COOP_ORDER_ORDERS = 100;
 
 const PROPERTY_ORIGINAL_GROUP_ID = "OriginalGroupID";
  
 public function __construct()
 { 
   
   $this->m_aSortFields = array(
       self::SORT_FIELD_CREATE_DATE => array(self::IND_SORT_FIELD_NAME => "O.dCreated", 
                                            self::IND_SORT_FIELD_ORDER => Consts::SORT_ORDER_DESCENDING),
       self::SORT_FIELD_MEMBER_NAME => array(self::IND_SORT_FIELD_NAME => "M.sName", 
                                            self::IND_SORT_FIELD_ORDER => Consts::SORT_ORDER_ASCENDING)
       );
   
   $this->m_aDefaultData = array( self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            Order::PROPERTY_MEMBER_ID => 0,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE => NULL,
                            CoopOrder::PROPERTY_SMALL_ORDER => NULL,
                            CoopOrder::PROPERTY_COOP_FEE => NULL,
                            CoopOrder::PROPERTY_COOP_FEE_PERCENT => NULL,
                            self::PROPERTY_SORT_FIELD => self::SORT_FIELD_CREATE_DATE,
                            self::PROPERTY_SORT_ORDER => Consts::SORT_ORDER_DESCENDING
                            );
   
   $this->m_aOriginalData = $this->m_aDefaultData;
   $this->m_aData = $this->m_aDefaultData;
 }
 
  public function __get( $name ) {
    switch ($name)
    {
      case self::PROPERTY_ORIGINAL_GROUP_ID:
        return $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];
      default:
        return parent::__get($name);
    }
  }
 
 public function LoadDataByCoopOrder()
 {
    $this->m_aOriginalData = NULL; //trigger data reload
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    $this->m_aOriginalData = $this->m_aData;
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_ORDERS, Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL = " SELECT O.OrderID, O.MemberID, O.PickupLocationKeyID, O.dCreated, O.dModified, O.bHasItemComments, O.mCoopTotalIncFee as OrderCoopTotal, O.sMemberComments, "  .
              " O.CreatedByMemberID, O.ModifiedByMemberID, M.mBalance, M.sName as MemberName, MC.sName as CreateMemberName, MM.sName as ModifyMemberName,  " .
              " M.sLoginName, M.sEMail, M.sEMail2, M.sEMail3, M.sEMail4, M.PaymentMethodKeyID, M.mBalance, M.fPercentOverBalance, " .
                     $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
              ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, 'sPickupLocationAddress') .
              ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS, 'sPickupLocationComments') .
              " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " . 
              " LEFT JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = O.PickupLocationKeyID " . 
              " LEFT JOIN T_Member MC ON MC.MemberID = O.CreatedByMemberID  " . 
              " LEFT JOIN T_Member MM ON MM.MemberID = O.ModifiedByMemberID  " . 
              $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
              $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
              $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
              " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] 
              . $this->ConcatSortSQL() . ";";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
}

?>
