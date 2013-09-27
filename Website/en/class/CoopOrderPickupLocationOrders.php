<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;


//view orders according to pickup location
//for pickup location coordinators this is the only view they can access
class CoopOrderPickupLocationOrders extends CoopOrderPickupLocationSubBase {
  
  const SORT_FIELD_CREATE_DATE = 1;
  const SORT_FIELD_MEMBER_NAME = 2;
  
  const PERMISSION_COOP_ORDER_PRODUCER_VIEW = 400;
  
  public function __construct()
  {
    $this->m_aSortFields = array(
       self::SORT_FIELD_CREATE_DATE => array(self::IND_SORT_FIELD_NAME => "O.dCreated", 
                                            self::IND_SORT_FIELD_ORDER => Consts::SORT_ORDER_DESCENDING),
       self::SORT_FIELD_MEMBER_NAME => array(self::IND_SORT_FIELD_NAME => "M.sName", 
                                            self::IND_SORT_FIELD_ORDER => Consts::SORT_ORDER_ASCENDING)
       );
    
    $this->m_aOriginalData = array( self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_PICKUP_LOCATION_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            self::PROPERTY_COOP_ORDER_STORAGE_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN => NULL,
                            CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID => 0,
                            self::PROPERTY_PICKUP_LOCATION_COORD_GROUP_ID => 0,
                            self::PROPERTY_SORT_FIELD => self::SORT_FIELD_CREATE_DATE,
                            self::PROPERTY_SORT_ORDER => Consts::SORT_ORDER_DESCENDING
                            );
    
    $this->m_aData = $this->m_aOriginalData;
  }
  
 public function PreserveFields()
 {
   $this->m_aData[self::PROPERTY_COOP_ORDER_ID] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];
   $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID] = $this->m_aOriginalData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID];
 }
 
 public function LoadData()
 {
    global $g_oMemberSession;
    
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    if (!$this->LoadCoopOrderPickupLocationData(Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS))
      return NULL;
    
    $this->m_aOriginalData = $this->m_aData;

    $sSQL = " SELECT O.OrderID, O.MemberID, O.dCreated, O.dModified, O.bHasItemComments, O.mCoopTotalIncFee as OrderCoopTotal, O.sMemberComments, "  .
          " O.CreatedByMemberID, O.ModifiedByMemberID, M.mBalance, M.sName as MemberName, MC.sName as CreateMemberName, MM.sName as ModifyMemberName,  " .
          " M.sLoginName, M.sEMail, M.sEMail2, M.sEMail3, M.sEMail4, M.PaymentMethodKeyID, M.mBalance, M.fPercentOverBalance " .
          " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " . 
          " LEFT JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = O.PickupLocationKeyID " . 
          " LEFT JOIN T_Member MC ON MC.MemberID = O.CreatedByMemberID  " . 
          " LEFT JOIN T_Member MM ON MM.MemberID = O.ModifiedByMemberID  " . 
          " WHERE O.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . 
          " AND O.PickupLocationKeyID = " . $this->m_aData[CoopOrderPickupLocation::PROPERTY_PICKUP_LOCATION_ID] . 
            $this->ConcatSortSQL() . ";";

    $this->RunSQL( $sSQL );

    return $this->fetch();
    
 }
}

?>
