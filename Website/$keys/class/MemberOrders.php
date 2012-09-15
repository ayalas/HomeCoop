<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates member orders page - orders.php - (either for current user, or any user - for coordinators)
class MemberOrders extends SQLBase{
  const POST_ACTION_SWITCH_MEMBER = 11;
  const PROPERTY_MEMBER_NAME = "MemberName";
    
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_MEMBER_NAME => NULL,
                           Order::PROPERTY_MEMBER_ID => 0
                    );
  }
  
  protected function CheckAccess()
  {
    global $g_oMemberSession;
   
    $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if ( $this->m_aData[Order::PROPERTY_MEMBER_ID] == $g_oMemberSession->MemberID)
    {
      $this->m_aData[self::PROPERTY_MEMBER_NAME] == $g_oMemberSession->Name;
    
      $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    }
    
    return $this->HasPermissions(array(self::PERMISSION_COORD,self::PERMISSION_PAGE_ACCESS));
  }
  
  public function LoadDataByMember()
 {
   global $g_oMemberSession;
   if (!$this->CheckAccess())
   {
     $this->m_nLastOperationStatus = self::OPERATION_STATUS_NO_PERMISSION;
     return NULL;
   }
   
    $sSQL = " SELECT O.OrderID,O.CoopOrderKeyID, O.MemberID, O.PickupLocationKeyID,  (IfNull(O.mCoopFee,0) + O.mCoopTotal) as OrderCoopTotal, "  .
              " CO.dEnd, CO.dDelivery, CO.nStatus, O.sMemberComments, O.dCreated, O.bHasItemComments, " .
                     $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
             "," .   $this->ConcatStringsSelect(Consts::PERMISSION_AREA_COOP_ORDERS, 'sCoopOrder') .
              " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " . 
              " INNER JOIN T_CoopOrder CO ON O.CoopOrderKeyID = CO.CoopOrderKeyID " .
              " LEFT JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = O.PickupLocationKeyID " . 
              " LEFT JOIN T_Member MC ON MC.MemberID = O.CreatedByMemberID  " . 
              " LEFT JOIN T_Member MM ON MM.MemberID = O.ModifiedByMemberID  " . 
              $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
               $this->ConcatStringsJoin(Consts::PERMISSION_AREA_COOP_ORDERS) .
              " WHERE O.MemberID = " . $this->m_aData[Order::PROPERTY_MEMBER_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE)        
      $sSQL .=     " AND CO.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";
    
     $sSQL .= " ORDER BY O.dCreated desc;";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
}

?>
