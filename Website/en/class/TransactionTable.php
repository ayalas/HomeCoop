<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class TransactionTable extends SQLBase {
  
  const PROPERTY_FILTER_PICKUP_LOCATION_ID = "PickupLocationID";
  const PROPERTY_PICKUP_LOCATION_GROUP_ID = "PickupLocationGroupID";
  const PROPERTY_FILTER_MEMBER_ID = "MemberID";
  const PROPERTY_TABLE = "Table";
  const PERMISSION_ALL_MEMBERS_TRANSACTION = 100;
  const PERMISSION_PICKUP_LOCATION_TRANSACTION = 101;
  
  public function __construct()
  {
    $this->m_aData = array( 
        self::PROPERTY_FILTER_PICKUP_LOCATION_ID => 0,
        self::PROPERTY_FILTER_MEMBER_ID => 0,
        self::PROPERTY_TABLE => NULL,
        self::PROPERTY_PICKUP_LOCATION_GROUP_ID => 0,
       );
  }
  
  public function CheckAccess()
  {
    global $g_oMemberSession;
    
    if (!empty($this->m_aData[self::PROPERTY_FILTER_MEMBER_ID]))
    {
      if ($g_oMemberSession->MemberID == $this->m_aData[self::PROPERTY_FILTER_MEMBER_ID])
        return TRUE;
      
      return $this->HasPermission(self::PERMISSION_ALL_MEMBERS_TRANSACTION) || $this->AddPermissionBridge(self::PERMISSION_ALL_MEMBERS_TRANSACTION, Consts::PERMISSION_AREA_TRANSACTIONS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
    }
    
    if (!empty($this->m_aData[self::PROPERTY_FILTER_PICKUP_LOCATION_ID]))
    {
      return $this->AddPermissionBridge(self::PERMISSION_PICKUP_LOCATION_TRANSACTION, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_PICKUP_LOCATION_GROUP_ID], FALSE);
    }
    
    //must have global transactions permission if no filter is defined
    return $this->HasPermission(self::PERMISSION_ALL_MEMBERS_TRANSACTION) || $this->AddPermissionBridge(self::PERMISSION_ALL_MEMBERS_TRANSACTION, Consts::PERMISSION_AREA_TRANSACTIONS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
  }
    
  public function LoadTable()
  {    
    if (!$this->CheckAccess())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    $sWhere = '';
    
    $sSQL = 'SELECT T.TransactionID, T.PickupLocationKeyID, T.MemberID, T.ModifiedByMemberID, T.mAmount, T.dDate, T.sTransaction, ' .
        ' M.sName MemberName, MM.sName ModifierName, ' .
        $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
        ' FROM T_Transaction T LEFT JOIN T_PickupLocation PL ON T.PickupLocationKeyID = PL.PickupLocationKeyID ' .
        ' LEFT JOIN T_Member M ON T.MemberID = M.MemberID LEFT JOIN T_Member MM ON T.ModifiedByMemberID = MM.MemberID ' .
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS);
    
    if (!empty($this->m_aData[self::PROPERTY_FILTER_PICKUP_LOCATION_ID]))
      $sWhere .= ' T.PickupLocationKeyID = ' . (0 + $this->m_aData[self::PROPERTY_FILTER_PICKUP_LOCATION_ID]);
    
    if (!empty($this->m_aData[self::PROPERTY_FILTER_MEMBER_ID]))
      $sWhere .= ' T.MemberID = ' . (0 + $this->m_aData[self::PROPERTY_FILTER_MEMBER_ID]);
    
    if (!empty($sWhere))
      $sSQL .= ' WHERE ' . $sWhere;
    
    $this->RunSQL(HomeCoopPager::Process($sSQL, " ORDER BY TransactionID DESC "));
    
    return $this->fetch();
  }
}

?>
