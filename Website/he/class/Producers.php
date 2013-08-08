<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faclitates coord/producers.php - system-wide producers grid
class Producers extends SQLBase
{
    //returns the table's first row. Other rows are retreived by calling base::fetch()
    public function GetTable()
    {
        global $g_oMemberSession;
                
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
        $bEdit = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
        
        $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
        
        if (!$bEdit && !$bView)
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return NULL;
        }
        
        $this->AddPermissionBridge(self::PERMISSION_ADD, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_ADD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
        
        //check for coord setting permissions
        $this->AddPermissionBridge(self::PERMISSION_COORD_SET, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_COORD_SET, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

        $sSQL =          " SELECT P.ProducerKeyID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer');

        $sSQL .=  " , P.bDisabled, P.CoordinatingGroupID FROM T_Producer P " . $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS);

        if ( ($this->GetPermissionScope(self::PERMISSION_COORD) != Consts::PERMISSION_SCOPE_COOP_CODE) &&
             ($this->GetPermissionScope(self::PERMISSION_VIEW) != Consts::PERMISSION_SCOPE_COOP_CODE) )
           $sSQL .=     " WHERE P.CoordinatingGroupID IN (" . implode(",", $g_oMemberSession->Groups) . ") ";

        $sSQL .=         " ORDER BY P.bDisabled, P_S.sString; ";
        
        $this->RunSQL( $sSQL );

        return $this->fetch();
    }
    
    //for coordinator's coop order producer (coord/coproducer.php) - when creating a new record
    public function GetListForCoopOrder($nCurrentProducerID, $nCoopOrderID)
    {
      global $g_oMemberSession;
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
      
      if ($nCoopOrderID <= 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return NULL;
      }

      if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDER_PRODUCERS, 
            Consts::PERMISSION_TYPE_MODIFY, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return NULL;
      }
      
      if ($nCurrentProducerID === NULL)
        $nCurrentProducerID = 0;

      $sSQL =   " SELECT P.ProducerKeyID, " . 
               $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PRODUCERS, 'sProducer') .
        " FROM T_Producer P LEFT JOIN T_CoopOrderProducer COP ON COP.ProducerKeyID = P.ProducerKeyID AND COP.CoopOrderKeyID = " 
        .  $nCoopOrderID . " " .
        $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PRODUCERS) .
        " WHERE ( (COP.ProducerKeyID IS NULL AND P.bDisabled = 0) OR (P.ProducerKeyID = " . $nCurrentProducerID . " ) )";
     
      if ( $this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE )
          $sSQL .=  " AND P.CoordinatingGroupID IN ( 0, " . implode(",", $g_oMemberSession->Groups) . ") ";

      $sSQL .= " ORDER BY P_S.sString; ";

      $this->RunSQL( $sSQL );

      return $this->fetchAllKeyPair();
  }
}
?>
