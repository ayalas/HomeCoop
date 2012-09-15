<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faciliate member order and order items pages (order.php and orderitems.php)
class Order extends SQLBase {
  
  const MAX_LENGTH_MEMBER_COMMENTS = 250;
    
  const PROPERTY_COOP_ORDER_ID = "CoopOrderID";
  const PROPERTY_COOP_ORDER_NAME = "CoopOrderName";
  const PROPERTY_COOP_ORDER_CONTACTS = "CoopOrderContacts";
  const PROPERTY_MEMBER_ID = "MemberID";
  const PROPERTY_MEMBER_NAME = "MemberName";
  const PROPERTY_PICKUP_LOCATION_ID = "PickupLocationID";
  const PROPERTY_PICKUP_LOCATION_NAME = "PickupLocationName";
  const PROPERTY_PICKUP_LOCATION_ADDRESS = "PickupLocationAddress";
  const PROPERTY_PICKUP_LOCATION_COOP_TOTAL = "PickupLocationCoopTotal";
  const PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN = "PickupLocationBurden";
  const PROPERTY_PICKUP_LOCATION_MAX_TOTAL = "PickupLocationMaxCoopTotal";
  const PROPERTY_PICKUP_LOCATION_MAX_BURDEN = "PickupLocationMaxBurden";
  const PROPERTY_PICKUP_LOCATION_GROUP_ID = "PickupLocationGroupID";
  const PROPERTY_PICKUP_LOCATION_CONTACTS = "PickupLocationContacts";
  
  const PROPERTY_COOP_TOTAL = "CoopTotal";
  const PROPERTY_COOP_TOTAL_INC_FEE = "CoopTotalIncludingFee";
  const PROPERTY_PRODUCER_TOTAL = "ProducerTotal";
  const PROPERTY_TOTAL_BURDEN = "OrderBurden";
  const PROPERTY_COOP_FEE = "OrderCoopFee";
  const PROPERTY_MEMBER_COMMENTS = "MemberComments";  
  const PROPERTY_CREATE_DATE = "DateCreated";
  const PROPERTY_CREATE_MEMBER_ID = "CreatedByMemberID";
  const PROPERTY_CREATE_MEMBER_NAME = "CreatedByMemberName";
  const PROPERTY_MODIFY_DATE = "DateModified";
  const PROPERTY_MODIFY_MEMBER_ID = "ModifiedByMemberID";
  const PROPERTY_MODIFY_MEMBER_NAME = "ModifiedByMemberName";

  const PROPERTY_CAN_MODIFY = "CanModify";
  const PROPERTY_CAN_ENLARGE = "CanEnlarge";
  const PROPERTY_ITEMS_CHANGED_BY_COORD = "ItemsChangedByCoordinator";
  const PROPERTY_HAS_ITEMS_COMMENTS = "HasItemComments";
  const PROPERTY_PAGE_TITLE = "PageTitle";
  const PROPERTY_PAGE_TITLE_ADDITION = "PageTitleSuffix";
  const PROPERTY_COOP_ORDER_COOP_TOTAL = "CoopOrderCoopTotal";
  const PROPERTY_SUPRESS_MESSAGES = "SuppressMessages";
  
  const PROPERTY_COOP_ORDER_STATUS_OBJECT = "StatusObj";
  
  protected $m_bCanModify = FALSE;
  
  protected $m_bRunningTotalsValidation = FALSE;
  
  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_ID => 0,
                            self::PROPERTY_COOP_ORDER_ID => 0,
                            self::PROPERTY_COOP_ORDER_NAME => NULL,
                            self::PROPERTY_MEMBER_ID => 0,
                            self::PROPERTY_MEMBER_NAME => NULL,        
                            self::PROPERTY_PICKUP_LOCATION_ID => 0,
                            self::PROPERTY_PICKUP_LOCATION_NAME => 0,
                            self::PROPERTY_PICKUP_LOCATION_ADDRESS => NULL,
                            self::PROPERTY_PICKUP_LOCATION_GROUP_ID => 0,
                            PickupLocation::PROPERTY_PUBLISHED_STRINGS => NULL,
                            self::PROPERTY_COOP_TOTAL => 0,
                            self::PROPERTY_TOTAL_BURDEN => 0,
                            self::PROPERTY_PRODUCER_TOTAL => 0,
                            self::PROPERTY_COOP_FEE => 0,
                            self::PROPERTY_CREATE_DATE => NULL,
                            self::PROPERTY_CREATE_MEMBER_ID => 0,
                            self::PROPERTY_CREATE_MEMBER_NAME => NULL,
                            self::PROPERTY_MODIFY_DATE => NULL,
                            self::PROPERTY_MODIFY_MEMBER_ID => 0,
                            self::PROPERTY_MODIFY_MEMBER_NAME => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => 0,
                            self::PROPERTY_MEMBER_COMMENTS => NULL,
                            self::PROPERTY_PAGE_TITLE => '',
                            self::PROPERTY_PAGE_TITLE_ADDITION => '',
                            self::PROPERTY_ITEMS_CHANGED_BY_COORD => FALSE,
                            self::PROPERTY_HAS_ITEMS_COMMENTS => FALSE,
                            self::PROPERTY_SUPRESS_MESSAGES => FALSE,
                            self::PROPERTY_CAN_ENLARGE => FALSE,
                            self::PROPERTY_PICKUP_LOCATION_MAX_TOTAL => NULL,
                            self::PROPERTY_PICKUP_LOCATION_MAX_BURDEN => NULL,
                            self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL => 0,
                            self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN => 0,        
                            CoopOrder::PROPERTY_STATUS => CoopOrder::STATUS_DRAFT,
                            self::PROPERTY_COOP_ORDER_STATUS_OBJECT => NULL,
                            CoopOrder::PROPERTY_START => NULL,
                            CoopOrder::PROPERTY_END => NULL,
                            CoopOrder::PROPERTY_DELIVERY => NULL,
                            CoopOrder::PROPERTY_MAX_BURDEN => NULL,
                            CoopOrder::PROPERTY_MAX_COOP_TOTAL => NULL,
                            CoopOrder::PROPERTY_HAS_JOINED_PRODUCTS => FALSE,
                            self::PROPERTY_COOP_ORDER_COOP_TOTAL => 0,
                            CoopOrder::PROPERTY_TOTAL_BURDEN => 0,
                            CoopOrder::PROPERTY_COOP_FEE => NULL,
                            CoopOrder::PROPERTY_SMALL_ORDER => NULL,
                            CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE => NULL,
                            CoopOrder::PROPERTY_COOP_FEE_PERCENT => NULL,
                            self::PROPERTY_PICKUP_LOCATION_CONTACTS => NULL,
                            self::PROPERTY_COOP_ORDER_CONTACTS => NULL,
                            Member::PROPERTY_LOGIN_NAME => NULL,
                            Member::PROPERTY_EMAIL => NULL,
                            Member::PROPERTY_EMAIL2 => NULL,
                            Member::PROPERTY_EMAIL3 => NULL,
                            Member::PROPERTY_EMAIL4 => NULL,
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData;
  }
   
  public function __get( $name ) {
    switch ($name)
    {
      case self::PROPERTY_CAN_MODIFY:
        return $this->m_bCanModify;
      case self::PROPERTY_TOTAL_BURDEN:
        if ($this->m_aData[$name] == NULL)
          return 0;
        return $this->m_aData[$name];
      default:
        return parent::__get($name);
    }
  }

  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_CAN_MODIFY:
        if (!$value)
        {
          $this->m_bCanModify = $value;
          $this->m_aData[self::PROPERTY_CAN_ENLARGE] = $value; //if can't modify, can't add
        }
        else //cannot set to other value than FALSE - can also restrict more, not loosen permissions
        {
          $trace = debug_backtrace();
          throw new Exception(
            'Forbidden set operation for ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line']);
        }
        break;
      case self::PROPERTY_CAN_ENLARGE:
        if (!$value)
        {
          $this->m_aData[self::PROPERTY_CAN_ENLARGE] = $value;
        }
        else //cannot set to other value than FALSE - can also restrict more, not loosen permissions
        {
          $trace = debug_backtrace();
          throw new Exception(
            'Forbidden set operation for ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line']);
        }
        break;
      case self::PROPERTY_PAGE_TITLE:
      case self::PROPERTY_PAGE_TITLE_ADDITION:
      case self::PROPERTY_COOP_ORDER_NAME:
      case self::PROPERTY_PICKUP_LOCATION_NAME:
      case self::PROPERTY_PICKUP_LOCATION_ADDRESS:
      case PickupLocation::PROPERTY_PUBLISHED_STRINGS:
      case self::PROPERTY_MEMBER_NAME:
      case self::PROPERTY_CREATE_DATE:
      case self::PROPERTY_CREATE_MEMBER_ID:
      case self::PROPERTY_CREATE_MEMBER_NAME:
      case self::PROPERTY_MODIFY_DATE:
      case self::PROPERTY_MODIFY_MEMBER_ID:
      case self::PROPERTY_MODIFY_MEMBER_NAME:
      case self::PROPERTY_COORDINATING_GROUP_ID:
      case self::PROPERTY_PICKUP_LOCATION_GROUP_ID:
      case CoopOrder::PROPERTY_STATUS:
      case CoopOrder::PROPERTY_START:
      case CoopOrder::PROPERTY_END:
      case CoopOrder::PROPERTY_DELIVERY:
      case CoopOrder::PROPERTY_MAX_COOP_TOTAL:
      case CoopOrder::PROPERTY_MAX_BURDEN:
        $trace = debug_backtrace();
        throw new Exception(
          'Forbidden set operation for ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line']);
      default:
        parent::__set( $name, $value );
    }
  } 
  
  protected function CheckAccess()
  {
    global $g_oMemberSession;
    
    $bMyOrderEdit = FALSE;
    
    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_COOP_ORDER_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS, 
            Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    
    if ( $this->m_aData[self::PROPERTY_MEMBER_ID] == 0)
      $this->m_aData[self::PROPERTY_MEMBER_ID] = $this->m_aOriginalData[self::PROPERTY_MEMBER_ID];
    
    if ($this->m_aData[self::PROPERTY_MEMBER_ID] == 0 || $this->m_aData[self::PROPERTY_MEMBER_ID] == $g_oMemberSession->MemberID)
    {
      $bMyOrderEdit = $this->AddPermissionBridge(self::PERMISSION_PAGE_ACCESS, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
    }
    
    return ($bCoord || $bView || $bMyOrderEdit);
  }
  
  protected function CheckCoordinator()
  {
    $bModify =  $this->AddPermissionBridgeGroupID(self::PERMISSION_COORD, FALSE);
    
    $bView = $this->SetRecordGroupID(self::PERMISSION_VIEW, $this->m_aData[self::PROPERTY_PICKUP_LOCATION_GROUP_ID], FALSE);
    
    return ($bModify || $bView);
  }
  
  //get whether order got larger since postback - other in terms of burden ot total sum (which means CanEnlarge might have been crossed)
  public function IsLarger()
  {
     return ( $this->m_aData[self::PROPERTY_COOP_TOTAL] > $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL] ||
              $this->m_aData[self::PROPERTY_TOTAL_BURDEN] > $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN] );
  }
  
  public function LoadRecord($nID)
  {
    global $g_oMemberSession;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    if (!$this->CheckAccess())
    {
       $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
       return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_ID] = $nID;
    
    $sSQL =   " SELECT O.CoopOrderKeyID, O.MemberID, IfNull(O.PickupLocationKeyID,0) PickupLocationKeyID, O.dCreated, O.dModified, IfNull(O.mCoopTotal,0) as OrderCoopTotal, " . 
    " O.mProducerTotal as OrderProducerTotal, O.mCoopTotalIncFee, O.sMemberComments, O.mCoopFee, " . 
    " O.bHasItemComments, IfNull(O.fBurden,0) as OrderBurden, PL.CoordinatingGroupID PickupLocationGroupID, "  .
    " O.CreatedByMemberID, O.ModifiedByMemberID, M.sName as MemberName, MC.sName as CreateMemberName, MM.sName as ModifyMemberName, " .
    " M.sLoginName, M.sEMail, M.sEMail2, M.sEMail3, M.sEMail4, " .
           $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
    ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, 'sPickupLocationAddress') .
    ", " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS, 'sPickupLocationComments') .
    " FROM T_Order O INNER JOIN T_Member M ON M.MemberID = O.MemberID " . 
    " LEFT JOIN T_PickupLocation PL ON PL.PickupLocationKeyID = O.PickupLocationKeyID " . 
    " LEFT JOIN T_Member MC ON MC.MemberID = O.CreatedByMemberID  " . 
    " LEFT JOIN T_Member MM ON MM.MemberID = O.ModifiedByMemberID  " . 
    $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
    $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
    $this->ConcatForeignStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS, Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
    " WHERE O.OrderID = " . $this->m_aData[self::PROPERTY_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_MEMBER_ID] = $rec["MemberID"];    
    $this->m_aData[self::PROPERTY_COOP_ORDER_ID] = $rec["CoopOrderKeyID"];
    $this->m_aData[self::PROPERTY_MEMBER_NAME] = $rec["MemberName"];
    $this->m_aData[self::PROPERTY_MEMBER_COMMENTS] = $rec["sMemberComments"];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] = $rec["PickupLocationKeyID"];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_NAME] = $rec["sPickupLocation"];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ADDRESS] = $rec["sPickupLocationAddress"];
    $this->m_aData[PickupLocation::PROPERTY_PUBLISHED_STRINGS] = $rec["sPickupLocationComments"];
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_GROUP_ID] = $rec["PickupLocationGroupID"];
    $this->m_aData[self::PROPERTY_COOP_TOTAL] = $rec["OrderCoopTotal"];
    $this->m_aData[self::PROPERTY_COOP_TOTAL_INC_FEE] = $rec["mCoopTotalIncFee"];
    $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $rec["OrderBurden"];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $rec["OrderProducerTotal"];
    $this->m_aData[self::PROPERTY_COOP_FEE] = $rec["mCoopFee"];
    $this->m_aData[self::PROPERTY_CREATE_DATE] =  new DateTime($rec["dCreated"]);
    $this->m_aData[self::PROPERTY_CREATE_MEMBER_ID] = $rec["CreatedByMemberID"];
    $this->m_aData[self::PROPERTY_CREATE_MEMBER_NAME] = $rec["CreateMemberName"];
    $this->m_aData[self::PROPERTY_MODIFY_DATE] = new DateTime($rec["dModified"]);
    $this->m_aData[self::PROPERTY_MODIFY_MEMBER_ID] = $rec["ModifiedByMemberID"];
    $this->m_aData[self::PROPERTY_MODIFY_MEMBER_NAME] = $rec["ModifyMemberName"];
    $this->m_aData[self::PROPERTY_HAS_ITEMS_COMMENTS] = $rec["bHasItemComments"];
    
    $this->m_aData[Member::PROPERTY_LOGIN_NAME] = $rec["sLoginName"];
    $this->m_aData[Member::PROPERTY_EMAIL] = $rec["sEMail"];
    $this->m_aData[Member::PROPERTY_EMAIL2] = $rec["sEMail2"];
    $this->m_aData[Member::PROPERTY_EMAIL3] = $rec["sEMail3"];
    $this->m_aData[Member::PROPERTY_EMAIL4] = $rec["sEMail4"];
    
    $this->LoadCoopOrderPickupLocation();
    
    $this->CanModify();
    if ($this->m_nLastOperationStatus != parent::OPERATION_STATUS_NONE)
      return FALSE;

    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  //get contact info based on Is Contact Person flag set in coordinate.php for coordinating groups
  public function GetContacts(&$arrCOContacts, &$arrPLContacts)
  {
    $this->GetContactsForGroup($arrCOContacts, self::PROPERTY_COOP_ORDER_CONTACTS, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID]);
    $this->GetContactsForGroup($arrPLContacts, self::PROPERTY_PICKUP_LOCATION_CONTACTS, $this->m_aData[self::PROPERTY_PICKUP_LOCATION_GROUP_ID]);
  }
  
  //help get contact info
  protected function GetContactsForGroup(&$arrContacts, $nPropertyIndex, $nGroup)
  {
    if ($this->m_aData[$nPropertyIndex] != NULL)
      $arrContacts = unserialize(base64_decode($this->m_aData[$nPropertyIndex]));
    else if ($this->m_aOriginalData[$nPropertyIndex] != NULL)
    {
      $this->m_aData[$nPropertyIndex] = $this->m_aOriginalData[$nPropertyIndex];
      $arrContacts = unserialize(base64_decode($this->m_aData[$nPropertyIndex]));
    }
    else if ($nGroup > 0)
    {
      $arrContacts = $this->GetGroupContactPersons($nGroup);
      $this->m_aData[$nPropertyIndex] = base64_encode(serialize($arrContacts));
      $this->m_aOriginalData[$nPropertyIndex] = $this->m_aData[$nPropertyIndex];
    }
  }
 
  
  public function Add()
  {
      global $g_oMemberSession;
      global $g_dNow;
      $dNow = $g_dNow;
      $sNow = $dNow->format(DATABASE_DATE_FORMAT);
      
      if ($this->m_aData[self::PROPERTY_MEMBER_ID] <= 0)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
      }
      
      if (!$this->CanModify())
        return FALSE;

      if (!$this->Validate())
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
        return FALSE;
      }

      //insert the record
      $sSQL =  " INSERT INTO T_Order( CoopOrderKeyID, MemberID, dCreated, dModified, CreatedByMemberID, ModifiedByMemberID " .
                $this->ConcatColIfNotValue(self::PROPERTY_PICKUP_LOCATION_ID, "PickupLocationKeyID", 0)  . 
                " ,sMemberComments ) " .
               " VALUES ( " . $this->m_aData[self::PROPERTY_COOP_ORDER_ID] .   ", " . 
              $this->m_aData[self::PROPERTY_MEMBER_ID] .    
              ", ? , ?, " . $g_oMemberSession->MemberID .   ", " .  $g_oMemberSession->MemberID .   
              $this->ConcatValIfNotValue(self::PROPERTY_PICKUP_LOCATION_ID, 0) . ", ? );";

      $this->RunSQLWithParams($sSQL, array( $sNow, $sNow, $this->m_aData[self::PROPERTY_MEMBER_COMMENTS] ));

      $this->m_aData[self::PROPERTY_ID] = $this->GetLastInsertedID();

      $this->m_aOriginalData = $this->m_aData;

      //suppress record load messages (already displayed earlier)
      $this->m_aData[self::PROPERTY_SUPRESS_MESSAGES] = TRUE;
      $bReturn = $this->LoadRecord($this->m_aData[self::PROPERTY_ID]);
      $this->m_aData[self::PROPERTY_SUPRESS_MESSAGES] = FALSE;
      
      return $bReturn;
  }
  
  
  public function Edit()
  {
    global $g_oMemberSession;
    global $g_dNow;
    $dNow = $g_dNow;
    $sNow = $dNow->format(DATABASE_DATE_FORMAT);
        
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    if (!$this->CanModify())
    {
      $this->CopyOriginalDataWhenUnsaved();
      return FALSE;
    }
    
    if (!$this->Validate())
    {
      $this->CopyOriginalDataWhenUnsaved();
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();

      $sSQL =   " UPDATE T_Order " .
                " SET PickupLocationKeyID =  ? , dModified = ?, ModifiedByMemberID = ?, sMemberComments = ? ";
      if ($this->HasPermission(self::PERMISSION_COORD)) //allow changing a user for coordinators
        $sSQL .= ", MemberID = " . $this->m_aData[self::PROPERTY_MEMBER_ID];
      $sSQL .=  " WHERE OrderID = " . $this->m_aData[self::PROPERTY_ID] . ';';

      $nPickupLocationID = NULL;
      //allow null for pickup location
      if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] > 0)
        $nPickupLocationID = $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID];

      $this->RunSQLWithParams( $sSQL, array(
                $nPickupLocationID,
                $sNow,
                $g_oMemberSession->MemberID,
                $this->m_aData[self::PROPERTY_MEMBER_COMMENTS]
          ));

      $this->m_aData[self::PROPERTY_COOP_ORDER_ID] =  $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];

      //Recalculate totals for coop order pickup locations    
      if ($this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID] > 0 ||
          $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] > 0 )
      {
        $oCalc = new CoopOrderCalculate( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] );
        $oCalc->CalculatePickupLocs(TRUE);
        unset($oCalc);
      }
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    //suppress record load messages (already displayed earlier)
    $this->m_aData[self::PROPERTY_SUPRESS_MESSAGES] = TRUE;
    $bReturn = $this->LoadRecord($this->m_aData[self::PROPERTY_ID]);
    $this->m_aData[self::PROPERTY_SUPRESS_MESSAGES] = FALSE;

    return $bReturn;
  }

  public function Delete()
  {
    global $g_oError;
    global $g_oMemberSession;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }

    //general permission check and special delete permission
    if ( !$this->CanModify() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }   
    
    //if not deleting own order and hasn't permission to delete others
    if ($g_oMemberSession->MemberID != $this->m_aOriginalData[self::PROPERTY_MEMBER_ID] &&
            !$this->HasPermission(self::PERMISSION_DELETE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();

      $sSQL =   " DELETE FROM T_Order " .
                " WHERE OrderID = " . $this->m_aData[self::PROPERTY_ID] . ';';

      $this->RunSQL($sSQL); //deletes all child records

      $oCalc = new CoopOrderCalculate( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] );
      $oCalc->Run();
      unset($oCalc);
      
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }

    //reset data, except coop order and member
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aData[self::PROPERTY_COOP_ORDER_ID] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];
    $this->m_aData[self::PROPERTY_MEMBER_ID] = $this->m_aOriginalData[self::PROPERTY_MEMBER_ID];
    $this->m_aOriginalData = $this->m_aData; //erase old original data
    $bSuccess = $this->CanModify();
    $this->m_aOriginalData = $this->m_aData; //save data for serialization
    return $bSuccess;
  }
  
  //load data and validation for the coop order
  public function LoadCoopOrder($nCoopOrderID)
  {
    global $g_oMemberSession;
    
    $this->m_aData[self::PROPERTY_COOP_ORDER_ID] =  $nCoopOrderID;
    $this->m_aData[self::PROPERTY_MEMBER_ID] = $g_oMemberSession->MemberID;
    $bSuccess = $this->CanModify();
    $this->m_aOriginalData = $this->m_aData; //save data for serialization
    return $bSuccess;
  }
  
  //called from items screen to validate totals after save was clicked
  public function RunTotalsValidations()
  {
    $this->m_bRunningTotalsValidation = TRUE;
    $this->CanModify();
    $this->m_bRunningTotalsValidation = FALSE;
  }
  
  //called from items screen to validate totals after save was clicked
  public function SetCoopOrderTotalsForValidations()
  {    
    $this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN] = $this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN]
            - $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN]
            + $this->m_aData[self::PROPERTY_TOTAL_BURDEN];
    
    $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL]
            - $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL]
            + $this->m_aData[self::PROPERTY_COOP_TOTAL];
  }
  
  //called when changing pickup location or items in the order
  //validates pickup location max burden and max total were not crossed
  public function ValidatePickupLocation($bFromItems)
  {
    global $g_oError;
    
    $bValid = TRUE;
    
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL] = $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL]
         + $this->m_aData[self::PROPERTY_COOP_TOTAL];
    
    if ($bFromItems) //original coop sum already included in total, so remove it
         $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL]-= $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
    
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN] = $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN]
         + $this->m_aData[self::PROPERTY_TOTAL_BURDEN];
    
    if ($bFromItems) //original burden already included in total, so remove it
         $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN]-= $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];
      
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_TOTAL] != NULL && (!$bFromItems ||
         $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL] > $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL]))
    {  
      if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL] > $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_TOTAL])
      {
        if ($this->HasPermission(self::PERMISSION_COORD))
          $g_oError->AddError('<!$ORDER_CANNOT_BE_ENLARGED_WHEN_PICKUP_LOCATION_CAPACITY_IS_FULL_COOP_TOTAL_REASON$!>');
        $bValid = FALSE;
      }
    }
    
    //validate burden only when it is larger/equal then was before save and there is a max burden
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_BURDEN] != NULL && (!$bFromItems ||
         $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN] > $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN]) )
    {
      if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN] > $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_BURDEN])
      {
        if ($this->HasPermission(self::PERMISSION_COORD))
          $g_oError->AddError('<!$ORDER_CANNOT_BE_ENLARGED_WHEN_PICKUP_LOCATION_CAPACITY_IS_FULL_BURDEN_REASON$!>');
        $bValid = FALSE;
      }
    }
    
    if (!$bValid)
      $g_oError->AddError('<!$ORDER_CANNOT_BE_ENLARGED_WHEN_PICKUP_LOCATION_CAPACITY_IS_FULL$!>');
    
    return $bValid;
  }
  
  //this function helps get up-to-date data about pickup location capacities - recognizing the need to assert those are not exeeded
  //it is called both when loading the order/orderitems screen and when posting back changes in those screens (for validation)
  public function LoadCoopOrderPickupLocation()
  {
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL] = 0;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN] = 0;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_TOTAL] = NULL;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_BURDEN] = NULL;
    
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] == 0)
      return;
    
    $oCOPL = new CoopOrderPickupLocation;
    
    $oCOPL->PickupLocationID = $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID];
    $oCOPL->CoopOrderID = $this->m_aData[self::PROPERTY_COOP_ORDER_ID];
    $oCOPL->ForMember = TRUE;
    if (!$oCOPL->LoadRecord())
      return;

    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_COOP_TOTAL] = $oCOPL->CoopTotal;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_TOTAL_BURDEN] = $oCOPL->TotalBurden;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_TOTAL] = $oCOPL->MaxCoopTotal;
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_MAX_BURDEN] = $oCOPL->MaxBurden;
    
    unset($oCOPL);
  }
  
  //preserve data after post back
  protected function CopyOriginalDataWhenUnsaved()
  {
    $this->m_aData[self::PROPERTY_PICKUP_LOCATION_NAME] = $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_NAME];
    $this->m_aData[self::PROPERTY_MEMBER_NAME] = $this->m_aOriginalData[self::PROPERTY_MEMBER_NAME];
    $this->m_aData[self::PROPERTY_PRODUCER_TOTAL] = $this->m_aOriginalData[self::PROPERTY_PRODUCER_TOTAL];
    $this->m_aData[self::PROPERTY_COOP_FEE] = $this->m_aOriginalData[self::PROPERTY_COOP_FEE];
    $this->m_aData[self::PROPERTY_CREATE_DATE] =  $this->m_aOriginalData[self::PROPERTY_CREATE_DATE];
    $this->m_aData[self::PROPERTY_CREATE_MEMBER_ID] = $this->m_aOriginalData[self::PROPERTY_CREATE_MEMBER_ID];
    $this->m_aData[self::PROPERTY_CREATE_MEMBER_NAME] = $this->m_aOriginalData[self::PROPERTY_CREATE_MEMBER_NAME];
    $this->m_aData[self::PROPERTY_MODIFY_DATE] = $this->m_aOriginalData[self::PROPERTY_MODIFY_DATE];
    $this->m_aData[self::PROPERTY_MODIFY_MEMBER_ID] =  $this->m_aOriginalData[self::PROPERTY_MODIFY_MEMBER_ID];
    $this->m_aData[self::PROPERTY_MODIFY_MEMBER_NAME] = $this->m_aOriginalData[self::PROPERTY_MODIFY_MEMBER_NAME];
    $this->m_aData[self::PROPERTY_HAS_ITEMS_COMMENTS] = $this->m_aOriginalData[self::PROPERTY_HAS_ITEMS_COMMENTS];
    
    $this->m_aData[Member::PROPERTY_LOGIN_NAME] = $this->m_aOriginalData[Member::PROPERTY_LOGIN_NAME];
    $this->m_aData[Member::PROPERTY_EMAIL] = $this->m_aOriginalData[Member::PROPERTY_EMAIL];
    $this->m_aData[Member::PROPERTY_EMAIL2] = $this->m_aOriginalData[Member::PROPERTY_EMAIL2];
    $this->m_aData[Member::PROPERTY_EMAIL3] = $this->m_aOriginalData[Member::PROPERTY_EMAIL3];
    $this->m_aData[Member::PROPERTY_EMAIL4] = $this->m_aOriginalData[Member::PROPERTY_EMAIL4];

  }
  
  //get coop order data for validations and calculations
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
    
    if ( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] == 0 )
      $this->m_aData[self::PROPERTY_COOP_ORDER_ID] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_ID];
    
    if ( $this->m_aData[self::PROPERTY_COOP_ORDER_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $sSQL =   " SELECT CO.dStart, CO.dEnd, CO.dDelivery, CO.mCoopFee, CO.mSmallOrder, " .
              " CO.mSmallOrderCoopFee, CO.fCoopFee, CO.CoordinatingGroupID, " .
              " CO.nStatus, CO.mMaxCoopTotal,  CO.fMaxBurden, " .
              " IfNull(CO.fBurden,0) fBurden, IfNull(CO.mCoopTotal,0) mCoopTotal, " .
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

    $this->m_aData[self::PROPERTY_COOP_ORDER_NAME] = $rec["sCoopOrder"];
    $this->m_aData[CoopOrder::PROPERTY_STATUS] = $rec["nStatus"];
    $this->m_aData[CoopOrder::PROPERTY_START] = new DateTime($rec["dStart"]);
    $this->m_aData[CoopOrder::PROPERTY_END] = new DateTime($rec["dEnd"]);
    $this->m_aData[CoopOrder::PROPERTY_DELIVERY] = new DateTime($rec["dDelivery"]);
    $this->m_aData[CoopOrder::PROPERTY_COOP_FEE] = $rec["mCoopFee"];
    $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER] = $rec["mSmallOrder"];
    $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE] = $rec["mSmallOrderCoopFee"];
    $this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] = $rec["fCoopFee"];
    $this->m_aData[CoopOrder::PROPERTY_MAX_COOP_TOTAL] = $rec["mMaxCoopTotal"];
    $this->m_aData[CoopOrder::PROPERTY_MAX_BURDEN] = $rec["fMaxBurden"];
    $this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN] = $rec["fBurden"];
    $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $rec["mCoopTotal"];
    
    //see if active coop order is open
    $this->m_aData[self::PROPERTY_COOP_ORDER_STATUS_OBJECT] = new ActiveCoopOrderStatus($this->m_aData[CoopOrder::PROPERTY_END], 
      $this->m_aData[CoopOrder::PROPERTY_DELIVERY], $this->m_aData[CoopOrder::PROPERTY_STATUS]);
    
    //check delete permissions
    $this->AddPermissionBridge(self::PERMISSION_DELETE, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_DELETE, 
         Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID], FALSE);
        
    return TRUE;
  }
  
  //check permissions, if the user can modify or enlarge the order, and possibly display error messages or warnings
  protected function CanModify()
  {
    global $g_oError;
    global $g_dNow;
    $dNow = $g_dNow;
    
    global $g_oMemberSession;
    
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    $this->m_bCanModify = FALSE;
    $this->m_aData[self::PROPERTY_CAN_ENLARGE] = FALSE;
    
    if (!$this->m_bRunningTotalsValidation)
    {
      //load coop order data on every postback, to make validations against up-to-date data
      if (!$this->LoadCoopOrderData())
        return FALSE;
   
      //has coordinating permissions?
      if ($this->HasPermission(self::PERMISSION_COORD))
      {
        //now let's check them: coordinating group permission check for updating any order in the current coop order
        if ( !$this->CheckCoordinator() )
        {
          //user has no coordination permissions for the current coop order
          //if does not have regular permissions, block hir
          if (!$this->HasPermission(self::PERMISSION_PAGE_ACCESS))
          {
            $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
            return FALSE;
          }
        }
      }

      //member can order (has balance/pays at pickup) or coordinator
      if (!$g_oMemberSession->CanOrder && !$this->HasPermission(self::PERMISSION_COORD))
      {
        if (!$this->HasPermission(self::PERMISSION_VIEW))
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        
        return FALSE;
      }

      if ($this->m_aData[CoopOrder::PROPERTY_STATUS] == 0)
        $this->m_aData[CoopOrder::PROPERTY_STATUS] = $this->m_aOriginalData[CoopOrder::PROPERTY_STATUS];

      //coop order status is active
      if ($this->m_aData[CoopOrder::PROPERTY_STATUS] != CoopOrder::STATUS_ACTIVE)
      {
        $this->AddError('<!$ORDER_READONLY_REASON_COOPORDER_IS_NOT_ACTIVE$!>');
        return FALSE;
      }

      if ($this->m_aData[CoopOrder::PROPERTY_END] == NULL)
        $this->m_aData[CoopOrder::PROPERTY_END] = $this->m_aOriginalData[CoopOrder::PROPERTY_END];

       if ($this->m_aData[CoopOrder::PROPERTY_DELIVERY] == NULL)
        $this->m_aData[CoopOrder::PROPERTY_DELIVERY] = $this->m_aOriginalData[CoopOrder::PROPERTY_DELIVERY];

      if ($this->m_aData[self::PROPERTY_COOP_ORDER_STATUS_OBJECT]->Status != ActiveCoopOrderStatus::Open)
      {
        if (!$this->HasPermission(self::PERMISSION_COORD))
        {
          $this->AddError('<!$ORDER_READONLY_REASON_ACTIVE_COOPORDER_NOT_OPEN_FOR_MEMBER$!>');
          return FALSE;
        }
        else
          $this->AddError('<!$ORDER_COORDINATOR_BYPASS_MEMBER_PERMISSIONS_WARNING$!><!$ORDER_READONLY_REASON_ACTIVE_COOPORDER_NOT_OPEN_FOR_MEMBER$!>'); 
      }

     if ($this->m_aData[CoopOrder::PROPERTY_MAX_BURDEN] == NULL)
        $this->m_aData[CoopOrder::PROPERTY_MAX_BURDEN] = $this->m_aOriginalData[CoopOrder::PROPERTY_MAX_BURDEN];

     if ($this->m_aData[CoopOrder::PROPERTY_MAX_COOP_TOTAL] == NULL)
        $this->m_aData[CoopOrder::PROPERTY_MAX_COOP_TOTAL] = $this->m_aOriginalData[CoopOrder::PROPERTY_MAX_COOP_TOTAL];

     if ($this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN] == NULL)
        $this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[CoopOrder::PROPERTY_TOTAL_BURDEN];

     if ($this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] == NULL)
        $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_ORDER_COOP_TOTAL];
    } //end of !m_bRunningTotalsValidation

    $oCoopOrderCapacity = new CoopOrderCapacity($this->m_aData[CoopOrder::PROPERTY_MAX_BURDEN], 
          $this->m_aData[CoopOrder::PROPERTY_TOTAL_BURDEN], 
          $this->m_aData[CoopOrder::PROPERTY_MAX_COOP_TOTAL], 
          $this->m_aData[self::PROPERTY_COOP_ORDER_COOP_TOTAL]);
    
    if ($oCoopOrderCapacity->Percent >= 100)
    {    
      if ($this->IsLarger())
      {
        //coop order percent < 100 and requesting to create a new order
        if ($this->m_aData[self::PROPERTY_ID] > 0)
          $this->AddError('<!$ORDER_CANNOT_BE_ENLARGED_WHEN_CAPACITY_IS_FULL$!>');

        //more detailed message for coordinators
        if ($this->HasPermission(self::PERMISSION_COORD))
        {
          if ($oCoopOrderCapacity->SelectedType == CoopOrderCapacity::TypeBurden)
            $this->AddError('<!$ORDER_READONLY_REASON_CANNOT_CREATE_NEW_DUE_TO_BURDER$!>');
          else if ($oCoopOrderCapacity->SelectedType == CoopOrderCapacity::TypeTotal)
            $this->AddError('<!$ORDER_READONLY_REASON_CANNOT_CREATE_NEW_DUE_TO_MAX_COOP_ORDER$!>');
        }
      }

      if ($this->m_aData[self::PROPERTY_ID] == 0)
      {
        $this->AddError('<!$ORDER_READONLY_REASON_CANNOT_CREATE_NEW_WHEN_CAPACITY_IS_FULL$!>');
        return FALSE; //block creating a new record
      }

      //CanEnlarge remains FALSE
      $this->m_bCanModify = TRUE;
      return TRUE;
    }

    $oMember = new Member;
    $mMaxOrder = $oMember->GetMaxOrder($this->m_aData[self::PROPERTY_MEMBER_ID]);
    unset($oMember);

    if ($mMaxOrder != NULL) //if not payment at pickup
    {
      if ($mMaxOrder < $this->m_aData[self::PROPERTY_COOP_TOTAL])
      {
        $this->m_bCanModify = TRUE;
        if (!$this->HasPermission(self::PERMISSION_COORD))
        {
          $this->AddError(sprintf('<!$ORDER_CANNOT_BE_ENLARGED_BEYOND_BALANCE$!>',$mMaxOrder));
          return TRUE; //CanEnlarge remains FALSE
        }
        else
          $this->AddError(sprintf('<!$ORDER_COORDINATOR_BYPASS_MEMBER_PERMISSIONS_WARNING$!><!$ORDER_CANNOT_BE_ENLARGED_BEYOND_BALANCE$!>',$mMaxOrder)); 
      }
    }

    $this->m_aData[self::PROPERTY_CAN_ENLARGE] = TRUE;
    $this->m_bCanModify = TRUE;
    return $this->m_bCanModify;
  }
  
  //validate order after save (called only for order header)
  protected function Validate()
  {
    global $g_oError;
    
    $bValid = TRUE;
    
    if (ORDER_PICKUP_LOCATION_IS_REQUIRED) //global setting
    {
      if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] <= 0)
      {
        $g_oError->AddError(sprintf('<!$FIELD_SELECT_REQUIRED$!>', '<!$FIELD_PICKUP_LOCATION_NAME$!>'));
        $bValid = FALSE;
      }
    }
    
    //validate pickup location change
    if ($this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] != NULL &&
      $this->m_aData[self::PROPERTY_PICKUP_LOCATION_ID] != $this->m_aOriginalData[self::PROPERTY_PICKUP_LOCATION_ID])
    {
      $this->LoadCoopOrderPickupLocation();
      //copy totals original data
      $this->m_aData[self::PROPERTY_COOP_TOTAL] = $this->m_aOriginalData[self::PROPERTY_COOP_TOTAL];
      $this->m_aData[self::PROPERTY_TOTAL_BURDEN] = $this->m_aOriginalData[self::PROPERTY_TOTAL_BURDEN];

      if (!$this->ValidatePickupLocation(FALSE))
        $bValid = FALSE;
    }
    
    return $bValid;
  }
  
  //there are two types of fee: one fixed (with small order discount option), and another as percent of the total order
  //any, both or none of them can be set. If both are set, a cumulation occurs
  public function CalculateCoopFee()
  {
    $mFee = 0;
    //no fee if no order
    if ($this->m_aData[self::PROPERTY_COOP_TOTAL] == NULL || $this->m_aData[self::PROPERTY_COOP_TOTAL] <= 0)
    {
      $this->m_aData[self::PROPERTY_COOP_FEE] = NULL;
      return;
    }

    //small order
    if ($this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE] != NULL &&
        $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE] != 0
        && $this->m_aData[self::PROPERTY_COOP_TOTAL] <= $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER])
    {
      $mFee += $this->m_aData[CoopOrder::PROPERTY_SMALL_ORDER_COOP_FEE];
    }
    //regular order
    else if ($this->m_aData[CoopOrder::PROPERTY_COOP_FEE] != NULL &&
        $this->m_aData[CoopOrder::PROPERTY_COOP_FEE] != 0)
    {
      $mFee += $this->m_aData[CoopOrder::PROPERTY_COOP_FEE];
    }

    //fee as percent
    if ($this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] != NULL && $this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] != 0)
    {
      $mFee += (($this->m_aData[CoopOrder::PROPERTY_COOP_FEE_PERCENT] / 100) * $this->m_aData[self::PROPERTY_COOP_TOTAL]);
    }
    
    //set fee
    if ($mFee == 0)
      $this->m_aData[self::PROPERTY_COOP_FEE] = NULL;
    else
    {
      $mFee = Rounding::Round($mFee, ROUND_SETTING_ORDER_COOP_FEE);
      $this->m_aData[self::PROPERTY_COOP_FEE] = $mFee;
    }
    
    $this->m_aData[self::PROPERTY_COOP_TOTAL_INC_FEE] = $this->m_aData[self::PROPERTY_COOP_TOTAL] + $mFee;
  }
  
  //build string of order page title
  public function BuildPageTitle()
  {
    global $g_oMemberSession;
    
    if ($this->m_aData[self::PROPERTY_ID] > 0)
    {
      if ($this->m_aData[self::PROPERTY_MEMBER_ID] == $g_oMemberSession->MemberID)
      {
        $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION] = '<!$MY_ORDER_NAME_SUFFIX$!>';
        $this->m_aData[self::PROPERTY_PAGE_TITLE] = $this->m_aData[self::PROPERTY_COOP_ORDER_NAME] . '<!$PAGE_TITLE_SEPARATOR$!>' . 
                $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION];

      }
      else
      {
        $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION] = sprintf('<!$USER_ORDER_NAME_SUFFIX$!>', $this->m_aData[self::PROPERTY_MEMBER_NAME]);
        $this->m_aData[self::PROPERTY_PAGE_TITLE] = $this->m_aData[self::PROPERTY_COOP_ORDER_NAME] . '<!$PAGE_TITLE_SEPARATOR$!>' . 
                $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION];
      }
    }
    else
    {
      $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION] = '<!$NEW_ORDER_SUFFIX$!>';
      $this->m_aData[self::PROPERTY_PAGE_TITLE] = $this->m_aData[self::PROPERTY_COOP_ORDER_NAME] . '<!$PAGE_TITLE_SEPARATOR$!>' . 
                $this->m_aData[self::PROPERTY_PAGE_TITLE_ADDITION];
    }
  }
  
  //allow suppresssion of message when the class is used from the orderitems page
  protected function AddError($sError)
  {
    //if to supress messages:
    //the part about running totals validation means that if the order originally couldn't be enlarged
    //we can suppress messages here, because they would be displayed anyway after the save fails, when simply loading the order as is
    if ( $this->m_aData[self::PROPERTY_SUPRESS_MESSAGES] )
      return;
    
    global $g_oError;
    
    $g_oError->AddError($sError);
  }
}

?>
