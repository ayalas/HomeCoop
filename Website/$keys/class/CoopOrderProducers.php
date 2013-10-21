<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class CoopOrderProducers extends CoopOrderSubBase {
  
  const PERMISSION_COOP_ORDER_PRODUCER_EDIT = 100;
  const PERMISSION_COOP_ORDER_PRODUCER_VIEW = 101;
  
 public function __construct()
 {
   parent::__construct();
 }

 public function LoadData()
 {
    global $g_oMemberSession;
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL =   " SELECT COP.ProducerKeyID, COP.mMaxProducerOrder, COP.mCoopTotal, COP.mProducerTotal, COP.fDelivery, COP.mDelivery, COP.mMinDelivery, " . 
              " COP.mMaxDelivery, COP.mTotalDelivery, P.CoordinatingGroupID,IfNull(COP.fBurden,0) fBurden, COP.fMaxBurden, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          " FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          " WHERE COP.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_EDIT) != Consts::PERMISSION_SCOPE_COOP_CODE &&
        $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW) != Consts::PERMISSION_SCOPE_COOP_CODE)
            $sSQL .=  " AND P.CoordinatingGroupID IN ( " . implode(",", $g_oMemberSession->Groups) . ") ";
    $sSQL .= " ORDER BY P_S.sString; ";

    $this->RunSQL( $sSQL );
    
    return $this->fetch();
 }
 
 public function LoadCoordList()
 {
    global $g_oMemberSession;
    if (!$this->LoadCoopOrderData())
      return NULL;
    
    $bEdit = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_EDIT, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if (!$bEdit && !$bView)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL =   " SELECT COP.ProducerKeyID, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          " FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          " WHERE COP.CoopOrderKeyID = " . $this->m_aData[parent::PROPERTY_COOP_ORDER_ID];
    if ($this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_EDIT) != Consts::PERMISSION_SCOPE_COOP_CODE &&
        $this->GetPermissionScope(self::PERMISSION_COOP_ORDER_PRODUCER_VIEW) != Consts::PERMISSION_SCOPE_COOP_CODE)
            $sSQL .=  " AND P.CoordinatingGroupID IN ( " . implode(",", $g_oMemberSession->Groups) . ") ";
    $sSQL .= " ORDER BY P_S.sString; ";

    $this->RunSQL( $sSQL );
    
    return $this->fetchAllKeyPair();
 }
 
 //unlike equivalent pickup location function, this one is used only in the home page orders boxes
 //and that's why it's checking only for member "can order" permissions
 public function LoadList($CoopOrderID)
 {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
   
    if (!$this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL =   " SELECT COP.ProducerKeyID, COP.mTotalDelivery, COP.mMaxProducerOrder, IfNull(COP.mProducerTotal,0) mProducerTotal,  " .
          " P.sExportFileName, IfNull(COP.fBurden,0) fBurden, COP.fMaxBurden, P.CoordinatingGroupID, " .
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
          " FROM T_CoopOrderProducer COP INNER JOIN T_Producer P ON COP.ProducerKeyID = P.ProducerKeyID " . 
          $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
          " WHERE COP.CoopOrderKeyID = " . $CoopOrderID;
    $sSQL .= " ORDER BY P_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetch();
 }
 
}

?>
