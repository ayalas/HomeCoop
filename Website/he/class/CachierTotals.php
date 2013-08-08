<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

class CachierTotals extends SQLBase {
  
  const PROPERY_TOTAL_BALANCE = "TotalMemberBalances";
  const PROPERY_TOTAL_CACHIER = "TotalPickupLocationCachiers";
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERY_TOTAL_BALANCE => 0,
                            self::PROPERY_TOTAL_CACHIER => 0
                            );
  }
  
  public function GetData()
  {
    if (!$this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_CACHIER_TOTALS, 
           Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    $sSQL = " SELECT SUM(mBalanceHeld) as SumBalance FROM T_Member;";
    $this->RunSQL($sSQL);
        
    $recSum = $this->fetch();
    if ($recSum)
      $this->m_aData[self::PROPERY_TOTAL_BALANCE] = $recSum["SumBalance"];
    
    $sSQL = " SELECT SUM(mCachier) as SumCachier FROM T_PickupLocation;";
    $this->RunSQL($sSQL);
        
    $recSum = $this->fetch();
    if ($recSum)
      $this->m_aData[self::PROPERY_TOTAL_CACHIER] = $recSum["SumCachier"];
    
    return TRUE;
  }
}

?>
