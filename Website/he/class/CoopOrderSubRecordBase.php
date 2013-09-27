<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//base class for coop order record edit sub pages - pickup location/producer/product/order/export
class CoopOrderSubRecordBase extends CoopOrderSubBase {
  
  const PROPERTY_IS_EXISTING_RECORD = "IsExistingRecord";
  
  public function __get( $name ) {
    switch ($name)
    {
      case self::PROPERTY_IS_EXISTING_RECORD:
        if ($this->m_aOriginalData != NULL)
          return $this->m_aOriginalData[self::PROPERTY_IS_EXISTING_RECORD];
        return FALSE;
      default:
        return parent::__get($name);
    }
  }
  
  protected function VerifyAction()
  {
    global $g_oError;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    $bModify = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
     
    $bView = $this->AddPermissionBridge(self::PROPERTY_PERMISSION_COOP_ORDER_VIEW, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
     
    if ( !$bModify && !$bView)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }  
    
    //allow updating only active, locked and draft orders
    if (    $this->m_aData[self::PROPERTY_STATUS] != CoopOrder::STATUS_ACTIVE 
        &&  $this->m_aData[self::PROPERTY_STATUS] != CoopOrder::STATUS_DRAFT
        &&  $this->m_aData[self::PROPERTY_STATUS] != CoopOrder::STATUS_LOCKED )
    {
      $g_oError->AddError('לא ניתן לעדכן את הזמנת הקואופרטיב במצב הנוכחי שלה');
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    return TRUE;
  }
  
  //after delete operation, clean all other data, but preserve coop order data
  protected function PreserveCoopOrderData()
  {
    $this->m_aDefaultData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];
    $this->m_aDefaultData[self::PROPERTY_STATUS] = $this->m_aOriginalData[self::PROPERTY_STATUS];
    $this->m_aDefaultData[self::PROPERTY_NAME] = $this->m_aOriginalData[self::PROPERTY_NAME];
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_ID] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];
    
    $this->m_aDefaultData[CoopOrder::PROPERTY_END] = $this->m_aOriginalData[CoopOrder::PROPERTY_END];
    $this->m_aDefaultData[CoopOrder::PROPERTY_DELIVERY] = $this->m_aOriginalData[CoopOrder::PROPERTY_DELIVERY];
    $this->m_aDefaultData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS] = $this->m_aOriginalData[CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS];
    
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_BURDEN];
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_MAX_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_MAX_BURDEN];
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_MAX_COOP_TOTAL];
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_COOP_TOTAL];
    
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_STORAGE_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_STORAGE_BURDEN];
    $this->m_aDefaultData[self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_MAX_STORAGE_BURDEN];
        
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
}

?>
