<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faclitates the coord/pickuplocs.php coordinator's page, showing a grid of all pickup locations in the system
//also facilitates coop order pickup locations addition
class PickupLocations extends SQLBase {
  public function GetTable()
  {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
            
      if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      //check for add permissions
      $this->AddPermissionBridge(self::PERMISSION_ADD, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_ADD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      //check for coord setting permissions
      $this->AddPermissionBridge(self::PERMISSION_COORD_SET, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_COORD_SET, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);


      $sSQL =   " SELECT PL.PickupLocationKeyID, PL.AddressStringKeyID, PL.fMaxBurden, PL.PublishedCommentsStringKeyID, PL.AdminCommentsStringKeyID, PL.CoordinatingGroupID," .
                " PL.bDisabled, PL.nRotationOrder, PL.mCachier, PL.dCachierUpdate, " . 
                       $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
                ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, 'sAddress') .
                " FROM T_PickupLocation PL " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
                $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) ;
     
      if ( $this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
          $sSQL .=  " WHERE PL.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";

      $sSQL .= " ORDER BY PL.bDisabled, PL.nRotationOrder, PL_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetch();
  }
  
  //get pickup location list for coop order - for coordinators only
  public function GetListForCoopOrder($nCurrentPickupLocationID, $nCoopOrderID)
  {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
            
      $bEdit = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      if (!$bEdit && !$bView)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      if ($nCoopOrderID <= 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return NULL;
      }
      
      if ($nCurrentPickupLocationID === NULL)
        $nCurrentPickupLocationID = 0;

      $sSQL =   " SELECT PL.PickupLocationKeyID, " . 
               $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
        " FROM T_PickupLocation PL LEFT JOIN T_CoopOrderPickupLocation COPL ON COPL.PickupLocationKeyID = PL.PickupLocationKeyID AND COPL.CoopOrderKeyID = " 
        .  $nCoopOrderID . " " .
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
        " WHERE (";

      if ($bEdit)
        $sSQL .= "(COPL.PickupLocationKeyID IS NULL AND PL.bDisabled = 0) OR ";
        
      $sSQL .= " (PL.PickupLocationKeyID = " . $nCurrentPickupLocationID . " )) ";
      if ( $bEdit && $this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE )              
          $sSQL .=  " AND PL.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";

      $sSQL .= " ORDER BY PL.nRotationOrder, PL_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetchAllKeyPair();
  }
  
}

?>
