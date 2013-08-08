<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//coop orders view for coordinators
class CoopOrders extends SQLBase {
  
  const PERMISSION_SUMS = 10;
  
  public function CanCopy()
  {
    return $this->AddPermissionBridge(self::PERMISSION_COPY, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_COPY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
  
  protected function CanViewSums()
  {
    $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_SUMS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);  
  }
  
  public function GetTable()
  {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

      $bView = $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      if (!$bView && !$bCoord)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
            
      $this->CanCopy(); //check copy permissions
      $this->CanViewSums();
      $this->AddPermissionBridge(self::PERMISSION_COORD_SET, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_COORD_SET, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

      $sSQL =   " SELECT CO.CoopOrderKeyID, CO.dStart, CO.dEnd, CO.dDelivery, CO.ModifiedByMemberID, M.sName as ModifierName, CO.nStatus, CO.CoordinatingGroupID," .
                " CO.mMaxCoopTotal, CO.fMaxBurden, CO.mCoopTotal, CO.mProducerTotal, IfNull(CO.fBurden,2) fBurden, CO.mTotalDelivery, " . 
                       $this->ConcatStringsSelect(Consts::PERMISSION_AREA_COOP_ORDERS, 'sCoopOrder') .
                " FROM T_CoopOrder CO INNER JOIN T_Member M ON M.MemberID = CO.ModifiedByMemberID " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_COOP_ORDERS);
     
      if ( $this->GetPermissionScope(self::PERMISSION_PAGE_ACCESS) != Consts::PERMISSION_SCOPE_COOP_CODE 
           && $this->GetPermissionScope(self::PERMISSION_COORD) != Consts::PERMISSION_SCOPE_COOP_CODE )
          $sSQL .=  " WHERE CO.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";

      $sSQL .= " ORDER BY CO.dDelivery desc; ";

      $this->RunSQL( $sSQL );

      return $this->fetch();
  }
  
 
}

?>
