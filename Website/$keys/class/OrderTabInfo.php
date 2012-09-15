<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates member order tab
class OrderTabInfo extends SQLBase {
  const PAGE_NONE = 0;
  const PAGE_ENTRY = 1;
  const PAGE_ITEMS = 2;
  
  const PROPERTY_PAGE = "Page";
  const PROPERTY_MAIN_TAB_NAME = "MainTabName";
  
  public function __construct($nID, $nPage, $mCoopTotal, $mCoopFee)
  {
    $this->m_aData = array( Order::PROPERTY_ID => $nID,
                            self::PROPERTY_PAGE => $nPage,
                            Order::PROPERTY_COOP_TOTAL => $mCoopTotal,
                            Order::PROPERTY_COOP_FEE => $mCoopFee,
                            CoopOrderTabInfo::PROPERTY_CAPACITY => NULL,
                            CoopOrderTabInfo::PROPERTY_COOP_ORDER_STATUS_OBJECT => NULL,
                            self::PROPERTY_MAIN_TAB_NAME => NULL
                            );
  }
}

?>
