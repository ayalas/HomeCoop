<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//a cooperative order from producers to pickup locations
class CoopOrder extends SQLBase {
  
  //values of the status field
  const STATUS_DRAFT = 0;
  const STATUS_ACTIVE = 1;
  const STATUS_CLOSED = 2;
  const STATUS_CANCELLED = 3;
  const STATUS_LOCKED = 4;
  const MAX_STATUS = 4;
  
  //id of permission bridge to view/edit summary fields
  const PERMISSION_SUMS = 11;
  
  //relate status codes to descriptions
  protected static $m_aStatusNames = array(
        self::STATUS_DRAFT => '<!$COOP_ORDER_STATUS_DRAFT$!>',
        self::STATUS_ACTIVE => '<!$COOP_ORDER_STATUS_ACTIVE$!>',
        self::STATUS_LOCKED => '<!$COOP_ORDER_STATUS_LOCKED$!>',
        self::STATUS_CLOSED => '<!$COOP_ORDER_STATUS_CLOSED$!>',
        self::STATUS_CANCELLED => '<!$COOP_ORDER_STATUS_CANCELLED$!>'
      );

  //for copy order screen
  const PROPERTY_SOURCE_COOP_ORDER_ID = "SourceCoopOrderID";
  const PROPERTY_NAME = "Name";
  const PROPERTY_STATUS = "Status";
  const PROPERTY_NAMES = "Names";
  const PROPERTY_START = "Start";
  const PROPERTY_END = "End";
  const PROPERTY_DELIVERY = "Delivery";
  const PROPERTY_MAX_BURDEN = "MaxBurden";
  const PROPERTY_MAX_COOP_TOTAL = "MaxCoopTotal";
  const PROPERTY_COOP_TOTAL = "CoopTotal";
  const PROPERTY_PRODUCER_TOTAL = "ProducerTotal";
  const PROPERTY_TOTAL_BURDEN = "TotalBurden";
  const PROPERTY_TOTAL_DELIVERY = "TotalDelivery";
  const PROPERTY_COOP_FEE = "CoopFee";
  const PROPERTY_SMALL_ORDER = "SmallOrder";
  const PROPERTY_SMALL_ORDER_COOP_FEE = "SmallOrderCoopFee";
  const PROPERTY_COOP_FEE_PERCENT = "CoopFeePercent";
  const PROPERTY_MODIFIER_ID = "ModifiedByMemberID";
  const PROPERTY_MODIFIER_NAME = "ModifiedByMemberName";
  const PROPERTY_ORIGINAL_STATUS = "OriginalStatus";
  const PROPERTY_HAS_JOINED_PRODUCTS = "HasJoinedProducts";
  const PROPERTY_PRICES_FROM_PRODUCTS = "PricesFromProducts";
  
  const PROPERTY_MAX_STORAGE_BURDEN = "MaxStorageBurden";
  const PROPERTY_STOARGE_BURDEN = "StorageBurden";
  
  protected $m_bCopyMode = FALSE;

  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_ID => 0,
                            self::PROPERTY_NAMES => NULL,
                            self::PROPERTY_START => NULL,
                            self::PROPERTY_END => NULL,
                            self::PROPERTY_DELIVERY => NULL,
                            self::PROPERTY_MAX_BURDEN => NULL,
                            self::PROPERTY_MAX_COOP_TOTAL => NULL,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_PRODUCER_TOTAL => 0,
                            self::PROPERTY_TOTAL_BURDEN => 0,
                            self::PROPERTY_COOP_FEE => NULL,
                            self::PROPERTY_SMALL_ORDER => NULL,
                            self::PROPERTY_SMALL_ORDER_COOP_FEE => NULL,
                            self::PROPERTY_COOP_FEE_PERCENT => NULL,
                            self::PROPERTY_TOTAL_DELIVERY => 0,
                            self::PROPERTY_MODIFIER_ID => 0,
                            self::PROPERTY_MODIFIER_NAME => NULL,
                            self::PROPERTY_STATUS => self::STATUS_DRAFT,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_SOURCE_COOP_ORDER_ID => 0,
                            self::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_PRICES_FROM_PRODUCTS => FALSE,
                            self::PROPERTY_MAX_STORAGE_BURDEN => NULL,
                            self::PROPERTY_STOARGE_BURDEN => NULL,
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
 
  public function __get( $name ) {
    global $g_sLangDir;
    switch ($name)
    {
      case self::PROPERTY_ORIGINAL_STATUS:
        return $this->m_aOriginalData[self::PROPERTY_STATUS];
      case self::PROPERTY_NAME:
        if ($g_sLangDir == '')
          return $this->m_aData[self::PROPERTY_NAMES][0];
        else
          return $this->GetLangPropertyVal(self::PROPERTY_NAMES,$g_sLangDir);
      default:
        return parent::__get($name);
    }
  }
  
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_TOTAL_DELIVERY:
      case self::PROPERTY_COOP_TOTAL:
      case self::PROPERTY_PRODUCER_TOTAL:
      case self::PROPERTY_TOTAL_BURDEN:
      case self::PROPERTY_MODIFIER_ID:
      case self::PROPERTY_MODIFIER_NAME:
      case self::PROPERTY_COORDINATING_GROUP_ID:
        $trace = debug_backtrace();
        trigger_error(
          'unsupported property via __set(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line'],
          E_USER_NOTICE);
      default:
        parent::__set( $name, $value );
    }
  }
  
  //basic permissions for the coop order
  public function CheckAccess()
  {
     $bModify = $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
     
     return ($bModify || $bView);
  }
  
  
  public function CheckEditPermission()
  {
     return $this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
  
  //check permissions for specific coop order
  protected function CheckPermissionAfterGroupAdd()
  {    
    $bModify = $this->AddPermissionBridgeGroupID(self::PERMISSION_EDIT, FALSE);
    $bView  = $this->AddPermissionBridgeGroupID(self::PERMISSION_VIEW, FALSE);
        
    return ($bModify || $bView);
  }
  
  public function CanCopy()
  {
    return $this->AddPermissionBridge(self::PERMISSION_COPY, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_COPY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
  }
  
  public function LoadRecord($nID)
  {
    global $g_oMemberSession;
    global $g_oTimeZone;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //general permission check
    if ( !$this->CheckAccess() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_ID] = $nID;
    
    $sSQL =   " SELECT CO.CoopOrderKeyID, CO.dStart, CO.dEnd, CO.dDelivery, CO.mCoopFee, CO.mSmallOrder, " . 
              " CO.bHasJoinedProducts, CO.fMaxStorageBurden, CO.fStorageBurden, " .
              " CO.mSmallOrderCoopFee, CO.fCoopFee, CO.ModifiedByMemberID, " .
              " CO.nStatus, CO.CoordinatingGroupID, CO.mMaxCoopTotal,  CO.fMaxBurden, " .
              " IfNull(CO.fBurden,0) fBurden, CO.mCoopTotal, CO.mProducerTotal, M.sName as ModifierName, mTotalDelivery " .
              " FROM T_CoopOrder CO  INNER JOIN T_Member M ON M.MemberID = CO.ModifiedByMemberID  " . 
              " WHERE CO.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

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

    //coordinating group permission check
    if ( !$this->CheckPermissionAfterGroupAdd() )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
      return FALSE;
    }
    
    //check sums permissions
    $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_SUMS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
    
    $this->m_aData[self::PROPERTY_START] = new DateTime($rec["dStart"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_END] = new DateTime($rec["dEnd"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_DELIVERY] = new DateTime($rec["dDelivery"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_COOP_FEE] = $rec["mCoopFee"];
    $this->m_aData[self::PROPERTY_SMALL_ORDER] = $rec["mSmallOrder"];
    $this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE] = $rec["mSmallOrderCoopFee"];
    $this->m_aData[self::PROPERTY_COOP_FEE_PERCENT] = $rec["fCoopFee"];
    $this->m_aData[self::PROPERTY_MODIFIER_ID] = $rec["ModifiedByMemberID"];
    $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $rec["ModifierName"];
    $this->m_aData[self::PROPERTY_STATUS] = $rec["nStatus"];
    $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] = $rec["mMaxCoopTotal"];
    $this->m_aData[self::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = Rounding::Round($rec["fBurden"], ROUND_SETTING_BURDEN);
    $this->m_aData[self::PROPERTY_COOP_TOTAL] = $rec["mCoopTotal"];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $rec["mProducerTotal"];
    $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = $rec["mTotalDelivery"];
    $this->m_aData[self::PROPERTY_HAS_JOINED_PRODUCTS] = $rec["bHasJoinedProducts"];
    $this->m_aData[self::PROPERTY_MAX_STORAGE_BURDEN] = $rec["fMaxStorageBurden"];
    $this->m_aData[self::PROPERTY_STOARGE_BURDEN] = $rec["fStorageBurden"];
    $this->m_aData[self::PROPERTY_NAMES] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_ID]);

    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  //permissions to view/edit summary fields
  public function CheckSumsPermission()
  {
   //if already checked in this run
   if ($this->HasPermission(self::PERMISSION_SUMS))
      return TRUE;
    
   if ($this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID] > 0)
      $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];
   
   return $this->AddPermissionBridge(self::PERMISSION_SUMS, Consts::PERMISSION_AREA_COOP_ORDER_SUMS, Consts::PERMISSION_TYPE_VIEW, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE); 
  }
  
  //insert a new coop order
  public function Add()
    {
        global $g_oMemberSession;
        global $g_sLangDir;
        $bUserGroupUsed = FALSE;
        $bUseSourceGroup = ($this->m_bCopyMode && $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] > 0);
        
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

        //general permission check
        if ( !$this->CheckEditPermission() )
        {
            $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
            return FALSE;
        }
        
        if (!$this->Validate())
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
          return FALSE;
        }
        
        try
        {
          $this->m_bUseClassConnection = TRUE; //counting on last inserted id+used in Copy()
          
          $this->BeginTransaction();
        
          //create new string key for the new record
          $nKeyID = $this->NewKey();

          //insert names     
          $this->InsertStrings($this->m_aData[self::PROPERTY_NAMES], $nKeyID);

          //insert the record - some of these fields are filled when copying an order only
          $sSQL =  " INSERT INTO T_CoopOrder( CoopOrderKeyID, dStart, dEnd, dDelivery, ModifiedByMemberID, nStatus " . 
                  $this->ConcatColIfNotNull(self::PROPERTY_COOP_FEE, "mCoopFee") .
                  $this->ConcatColIfNotNull(self::PROPERTY_SMALL_ORDER, "mSmallOrder") .
                  $this->ConcatColIfNotNull(self::PROPERTY_SMALL_ORDER_COOP_FEE, "mSmallOrderCoopFee") .
                  $this->ConcatColIfNotNull(self::PROPERTY_COOP_FEE_PERCENT, "fCoopFee") .
                  $this->ConcatColIfNotNull(self::PROPERTY_MAX_COOP_TOTAL, "mMaxCoopTotal") .
                  $this->ConcatColIfNotNull(self::PROPERTY_MAX_BURDEN, "fMaxBurden") .
		  $this->ConcatColIfNotNull(self::PROPERTY_MAX_STORAGE_BURDEN, "fMaxStorageBurden");


          if ( $this->GetPermissionScope(self::PERMISSION_EDIT) == Consts::PERMISSION_SCOPE_GROUP_CODE || $bUseSourceGroup )
              $sSQL .= ", CoordinatingGroupID ";

          $sSQL .= ") VALUES ( " . $nKeyID .   ", ?, ?, ?, " . $g_oMemberSession->MemberID . ", " . $this->m_aData[self::PROPERTY_STATUS] . 
                  $this->ConcatValIfNotNull(self::PROPERTY_COOP_FEE) . 
                  $this->ConcatValIfNotNull(self::PROPERTY_SMALL_ORDER) . 
                  $this->ConcatValIfNotNull(self::PROPERTY_SMALL_ORDER_COOP_FEE) . 
                  $this->ConcatValIfNotNull(self::PROPERTY_COOP_FEE_PERCENT) . 
                  $this->ConcatValIfNotNull(self::PROPERTY_MAX_COOP_TOTAL) . 
                  $this->ConcatValIfNotNull(self::PROPERTY_MAX_BURDEN) .
                  $this->ConcatValIfNotNull(self::PROPERTY_MAX_STORAGE_BURDEN);

          if ($bUseSourceGroup)
          {
            $sSQL .= ", " . $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID];
          }
          else if ( $this->GetPermissionScope(self::PERMISSION_EDIT) == Consts::PERMISSION_SCOPE_GROUP_CODE ) 
          {
            $sSQL .= ", " . $g_oMemberSession->CoordinatingGroupID;
            $bUserGroupUsed = TRUE;
          }

          $sSQL .= " );";

          $this->RunSQLWithParams($sSQL, array( $this->m_aData[self::PROPERTY_START]->format(DATABASE_DATE_FORMAT), 
              $this->m_aData[self::PROPERTY_END]->format(DATABASE_DATE_FORMAT),
              $this->m_aData[self::PROPERTY_DELIVERY]->format(DATABASE_DATE_FORMAT) ));
         
          $this->CommitTransaction();
        }
        catch(Exception $e)
        {
          $this->RollbackTransaction();
          $this->CloseConnection();
          $this->m_bUseClassConnection = FALSE;
          throw $e;
        }
        $this->CloseConnection();
        $this->m_bUseClassConnection = FALSE;
        
        $this->m_aData[self::PROPERTY_ID] = $nKeyID;
        $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $g_oMemberSession->Name;
        $this->m_aData[self::PROPERTY_MODIFIER_ID] = $g_oMemberSession->MemberID;
        
        if ( $bUserGroupUsed ) 
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $g_oMemberSession->CoordinatingGroupID;
        
        $this->m_aOriginalData = $this->m_aData;

        return TRUE;
    }
  
  
  public function Edit()
  {
    global $g_oMemberSession;
    global $g_sLangDir;
    
    $bRecalc = FALSE;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //permission check
    if ( !$this->AddPermissionBridge(self::PERMISSION_EDIT, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE) )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    try
    {
        
      //allow updating only active, locked and draft orders
      if ($this->m_aOriginalData[self::PROPERTY_STATUS] == self::STATUS_ACTIVE 
         ||  $this->m_aOriginalData[self::PROPERTY_STATUS] == self::STATUS_DRAFT 
         ||  $this->m_aOriginalData[self::PROPERTY_STATUS] == self::STATUS_LOCKED )
      {
        if (!$this->Validate())
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
          return FALSE;
        }
        
        $this->BeginTransaction();

        $sSQL =   " UPDATE T_CoopOrder " .
                  " SET dStart =  ? , dEnd = ?, dDelivery = ?, ModifiedByMemberID = ? , mCoopFee = ?, mSmallOrder = ?, mSmallOrderCoopFee = ?, " . 
                  " fCoopFee =  ? , mMaxCoopTotal = ?, fMaxBurden = ?, nStatus = ?  " .
                  " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

        $this->RunSQLWithParams( $sSQL, array(
                  $this->m_aData[self::PROPERTY_START]->format(DATABASE_DATE_FORMAT),
                  $this->m_aData[self::PROPERTY_END]->format(DATABASE_DATE_FORMAT),
                  $this->m_aData[self::PROPERTY_DELIVERY]->format(DATABASE_DATE_FORMAT),
                  $g_oMemberSession->MemberID,
                  $this->m_aData[self::PROPERTY_COOP_FEE],
                  $this->m_aData[self::PROPERTY_SMALL_ORDER],
                  $this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE],
                  $this->m_aData[self::PROPERTY_COOP_FEE_PERCENT],
                  $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL],
                  $this->m_aData[self::PROPERTY_MAX_BURDEN],
                  $this->m_aData[self::PROPERTY_STATUS]
                )
            );

        $this->UpdateStrings(self::PROPERTY_NAMES, $this->m_aData[self::PROPERTY_ID]);

        if ($this->m_aData[self::PROPERTY_COOP_FEE] != $this->m_aOriginalData[self::PROPERTY_COOP_FEE] ||
            $this->m_aData[self::PROPERTY_SMALL_ORDER] != $this->m_aOriginalData[self::PROPERTY_SMALL_ORDER] ||
            $this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE] != $this->m_aOriginalData[self::PROPERTY_SMALL_ORDER_COOP_FEE] ||
            $this->m_aData[self::PROPERTY_COOP_FEE_PERCENT] != $this->m_aOriginalData[self::PROPERTY_COOP_FEE_PERCENT])
        {
          $this->CalculateCoopFee();
          $bRecalc = TRUE;
        }

        $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $g_oMemberSession->Name;
        $this->m_aData[self::PROPERTY_MODIFIER_ID] = $g_oMemberSession->MemberID;
      }
      else if ($this->m_aData[self::PROPERTY_STATUS] != $this->m_aOriginalData[self::PROPERTY_STATUS])
      {
        //copy original values
        $this->m_aData[self::PROPERTY_START] = $this->m_aOriginalData[self::PROPERTY_START];
        $this->m_aData[self::PROPERTY_END] = $this->m_aOriginalData[self::PROPERTY_END];
        $this->m_aData[self::PROPERTY_DELIVERY] = $this->m_aOriginalData[self::PROPERTY_DELIVERY];
        $this->m_aData[self::PROPERTY_COOP_FEE] = $this->m_aOriginalData[self::PROPERTY_COOP_FEE];
        $this->m_aData[self::PROPERTY_SMALL_ORDER] = $this->m_aOriginalData[self::PROPERTY_SMALL_ORDER];
        $this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE] = $this->m_aOriginalData[self::PROPERTY_SMALL_ORDER_COOP_FEE];
        $this->m_aData[self::PROPERTY_COOP_FEE_PERCENT] = $this->m_aOriginalData[self::PROPERTY_COOP_FEE_PERCENT];
        $this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_MAX_COOP_TOTAL];
        $this->m_aData[self::PROPERTY_MAX_BURDEN] = $this->m_aOriginalData[self::PROPERTY_MAX_BURDEN];
        $this->m_aData[self::PROPERTY_NAMES] = $this->m_aOriginalData[self::PROPERTY_NAMES];
        $this->m_aData[self::PROPERTY_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
        $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL];
        $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = $this->m_aOriginalData[self::PROPERTY_TOTAL_DELIVERY];
        $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $this->m_aOriginalData[self::PROPERTY_MODIFIER_NAME];
        $this->m_aData[self::PROPERTY_MODIFIER_ID] = $this->m_aOriginalData[self::PROPERTY_MODIFIER_ID];

        if (!$this->ValidateStatus())
        {
          $g_oError->AddError( sprintf('<!$INVALID_STATUS$!>', '<!$FIELD_COOP_ORDER_STATUS$!>'));
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
          return FALSE;
        }
        
        $this->BeginTransaction();

        $sSQL =   " UPDATE T_CoopOrder " .
                  " SET ModifiedByMemberID = " . $g_oMemberSession->MemberID . " , nStatus = " . $this->m_aData[self::PROPERTY_STATUS] . 
                  " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

        $this->RunSQL($sSQL);

        $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $g_oMemberSession->Name;
        $this->m_aData[self::PROPERTY_MODIFIER_ID] = $g_oMemberSession->MemberID;
      }


      //Recalculate totals for coop order, pickup locations and producers
      if ($bRecalc)
      {
        //calculate elements affected by the change of fee - coop order sums and pickup locations sums
        $oCalc = new CoopOrderCalculate($this->m_aData[self::PROPERTY_ID]);
        $oCalc->CalculatePickupLocs(FALSE);
        $oCalc->CalculateCoopOrder();
        unset($oCalc);
      }
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  public function HasDeletePermission()
  {
    return $this->AddPermissionBridge(self::PERMISSION_DELETE, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_DELETE, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
  }

  public function Delete()
  {
    global $g_oError;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //general permission check and special delete permission
    if ( !$this->CheckEditPermission() || !$this->HasDeletePermission() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }

    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }

    if ( $this->m_aOriginalData[self::PROPERTY_STATUS] == self::STATUS_ACTIVE )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      $g_oError->AddError('<!$CANNOT_DELETE_ACTIVE_ORDER$!>');
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();

      $sSQL =   " DELETE FROM T_CoopOrder " .
                 " WHERE CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_ID] . ';';

      $this->RunSQL($sSQL); //deletes all child records. Notifications remain, with CoopOrderKeyID set to NULL

      $this->DeleteKey($this->m_aData[self::PROPERTY_ID]); //deletes all associated strings
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
        
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
    
    return TRUE;
  }
  
  public function Validate()
  {
    global $g_oError;
    global $g_sLangDir;
    
    $bValid = TRUE;
    
    if (!$this->ValidateRequiredNames(self::PROPERTY_NAMES, '<!$FIELD_COOP_ORDER_NAME$!>'))
      $bValid = FALSE;
    
    if (!$this->ValidateStatus())
    {
      $g_oError->AddError( sprintf('<!$INVALID_SELECT_CHANGE$!>', '<!$FIELD_COOP_ORDER_STATUS$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_START] == NULL || get_class($this->m_aData[self::PROPERTY_START]) != "DateTime")
    {
      $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_SET_TO_VALID_DATE$!>', '<!$FIELD_COOP_ORDER_START$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_END] == NULL || get_class($this->m_aData[self::PROPERTY_END]) != "DateTime")
    {
      $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_SET_TO_VALID_DATE$!>', '<!$FIELD_COOP_ORDER_END$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_DELIVERY] == NULL || get_class($this->m_aData[self::PROPERTY_DELIVERY]) != "DateTime")
    {
      $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_SET_TO_VALID_DATE$!>', '<!$FIELD_COOP_ORDER_DELIVERY$!>'));
      $bValid = FALSE;
    }
    
    if ($bValid) //if dates are valid
    {
      //validate that start is equal or less than end 
      $oInterval = $this->m_aData[self::PROPERTY_START]->diff($this->m_aData[self::PROPERTY_END]);
      if (intval($oInterval->format('%R%a')) < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_LATER_OR_EQUAL_TO_FIELD2$!>', '<!$FIELD_COOP_ORDER_END$!>',
                '<!$FIELD_COOP_ORDER_START$!>'));
        $bValid = FALSE;
      }

      //validate that end is equal or less than delivery
      $oInterval = $this->m_aData[self::PROPERTY_END]->diff($this->m_aData[self::PROPERTY_DELIVERY]);
      if (intval($oInterval->format('%R%a')) < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_LATER_OR_EQUAL_TO_FIELD2$!>', '<!$FIELD_COOP_ORDER_DELIVERY$!>',
                '<!$FIELD_COOP_ORDER_END$!>'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_COOP_FEE] != NULL && !is_numeric($this->m_aData[self::PROPERTY_COOP_FEE]))
    {
      $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_COOP_FEE$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_SMALL_ORDER] != NULL)
    {
      if ( !is_numeric($this->m_aData[self::PROPERTY_SMALL_ORDER]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_SMALL_ORDER$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE] == NULL)
      {
        $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_SET_WHEN_FIELD2_IS_SET$!>',
                '<!$FIELD_COOP_ORDER_SMALL_ORDER_COOP_FEE$!>', '<!$FIELD_COOP_ORDER_SMALL_ORDER$!>'
                ));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE] != NULL)
    {
      if ( !is_numeric($this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_SMALL_ORDER_COOP_FEE$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_SMALL_ORDER] == NULL)
      {
        $g_oError->AddError( sprintf('<!$FIELD1_MUST_BE_SET_WHEN_FIELD2_IS_SET$!>', '<!$FIELD_COOP_ORDER_SMALL_ORDER$!>',
                '<!$FIELD_COOP_ORDER_SMALL_ORDER_COOP_FEE$!>'
                ));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_COOP_FEE_PERCENT] != NULL && !is_numeric($this->m_aData[self::PROPERTY_COOP_FEE_PERCENT]))
    {
      $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_COOP_FEE_PERCENT$!>'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_MAX_TOTAL_AMOUNT$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_COOP_TOTAL] < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_COOP_ORDER_MAX_TOTAL_AMOUNT$!>'));
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_MAX_BURDEN] != NULL)
    {
      if (!is_numeric($this->m_aData[self::PROPERTY_MAX_BURDEN]))
      {
        $g_oError->AddError( sprintf('<!$FIELD_MUST_BE_NUMERIC$!>', '<!$FIELD_COOP_ORDER_MAX_BURDEN$!>'));
        $bValid = FALSE;
      }
      else if ($this->m_aData[self::PROPERTY_MAX_BURDEN] < 0)
      {
        $g_oError->AddError( sprintf('<!$FIELD_NON_NEGATIVE$!>', '<!$FIELD_COOP_ORDER_MAX_BURDEN$!>'));
        $bValid = FALSE;
      }
    }
    
    return $bValid;
  }
  
  public function PreserveData()
  {
    $this->m_aData[self::PROPERTY_STOARGE_BURDEN]  = $this->m_aOriginalData[self::PROPERTY_STOARGE_BURDEN];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
    $this->m_aData[self::PROPERTY_MODIFIER_ID] = $this->m_aOriginalData[self::PROPERTY_MODIFIER_ID];
    $this->m_aData[self::PROPERTY_MODIFIER_NAME] = $this->m_aOriginalData[self::PROPERTY_MODIFIER_NAME];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
    $this->m_aData[self::PROPERTY_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL];
    $this->m_aData[self::PROPERTY_TOTAL_DELIVERY] = $this->m_aOriginalData[self::PROPERTY_TOTAL_DELIVERY];
  }
  //validate status change
  protected function ValidateStatus()
  {   
    $aValues = self::GetStatusesToChangeTo($this->m_aOriginalData[self::PROPERTY_STATUS]);
    return array_key_exists( $this->m_aData[self::PROPERTY_STATUS], $aValues );
  }
  
  //for coop order copy
  public function LoadSourceOrderInitalData()
  {
    global $g_oMemberSession;
    global $g_oTimeZone;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    //general permission check
    if ( !$this->CheckEditPermission() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $sSQL =   " SELECT CO.CoordinatingGroupID, CO.dStart, CO.dEnd, CO.dDelivery " .
              " FROM T_CoopOrder CO " . 
              " WHERE CO.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] . ';';

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

    //coordinating group permission check
    if ( !$this->AddPermissionBridgeGroupID(self::PERMISSION_EDIT, FALSE) )
      return FALSE;
    
    $this->m_aData[self::PROPERTY_START] = new DateTime($rec["dStart"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_END] = new DateTime($rec["dEnd"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_DELIVERY] = new DateTime($rec["dDelivery"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_NAMES] = $this->GetKeyStrings($this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID]);

    return TRUE;
  }
  
  public function Copy()
  {
    global $g_oMemberSession;
    //save inserted data before loading existing record
    $aNewData = $this->m_aData;
    
    //has permission to copy
    if (!$this->CanCopy())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $this->m_bCopyMode = TRUE;
    
    if (!$this->LoadRecord( $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] ) )
      return FALSE;
    
    //override data
    $this->m_aData[self::PROPERTY_ID] = 0;
    $this->m_aData[self::PROPERTY_STATUS] = $aNewData[self::PROPERTY_STATUS];
    $this->m_aData[self::PROPERTY_START] = $aNewData[self::PROPERTY_START];
    $this->m_aData[self::PROPERTY_END] = $aNewData[self::PROPERTY_END];
    $this->m_aData[self::PROPERTY_DELIVERY] = $aNewData[self::PROPERTY_DELIVERY];
    $this->m_aData[self::PROPERTY_NAMES] = $aNewData[self::PROPERTY_NAMES];
    
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    //now $this->m_aData[self::PROPERTY_ID] contains the new order id
    
    try
    {
      $this->m_bUseClassConnection = TRUE;
      
      $this->BeginTransaction();
      
      //Add can fall on validations
      if (!$this->Add( $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] ) )
      {
        $this->RollbackTransaction();
        return false;
      }

      //copy order pickup locations that are still active
      $sSQL =  " INSERT INTO T_CoopOrderPickupLocation( CoopOrderKeyID, PickupLocationKeyID, fMaxBurden, mMaxCoopTotal , fMaxStorageBurden ) " . 
               " SELECT " .  $this->m_aData[self::PROPERTY_ID] . 
               " , SRC.PickupLocationKeyID, SRC.fMaxBurden, SRC.mMaxCoopTotal, SRC.fMaxStorageBurden " .
               " FROM T_CoopOrderPickupLocation SRC ". 
               " INNER JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = SRC.PickupLocationKeyID " .
               " WHERE SRC.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] .
               " AND PL.bDisabled = 0;";

      $this->RunSQL($sSQL);
      
      //copy order storage areas that are still active
      $sSQL =  " INSERT INTO T_CoopOrderStorageArea (CoopOrderKeyID, StorageAreaKeyID, fMaxBurden) " .
               " SELECT " .  $this->m_aData[self::PROPERTY_ID] . " , COSA.StorageAreaKeyID, COSA.fMaxBurden " .
               " FROM T_CoopOrderStorageArea COSA INNER JOIN T_PickupLocationStorageArea PLSA " .
               " ON COSA.StorageAreaKeyID = PLSA.StorageAreaKeyID " .
               " INNER JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = PLSA.PickupLocationKeyID " .
               " WHERE COSA.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] .
               " AND PLSA.bDisabled = 0 AND PL.bDisabled = 0;";
      
      $this->RunSQL($sSQL);

      //copy order producers
      $sSQL =  " INSERT INTO T_CoopOrderProducer( CoopOrderKeyID, ProducerKeyID, mTotalDelivery, mMaxProducerOrder, " .
               " fDelivery, mDelivery, mMinDelivery, mMaxDelivery, fMaxBurden  ) " .
               " SELECT " .  $this->m_aData[self::PROPERTY_ID] . 
               " , SRC.ProducerKeyID, IfNull(IfNull(SRC.mDelivery, SRC.mMinDelivery),0), SRC.mMaxProducerOrder, " .
               " SRC.fDelivery, SRC.mDelivery, SRC.mMinDelivery, SRC.mMaxDelivery, SRC.fMaxBurden " .
               " FROM T_CoopOrderProducer SRC WHERE SRC.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID];

      $this->RunSQL($sSQL);

      //copy order products that are still active
      $sSQL =  " INSERT INTO T_CoopOrderProduct( CoopOrderKeyID, ProductKeyID, mProducerPrice, mCoopPrice, fMaxUserOrder, fBurden, fMaxCoopOrder) " .
               " SELECT " .  $this->m_aData[self::PROPERTY_ID] . " , SRC.ProductKeyID, ";
      
      if ($aNewData[self::PROPERTY_PRICES_FROM_PRODUCTS])
        $sSQL .= " PRD.mProducerPrice, PRD.mCoopPrice, ";
      else
        $sSQL .= " SRC.mProducerPrice, SRC.mCoopPrice, "; 
      
      $sSQL .= " SRC.fMaxUserOrder, SRC.fBurden, SRC.fMaxCoopOrder FROM T_CoopOrderProduct SRC ";
      
      $sSQL .= " INNER JOIN T_Product PRD ON PRD.ProductKeyID = SRC.ProductKeyID ";
      
      $sSQL .= " WHERE SRC.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] .
               " AND PRD.bDisabled = 0;";

      $this->RunSQL($sSQL);
      
      //copy order products storages that are still active
      $sSQL =  " INSERT INTO T_CoopOrderProductStorage (CoopOrderKeyID, ProductKeyID, PickupLocationKeyID, StorageAreaKeyID) " .
               " SELECT " .  $this->m_aData[self::PROPERTY_ID] . " , COPS.ProductKeyID,  COPS.PickupLocationKeyID, COPS.StorageAreaKeyID " .
               " FROM T_CoopOrderProductStorage COPS INNER JOIN T_CoopOrderStorageArea COSA " .
               " ON COSA.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_ID] .
               " AND COPS.StorageAreaKeyID = COSA.StorageAreaKeyID " .
               " INNER JOIN T_Product PRD ON PRD.ProductKeyID = COPS.ProductKeyID " .
               " WHERE COPS.CoopOrderKeyID = " . $this->m_aData[self::PROPERTY_SOURCE_COOP_ORDER_ID] .
               " AND PRD.bDisabled = 0;";
      
      $this->RunSQL($sSQL);
    
      $this->CommitTransaction();
    
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      $this->CloseConnection();
      $this->m_bUseClassConnection = FALSE;
      throw $e;
    }
    $this->CloseConnection();
    $this->m_bUseClassConnection = FALSE;
    
    return TRUE;
    
  }
  
  public function JumpDates()
  {
    $di = NULL;
    switch(COPY_ORDER_DEFAULT_DATE_JUMP)
    {
      case Consts::COPY_ORDER_JUMP_WEEK:
        $di = new DateInterval('P' . (COPY_ORDER_JUMP +0) . 'W');
        $this->m_aData[self::PROPERTY_END]->add($di);
        $this->m_aData[self::PROPERTY_DELIVERY]->add($di);
        break;
        
      case Consts::COPY_ORDER_JUMP_MONTH:
        $di = new DateInterval('P' . (COPY_ORDER_JUMP +0) . 'M');
        $this->AddDatePreserveDW(self::PROPERTY_END, $di);
        $this->AddDatePreserveDW(self::PROPERTY_DELIVERY, $di);        
        break;
      default:
        return;
    }
  }
  //calculate coop fee for existing orders
  //called when actual member orders are changed/placed
  protected function CalculateCoopFee()
  {
    $oOrder = NULL;
    //load all orders that have total > 0
    $sSQL = " SELECT O.OrderID, O.mCoopFee, O.mCoopTotal FROM T_Order O WHERE O.CoopOrderKeyID = " . 
        $this->m_aData[self::PROPERTY_ID] . ' AND O.mCoopTotal > 0;';

    $this->RunSQL( $sSQL );

    $recOrder = $this->fetch();
    //update coop fee for each order
    while($recOrder)
    {
      $oOrder = new Order;
      $oOrder->CoopTotal = $recOrder["mCoopTotal"];
      $oOrder->CoopFee = $this->m_aData[self::PROPERTY_COOP_FEE];
      $oOrder->SmallOrder = $this->m_aData[self::PROPERTY_SMALL_ORDER];
      $oOrder->SmallOrderCoopFee = $this->m_aData[self::PROPERTY_SMALL_ORDER_COOP_FEE];
      $oOrder->CoopFeePercent = $this->m_aData[self::PROPERTY_COOP_FEE_PERCENT];
      $oOrder->CalculateCoopFee();
      if ($oOrder->OrderCoopFee != $recOrder["mCoopFee"])
      {
        $sSQL = " UPDATE T_Order SET mCoopFee = ?, mCoopTotalIncFee = ? WHERE OrderID = " . $recOrder["OrderID"] . ";";
        $this->m_bUseSecondSqlPreparedStmt = TRUE;
        $this->RunSQLWithParams($sSQL, array($oOrder->OrderCoopFee, $oOrder->CoopTotalIncludingFee));
        $this->m_bUseSecondSqlPreparedStmt = FALSE;
      }
      
      $recOrder = $this->fetch();
    }
  }
  
  public static function GetAllStatusNames()
  {
    return self::$m_aStatusNames;
  }
  
  public static function GetStatusesToChangeTo($nStatus)
  {
    //copy the original array
    $aReturn = self::$m_aStatusNames;
    
    switch($nStatus)
    {
      case self::STATUS_DRAFT:
        unset($aReturn[self::STATUS_CLOSED]);
        break;
      case self::STATUS_CANCELLED:
        unset($aReturn[self::STATUS_ACTIVE]);
        break;
      case self::STATUS_ACTIVE:
      case self::STATUS_CLOSED:
      case self::STATUS_LOCKED;
        break;
      default:
        return NULL;
    }
    
    return $aReturn;
  }
  
  public static function StatusName($nStatus)
  {
    if ($nStatus < 0 || $nStatus > self::MAX_STATUS)
      return $nStatus;
    
    if ($nStatus == self::STATUS_DRAFT)
      return '<!$COOP_ORDER_STATUS_DRAFT_PUBLIC$!>';
    
    return self::$m_aStatusNames[$nStatus];
  }
}

?>
