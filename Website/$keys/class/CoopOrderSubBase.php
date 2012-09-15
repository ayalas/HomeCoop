<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//base class for coop order sub pages - pickup locations/producers/products/orders/export
//general data and permissions
class CoopOrderSubBase extends SQLBase {
  
  const PROPERTY_NAME = "Name";
  const PROPERTY_STATUS = "Status";
  const PROPERTY_COOP_ORDER_ID = "CoopOrderID";
  
  const PROPERTY_COOP_ORDER_BURDEN = "CoopOrderBurden";
  const PROPERTY_COOP_ORDER_MAX_BURDEN = "CoopOrderMaxBurden";
  const PROPERTY_COOP_ORDER_COOP_TOTAL = "CoopOrderCoopTotal";
  const PROPERTY_COOP_ORDER_MAX_COOP_TOTAL = "CoopOrderMaxCoopTotal";
  
  const PROPERTY_PERMISSION_COOP_ORDER_VIEW = 200;
  const PROPERTY_PERMISSION_COOP_ORDER_EDIT = 201;
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_NAME => NULL,
                            self::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_COOP_ORDER_BURDEN => 0,
                            self::PROPERTY_COOP_ORDER_MAX_BURDEN => NULL,
                            self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0
                            );
  }
  
  public function CheckAccess()
  {
     $bModify = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     $bView = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_VIEW, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     return ($bModify || $bView);
  }
  
  protected function CheckPermissionAfterGroupAdd()
  {    
    $bModify = $this->AddPermissionBridgeGroupID(self::PROPERTY_PERMISSION_COOP_ORDER_EDIT, FALSE);
    $bView = $this->AddPermissionBridgeGroupID(self::PROPERTY_PERMISSION_COOP_ORDER_VIEW, FALSE);
        
    return ($bModify || $bView);
  }
  
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_COORDINATING_GROUP_ID:
      case self::PROPERTY_STATUS:
      case self::PROPERTY_NAME:
        $trace = debug_backtrace();
        trigger_error(
          'Undefined property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
      default:
        parent::__set( $name, $value );
    }
  }
  
   public function CopyCoopOrderData()
  {
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];
    $this->m_aData[self::PROPERTY_STATUS] = $this->m_aOriginalData[self::PROPERTY_STATUS];
    $this->m_aData[self::PROPERTY_NAME] = $this->m_aOriginalData[self::PROPERTY_NAME];
    $this->m_aData[self::PROPERTY_COOP_ORDER_ID] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];
    $this->m_aData[CoopOrder::PROPERTY_END] = $this->m_aOriginalData[CoopOrder::PROPERTY_END];
    $this->m_aData[CoopOrder::PROPERTY_DELIVERY] = $this->m_aOriginalData[CoopOrder::PROPERTY_DELIVERY];
    $this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] = $this->m_aOriginalData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS];
    
    $this->m_aData[self::PROPERTY_COOP_ORDER_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_BURDEN];
    $this->m_aData[self::PROPERTY_COOP_ORDER_MAX_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_MAX_BURDEN];
    $this->m_aData[self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL];
    $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_COOP_TOTAL];    
    
    if (array_key_exists(CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE, $this->m_aData))
      $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE] = $this->m_aOriginalData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE];

    if (array_key_exists(CoopOrder::PROPERTY_SMALL_ORDER, $this->m_aData))
      $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER] = $this->m_aOriginalData[CoopOrder::PROPERTY_SMALL_ORDER];

    if (array_key_exists(CoopOrder::PROPERTY_COOP_FEE, $this->m_aData))
      $this->m_aData[CoopOrder::PROPERTY_COOP_FEE] = $this->m_aOriginalData[CoopOrder::PROPERTY_COOP_FEE];

    if (array_key_exists(CoopOrder::PROPERTY_COOP_FEE_PERCENT, $this->m_aData))
      $this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] = $this->m_aOriginalData[CoopOrder::PROPERTY_COOP_FEE_PERCENT];
  }
  
  protected function LoadCoopOrderData()
  {
    global $g_oMemberSession;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //general permission check
    if ( !$this->CheckAccess() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    if ($this->m_aOriginalData != NULL && array_key_exists(self::PROPERTY_NAME, $this->m_aOriginalData) && $this->m_aOriginalData[self::PROPERTY_NAME] != NULL)
      $this->CopyCoopOrderData();
    else
    {
      $sSQL =   " SELECT CO.nStatus, CO.mMaxCoopTotal,  CO.fMaxBurden, CO.bHasJoinedProducts,  " . 
                " CO.mSmallOrderCoopFee, CO.mSmallOrder, CO.mCoopFee, CO.fCoopFee, " .
              " IfNull(CO.fBurden,0) fBurden, CO.mCoopTotal, CO.CoordinatingGroupID,CO.dEnd,CO.dDelivery, " .
                $this->ConcatStringsSelect(Consts::PERMISSION_AREA_COOP_ORDERS, 'sCoopOrder') .
                " FROM T_CoopOrder CO " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_COOP_ORDERS) .
                " WHERE CO.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] . ';';

      $this->RunSQL( $sSQL );

      $rec = $this->fetch();

      if (!is_array($rec) || count($rec) == 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }

      $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = 0;
      if ($rec["CoordinatingGroupID"])
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
      
      $this->m_aData[self::PROPERTY_STATUS] = $rec["nStatus"];
      $this->m_aData[self::PROPERTY_NAME] = $rec["sCoopOrder"];
      $this->m_aData[CoopOrder::PROPERTY_END] = new DateTime($rec["dEnd"]);
      $this->m_aData[CoopOrder::PROPERTY_DELIVERY] = new DateTime($rec["dDelivery"]);
      $this->m_aData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] = $rec["bHasJoinedProducts"];
      $this->m_aData[self::PROPERTY_COOP_ORDER_BURDEN] = $rec["fBurden"];
      $this->m_aData[self::PROPERTY_COOP_ORDER_MAX_BURDEN] = $rec["fMaxBurden"];
      $this->m_aData[self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL] = $rec["mMaxCoopTotal"];
      $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $rec["mCoopTotal"];
      
      if (array_key_exists(CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE, $this->m_aData))
        $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE] = $rec["mSmallOrderCoopFee"];
      
      if (array_key_exists(CoopOrder::PROPERTY_SMALL_ORDER, $this->m_aData))
        $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER] = $rec["mSmallOrder"];
      
      if (array_key_exists(CoopOrder::PROPERTY_COOP_FEE, $this->m_aData))
        $this->m_aData[CoopOrder::PROPERTY_COOP_FEE] = $rec["mCoopFee"];
      
      if (array_key_exists(CoopOrder::PROPERTY_COOP_FEE_PERCENT, $this->m_aData))
        $this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] = $rec["fCoopFee"];
    }

    //coordinating group permission check
    if ( !$this->CheckPermissionAfterGroupAdd() )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }

    return TRUE;
  }
}

?>
