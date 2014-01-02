<?php


class MemberProducers extends SQLBase implements IMemberFacet {
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
    $bCoord = $this->HasPermission(self::PERMISSION_EDIT) || $this->AddPermissionBridge(self::PERMISSION_EDIT, 
        Consts::PERMISSION_AREA_MEMBER_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->HasPermission(self::PERMISSION_COORD) || $this->AddPermissionBridge(self::PERMISSION_COORD, 
        Consts::PERMISSION_AREA_MEMBER_PRODUCERS, Consts::PERMISSION_TYPE_COORD, 
       Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    return ($bView || $bCoord);
  }
  
  public function GetTableForFacet()
  {
    global $g_oMemberSession;
       
    $this->m_aData[self::PROPERTY_ID] = $g_oMemberSession->MemberID;

    $sSQL = " SELECT P.ProducerKeyID, MPR.ProducerKeyID as MPRID, " . 
            $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            " , IfNUll(MPR.bRemoved,0) bRemoved, P.bDisabled, IfNull(MPR.bBlocked, 0) bBlocked " .
            " FROM T_Producer P LEFT JOIN T_MemberProducer MPR ON P.ProducerKeyID = MPR.ProducerKeyID " .
            " AND MPR.MemberID =  " . $this->m_aData[self::PROPERTY_ID] . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
            " ORDER BY bBlocked, bRemoved, P_S.sString;";
    
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
    
    $sSQL = " SELECT P.ProducerKeyID, MPR.ProducerKeyID as MPRID, " . 
            $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
            " , IfNUll(MPR.bRemoved,0) bRemoved, IfNull(MPR.bBlocked, 0) bBlocked " .
            " FROM T_Producer P LEFT JOIN T_MemberProducer MPR ON P.ProducerKeyID = MPR.ProducerKeyID " .
            " AND MPR.MemberID =  " . $this->m_aData[self::PROPERTY_ID] . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS);
    
    if (!$this->HasPermission(self::PERMISSION_EDIT))
    {
      if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE)
        $sSQL .= " WHERE P.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";    
    }
    
    $sSQL .= " ORDER BY P_S.sString;";
    
    $this->RunSQL( $sSQL );

    return $this->fetch();
  }
  
  public function BlockFromFacet($ProducerID, $bBlock = 1, $bInsert = TRUE)
  {
    global $g_oMemberSession;
    
    if (!$this->HasPermission(self::PERMISSION_COORD))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE)
    {
      if (!$g_oMemberSession->UserInGroup( $this->GetProducerGroup( $ProducerID ) ) )
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
        return FALSE;
      }
    }
    
    if ($bInsert)
    {
      $sSQL = " INSERT INTO T_MemberProducer (MemberID, ProducerKeyID, bBlocked) " .
              " VALUES ( :Member, :Producer, :Blocked );";
    }
    else
    {
      $sSQL = " UPDATE T_MemberProducer SET bBlocked = :Blocked " .
              " WHERE MemberID = :Member AND ProducerKeyID = :Producer;";
    }
    
    $this->RunSQLWithParams($sSQL, array(
      'Member' => $this->m_aData[self::PROPERTY_ID],
      'Producer' => $ProducerID,
      'Blocked' => $bBlock));
    
    return TRUE;
  }
  
  public function RemoveFromFacet($ProducerID, $bRemove = 1, $bInsert = TRUE)
  {
    global $g_oMemberSession;
    
    if ($g_oMemberSession->MemberID != $this->m_aData[self::PROPERTY_ID] && !$this->HasPermission(self::PERMISSION_EDIT))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }    
    
    if ($bInsert)
    {
      $sSQL = " INSERT INTO T_MemberProducer (MemberID, ProducerKeyID, bRemoved) " .
              " VALUES ( :Member, :Producer, :Removed );";
    }
    else
    {
      $sSQL = " UPDATE T_MemberProducer SET bRemoved = :Removed " .
              " WHERE MemberID = :Member AND ProducerKeyID = :Producer;";
    }
    
    $this->RunSQLWithParams($sSQL, array(
      'Member' => $this->m_aData[self::PROPERTY_ID],
      'Producer' => $ProducerID,
      'Removed' => $bRemove));
    
    return TRUE;
  }
  
  public function ApplyFilter($sSelectedPIDs)
  {
    global $g_aMemberProducers;
    //have pl ids as keys
    $arrNewValues = array_flip(explode(';', $sSelectedPIDs));
    
    $bHasChanges = FALSE;
    
    foreach($g_aMemberProducers as $PID => $P)
    {
      if ($P['bBlocked'] || $P['bDisabled'])     
        continue;
      
      if ($P['bRemoved']) //original is removed
      {
        if (isset($arrNewValues[$PID])) //new is added
        {
          $this->RemoveFromFacet($PID, 0, ($P['MPRID'] == NULL));
          $bHasChanges = TRUE;
        }
      }
      else //original is added
      {
        if (!isset($arrNewValues[$PID])) //new is removed
        {
          $this->RemoveFromFacet($PID, 1, ($P['MPRID'] == NULL));
          $bHasChanges = TRUE;
        }
      }
    }
    
    return $bHasChanges;
  }
  
  protected function GetProducerGroup($ProducerID)
  {
    $sSQL = " SELECT CoordinatingGroupID FROM T_Producer WHERE ProducerKeyID = :PID;";
    
    $this->RunSQLWithParams($sSQL, array(
      'PID' => $ProducerID));
    
    $rec = $this->fetch();
    if ($rec != NULL && $rec['CoordinatingGroupID'] != NULL)
      return $rec['CoordinatingGroupID'];
    return 0;
  }
}

?>
