<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class MemberPickupLocations extends SQLBase implements IMemberFacet {
  const PROPERTY_MEMBER_NAME = "Name";
  const POST_ACTION_BLOCK = 10;
  const POST_ACTION_FILTER = 11;
  
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_ID => 0, 
                            self::PROPERTY_MEMBER_NAME => NULL);
  }
  
  public function CheckAccess()
  {   
    $bCoord = $this->HasPermission(self::PERMISSION_EDIT) || $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_MEMBER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->HasPermission(self::PERMISSION_COORD) || $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBER_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_COORD, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    return ($bView || $bCoord);
  }
  
  public function GetTableForFacet()
  {
    global $g_oMemberSession;
       
    $this->m_aData[self::PROPERTY_ID] = $g_oMemberSession->MemberID;

    $sSQL = " SELECT PL.PickupLocationKeyID, MPL.PickupLocationKeyID as MPLID, " . 
            $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
            " , IfNUll(MPL.bRemoved,0) bRemoved, PL.bDisabled, IfNull(MPL.bBlocked, 0) bBlocked " .
            " FROM T_PickupLocation PL LEFT JOIN T_MemberPickupLocation MPL ON PL.PickupLocationKeyID = MPL.PickupLocationKeyID " .
            " AND MPL.MemberID =  " . $this->m_aData[self::PROPERTY_ID] . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
            " ORDER BY bBlocked, bRemoved, PL_S.sString;";
    
    $this->RunSQL( $sSQL );

    return $this->fetchAll();
  }
  
  public function GetTable()
  {
    global $g_oMemberSession;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return NULL;
    }
    
    if (!$this->CheckAccess())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    //get member name
    if ($this->m_aData[self::PROPERTY_MEMBER_NAME] == NULL)
    {
      $sSQL = "SELECT sName FROM T_Member WHERE MemberID = " . $this->m_aData[self::PROPERTY_ID];
      $this->RunSQL( $sSQL );

      $recMember = $this->fetch();
      if (!$recMember)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return NULL;
      }
      $this->m_aData[self::PROPERTY_MEMBER_NAME] = $recMember["sName"];
    }
    
    $sSQL = " SELECT PL.PickupLocationKeyID, MPL.PickupLocationKeyID as MPLID, " . 
            $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
            " , IfNUll(MPL.bRemoved,0) bRemoved, IfNull(MPL.bBlocked, 0) bBlocked " .
            " FROM T_PickupLocation PL LEFT JOIN T_MemberPickupLocation MPL ON PL.PickupLocationKeyID = MPL.PickupLocationKeyID " .
            " AND MPL.MemberID =  " . $this->m_aData[self::PROPERTY_ID] . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS);
    
    if (!$this->HasPermission(self::PERMISSION_EDIT))
    {
      if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE)
        $sSQL .= " WHERE PL.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";    
    }
    
    $sSQL .= " ORDER BY PL_S.sString;";
    
    $this->RunSQL( $sSQL );

    return $this->fetch();
  }
  
  public function BlockFromFacet($PickupLocationID, $bBlock = 1, $bInsert = TRUE)
  {
    global $g_oMemberSession;
    
    if (!$this->HasPermission(self::PERMISSION_COORD))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE)
    {
      if (!$g_oMemberSession->UserInGroup( $this->GetPickupLocationGroup( $PickupLocationID ) ) )
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
        return FALSE;
      }
    }
    
    if ($bInsert)
    {
      $sSQL = " INSERT INTO T_MemberPickupLocation (MemberID, PickupLocationKeyID, bBlocked) " .
              " VALUES ( :Member, :PickupLocation, :Blocked );";
    }
    else
    {
      $sSQL = " UPDATE T_MemberPickupLocation SET bBlocked = :Blocked " .
              " WHERE MemberID = :Member AND PickupLocationKeyID = :PickupLocation;";
    }
    
    $this->RunSQLWithParams($sSQL, array(
      'Member' => $this->m_aData[self::PROPERTY_ID],
      'PickupLocation' => $PickupLocationID,
      'Blocked' => $bBlock));
    
    return TRUE;
  }
  
  public function RemoveFromFacet($PickupLocationID, $bRemove = 1, $bInsert = TRUE)
  {
    global $g_oMemberSession;
    
    if ($g_oMemberSession->MemberID != $this->m_aData[self::PROPERTY_ID] && !$this->HasPermission(self::PERMISSION_EDIT))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }    
    
    if ($bInsert)
    {
      $sSQL = " INSERT INTO T_MemberPickupLocation (MemberID, PickupLocationKeyID, bRemoved) " .
              " VALUES ( :Member, :PickupLocation, :Removed );";
    }
    else
    {
      $sSQL = " UPDATE T_MemberPickupLocation SET bRemoved = :Removed " .
              " WHERE MemberID = :Member AND PickupLocationKeyID = :PickupLocation;";
    }
    
    $this->RunSQLWithParams($sSQL, array(
      'Member' => $this->m_aData[self::PROPERTY_ID],
      'PickupLocation' => $PickupLocationID,
      'Removed' => $bRemove));
    
    return TRUE;
  }
  
  public function ApplyFilter($sSelectedPLIDs)
  {
    global $g_aMemberPickupLocations;
    //have pl ids as keys
    $arrNewValues = array_flip(explode(';', $sSelectedPLIDs));
    
    $bHasChanges = FALSE;
    
    foreach($g_aMemberPickupLocations as $PLID => $PL)
    {
      if ($PL['bBlocked'] || $PL['bDisabled'])     
        continue;
      
      if ($PL['bRemoved']) //original is removed
      {
        if (isset($arrNewValues[$PLID])) //new is added
        {
          $this->RemoveFromFacet($PLID, 0, ($PL['MPLID'] == NULL));
          $bHasChanges = TRUE;
        }
      }
      else //original is added
      {
        if (!isset($arrNewValues[$PLID])) //new is removed
        {
          $this->RemoveFromFacet($PLID, 1, ($PL['MPLID'] == NULL));
          $bHasChanges = TRUE;
        }
      }
    }
    
    return $bHasChanges;
  }
  
  protected function GetPickupLocationGroup($PickupLocationID)
  {
    $sSQL = " SELECT CoordinatingGroupID FROM T_PickupLocation WHERE PickupLocationKeyID = :PLID;";
    
    $this->RunSQLWithParams($sSQL, array(
      'PLID' => $PickupLocationID));
    
    $rec = $this->fetch();
    if ($rec != NULL && $rec['CoordinatingGroupID'] != NULL)
      return $rec['CoordinatingGroupID'];
    return 0;
  }
}

?>
