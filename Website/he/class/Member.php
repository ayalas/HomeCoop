<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitate membership edit/view
class Member extends SQLBase  {
  
  const POST_ACTION_BLOCK_USER = 11;
  const POST_ACTION_ACTIVATE = 12;
  const POST_ACTION_DEACTIVATE = 13;
  
  const PROPERTY_MEMBER_NAME = "Name";
  const PROPERTY_LOGIN_NAME = "LoginName";
  const PROPERTY_NEW_PASSWORD = "NewPassword";
  const PROPERTY_VERIFY_PASSWORD = "VerifyPassword";
  const PROPERTY_EMAIL = "EMail";
  const PROPERTY_EMAIL2 = "EMail2";
  const PROPERTY_EMAIL3 = "EMail3";
  const PROPERTY_EMAIL4 = "EMail4";
  const PROPERTY_PAYMENT_METHOD_ID = "PaymentMethodID";
  const PROPERTY_PAYMENT_METHOD_NAME = "PaymentMethodName";
  const PROPERTY_JOINED_ON = "JoinedOn";
  const PROPERTY_BALANCE = "Balance";
  const PROPERTY_BALANCE_HELD = "BalanceHeld";
  const PROPERTY_BALANCE_INVESTED = "BalanceInvested";
  const PROPERTY_PERCENT_OVER_BALANCE ="PercentOverBalance";
  const PROPERTY_IS_COORDINATOR = "IsCoordinator"; 
  const PROPERTY_CAN_MODIFY = "CanModify";
  const PROPERTY_IS_SYS_ADMIN = "IsSysAdmin";
  const PROPERTY_HAS_NO_PERMISSIONS = "HasNoPermissions";
  const PROPERTY_IS_REGULAR_MEMBER = "IsRegularMember";
  const PROPERTY_IS_DISABLED = "IsDisabled";
  const PROPERTY_MAX_ORDER = "MaxOrder";
  const PROPERTY_CACHIER_PICKUP_LOCATION_ID = "PickupLocationID";
  const PROPERTY_COMMENTS = "Comments";
  
  public function __construct()
  {
    $this->m_aDefaultData = array( self::PROPERTY_ID => 0,
                            self::PROPERTY_MEMBER_NAME => NULL,
                            self::PROPERTY_LOGIN_NAME => NULL,
                            self::PROPERTY_EMAIL => NULL,
                            self::PROPERTY_EMAIL2 => NULL,
                            self::PROPERTY_EMAIL3 => NULL,
                            self::PROPERTY_EMAIL4 => NULL,
                            self::PROPERTY_PAYMENT_METHOD_ID => DEFAULT_PAYMENT_METHOD_FOR_NEW_MEMBERS,
                            self::PROPERTY_PAYMENT_METHOD_NAME => NULL,
                            self::PROPERTY_JOINED_ON => NULL,
                            self::PROPERTY_BALANCE => 0,
                            self::PROPERTY_BALANCE_HELD => 0,
                            self::PROPERTY_BALANCE_INVESTED => NULL,
                            self::PROPERTY_PERCENT_OVER_BALANCE => DEFAULT_PERCENT_OVER_BALANCE_FOR_NEW_MEMBERS,
                            self::PROPERTY_IS_COORDINATOR => FALSE,
                            self::PROPERTY_NEW_PASSWORD => NULL,
                            self::PROPERTY_VERIFY_PASSWORD => NULL,
                            self::PROPERTY_COORDINATING_GROUP_ID => NULL,
                            self::PROPERTY_CAN_MODIFY => TRUE,
                            self::PROPERTY_IS_SYS_ADMIN => FALSE,
                            self::PROPERTY_HAS_NO_PERMISSIONS => FALSE,
                            self::PROPERTY_IS_REGULAR_MEMBER => FALSE,
                            self::PROPERTY_IS_DISABLED => FALSE,
                            self::PROPERTY_MAX_ORDER => FALSE,
                            self::PROPERTY_CACHIER_PICKUP_LOCATION_ID => 0,
                            self::PROPERTY_COMMENTS => NULL,
                            UserSessionBase::KEY_EXPORT_FORMAT => NULL,
                            );
    $this->m_aData = $this->m_aDefaultData;
    $this->m_aOriginalData = $this->m_aDefaultData; 
  }
    
  //limit properties that can be set
  public function __set( $name, $value ) {
    switch ($name)
    {
      case self::PROPERTY_JOINED_ON:
      case self::PROPERTY_IS_COORDINATOR:
      case self::PROPERTY_CAN_MODIFY:
      case self::PROPERTY_MAX_ORDER:
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
  
 //access to roles screen?
 public function HasAccessToRoles()
 {
   $oMR = new MemberRoles();
   return $oMR->CheckAccess();
 }
  
  //access to the given member's page?
  public function CheckAccess()
  {
      global $g_oMemberSession;
      
     $bView = FALSE;
    
     //has coordinator access to member's page?
     if ($this->HasPermission(self::PERMISSION_COORD) || $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
          Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
        $this->m_aData[self::PROPERTY_IS_COORDINATOR] = TRUE;
     
     //save delete permission for displaying delete button
     if (!$this->HasPermission(self::PERMISSION_DELETE))
       $this->AddPermissionBridge(self::PERMISSION_DELETE, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_DELETE, 
            Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
     
     //save view permission
     $bView = $this->HasPermission(self::PERMISSION_VIEW) || $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_VIEW, 
           Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
     
     //members can access their own page
     if ($g_oMemberSession->MemberID == $this->m_aData[self::PROPERTY_ID])
       return TRUE;
     
     //must be coordinator to access other members pages
     return $this->m_aData[self::PROPERTY_IS_COORDINATOR] || $bView;
  }
  
  //revoke modify permission if not a coordinator. 
  //called from member page
  public function RevokeModifyPermission()
  {
    if (!$this->m_aData[self::PROPERTY_IS_COORDINATOR])
      $this->m_aData[self::PROPERTY_CAN_MODIFY] = FALSE;
  }
  
  public function LoadRecord($nID)
  {   
    global $g_oMemberSession;
    global $g_oError;
    global $g_oTimeZone;
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    $this->m_aData[self::PROPERTY_ID] = $nID;
    
    if ( $nID <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //permission check
    if ( !$this->CheckAccess() )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    $sSQL =   " SELECT M.MemberID, M.sName, M.sLoginName, M.sEMail, sComments, M.nExportFormat,
             M.PaymentMethodKeyID, M.dJoined, IfNull(M.mBalance,0) mBalance, IfNull(M.mBalanceHeld,0) mBalanceHeld, M.mBalanceInvested, M.fPercentOverBalance, M.sEMail2, 
             M.bDisabled, M.sEMail3, M.sEMail4, (SELECT CG.CoordinatingGroupID FROM T_CoordinatingGroupMember CGM INNER JOIN
             T_CoordinatingGroup CG ON CGM.CoordinatingGroupID = CG.CoordinatingGroupID
            WHERE CGM.MemberID = M.MemberID AND CG.sCoordinatingGroup IS NULL LIMIT 1) as CoordinatingGroupID, " .
            $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PAYMENT_METHODS, 'sPaymentMethod') .
            " FROM T_Member M INNER JOIN T_PaymentMethod PM ON M.PaymentMethodKeyID = PM.PaymentMethodKeyID " . 
             $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PAYMENT_METHODS) .
             " WHERE M.MemberID = " . $this->m_aData[self::PROPERTY_ID] . ';';

    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!is_array($rec) || count($rec) == 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return FALSE;
    }
    
    //if editing system admin record while not being a system admin
    if ($this->m_aData[self::PROPERTY_CAN_MODIFY] && $this->CheckMemberPermissions() && !$g_oMemberSession->IsSysAdmin)
    {
      $this->m_aData[self::PROPERTY_CAN_MODIFY] = FALSE;
      $g_oError->AddError('לא ניתן לערוך רשומה של מנהל/ת מערכת ללא הרשאת ניהול מערכת');
    }
       
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $rec["CoordinatingGroupID"];
    $this->m_aData[self::PROPERTY_MEMBER_NAME] = $rec["sName"];
    $this->m_aData[self::PROPERTY_LOGIN_NAME] = $rec["sLoginName"];    
    $this->m_aData[self::PROPERTY_EMAIL] = $rec["sEMail"];
    $this->m_aData[self::PROPERTY_EMAIL2] = $rec["sEMail2"];
    $this->m_aData[self::PROPERTY_EMAIL3] = $rec["sEMail3"];
    $this->m_aData[self::PROPERTY_EMAIL4] = $rec["sEMail4"];
    $this->m_aData[self::PROPERTY_IS_DISABLED] = $rec["bDisabled"];
    $this->m_aData[self::PROPERTY_COMMENTS] = $rec["sComments"];
    $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID] = $rec["PaymentMethodKeyID"];
    $this->m_aData[self::PROPERTY_PAYMENT_METHOD_NAME] = $rec["sPaymentMethod"];
    $this->m_aData[self::PROPERTY_JOINED_ON] = new DateTime($rec["dJoined"], $g_oTimeZone);
    $this->m_aData[self::PROPERTY_BALANCE] = $rec["mBalance"];
    $this->m_aData[self::PROPERTY_BALANCE_HELD] = $rec["mBalanceHeld"];
    $this->m_aData[self::PROPERTY_BALANCE_INVESTED] = $rec["mBalanceInvested"];
    $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE] = $rec["fPercentOverBalance"];
    
    if ($rec['nExportFormat'] != NULL)
      $this->m_aData[UserSessionBase::KEY_EXPORT_FORMAT] = $rec['nExportFormat'];
    
    $this->m_aData[self::PROPERTY_MAX_ORDER] = self::CalculateMaxOrder(
            $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID],
            $this->m_aData[self::PROPERTY_BALANCE],
            $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE]);
    
    $this->m_aOriginalData = $this->m_aData;
        
    return TRUE;
  }
  
  public function Add()
  {
    global $g_dNow, $g_oMemberSession;
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //must be coordinator to add members
    if ( !$this->CheckAccess() || !$this->m_aData[self::PROPERTY_IS_COORDINATOR])
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_JOINED_ON] = $g_dNow;
    
    try 
    {
      //for last inserted id to be returned for this insert
      $this->m_bUseClassConnection = TRUE;
      
      $this->BeginTransaction();
      
      //insert the record
      $sSQL =  " INSERT INTO T_Member( sName, sLoginName, sPassword, PaymentMethodKeyID, dJoined, sEMail, sEMail2, sEMail3, sEMail4, sComments ";
      
      if (MIGRATION_MODE)
      {
        $sSQL .=  ', sPasswordForMigration ';
      }
      $sSQL .=  $this->ConcatColIfNotNull(self::PROPERTY_BALANCE, "mBalance") .
              $this->ConcatColIfNotNull(self::PROPERTY_BALANCE_HELD, "mBalanceHeld") .
              $this->ConcatColIfNotNull(self::PROPERTY_BALANCE_INVESTED, "mBalanceInvested") .
              $this->ConcatColIfNotNull(self::PROPERTY_PERCENT_OVER_BALANCE, "fPercentOverBalance") .  
          " ) VALUES( :mname, :lname , md5(:pwd) ," .
          $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID] . ", :joined, :email1, :email2, :email3, :email4, :comments ";
      
      if (MIGRATION_MODE)
      {
        $sSQL .=  ', :pwd ';
      }
      
      $sSQL .=  $this->ConcatValIfNotNull(self::PROPERTY_BALANCE) .
             $this->ConcatValIfNotNull(self::PROPERTY_BALANCE_HELD) .
             $this->ConcatValIfNotNull(self::PROPERTY_BALANCE_INVESTED) .
             $this->ConcatValIfNotNull(self::PROPERTY_PERCENT_OVER_BALANCE) . " ); " ;

      $this->RunSQLWithParams($sSQL, array( "mname" => $this->m_aData[self::PROPERTY_MEMBER_NAME],
                                            "lname" => $this->m_aData[self::PROPERTY_LOGIN_NAME],
                                            "pwd" => $this->m_aData[self::PROPERTY_NEW_PASSWORD],
                                            "joined" => $this->m_aData[self::PROPERTY_JOINED_ON]->format(DATABASE_DATE_FORMAT),
                                            "email1" => $this->m_aData[self::PROPERTY_EMAIL],
                                            "email2" => $this->m_aData[self::PROPERTY_EMAIL2],
                                            "email3" => $this->m_aData[self::PROPERTY_EMAIL3],
                                            "email4" => $this->m_aData[self::PROPERTY_EMAIL4],
                                            'comments' => $this->m_aData[self::PROPERTY_COMMENTS],
          ));

      $this->m_aData[self::PROPERTY_NEW_PASSWORD] = NULL; //don't send passwords back to client
      $this->m_aData[self::PROPERTY_VERIFY_PASSWORD] = NULL;

      $this->m_aData[self::PROPERTY_ID] = $this->GetLastInsertedID();

      //insert personal group
      $sSQL = "INSERT INTO T_CoordinatingGroup(sCoordinatingGroup) VALUES( NULL );";
      $this->RunSQL($sSQL);
      $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->GetLastInsertedID();

      $sSQL = "INSERT INTO T_CoordinatingGroupMember(CoordinatingGroupId, MemberId) VALUES(" .
          $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] . "," . $this->m_aData[self::PROPERTY_ID] . ");";
      $this->RunSQL($sSQL);

      //INSERT DEFAULT ROLE
      $sSQL = "INSERT INTO T_MemberRole(MemberID, RoleKeyID) VALUES( " .
              $this->m_aData[self::PROPERTY_ID] . "," . Consts::ROLE_MEMBER . ");";
      $this->RunSQL($sSQL); 
      
      //check if need to update cachier / create transaction
      if ($this->m_aData[self::PROPERTY_BALANCE_HELD] != 0)
      {
        if ($this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID]  != 0)
        {
          $sSQL = " UPDATE T_PickupLocation SET mCachier = IFNULL(mCachier,0) + " . $this->m_aData[self::PROPERTY_BALANCE_HELD] .
                  " WHERE PickupLocationKeyID = ". $this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID] . ";";
          $this->RunSQL($sSQL); 
        }
        
        $sSQL = " INSERT INTO T_Transaction (PickupLocationKeyID, MemberID, ModifiedByMemberID, mAmount, dDate) " .
                 " VALUES(NullIf(:pickuplocid,0), :memberid, :modifier, :amount, :date);";
        
        $this->RunSQLWithParams($sSQL, array(
                  'pickuplocid' => $this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID],
                  'memberid' => $this->m_aData[self::PROPERTY_ID],
                  'modifier' => $g_oMemberSession->MemberID,
                  'amount' => $this->m_aData[self::PROPERTY_BALANCE_HELD],
                  'date' => $g_dNow->format(DATABASE_DATE_FORMAT),
                ));
      }
      
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
    
    $this->m_aData[self::PROPERTY_MAX_ORDER] = self::CalculateMaxOrder(
            $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID],
            $this->m_aData[self::PROPERTY_BALANCE],
            $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE]);
    
    $this->m_aData[self::PROPERTY_CAN_MODIFY] = TRUE;
    $this->m_aData[self::PROPERTY_IS_REGULAR_MEMBER] = TRUE;
    $this->m_aData[self::PROPERTY_HAS_NO_PERMISSIONS] = FALSE;

    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  public function Edit()
  {
    global $g_oError;
    global $g_oMemberSession;
    global $g_dNow;
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //must be coordinator to add members
    if ( !$this->CheckAccess())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //save unchanged data
    $this->PreserveFormData();
        
    //if editing system admin record while not being a system admin
    if ($this->CheckMemberPermissions() && !$g_oMemberSession->IsSysAdmin)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      $g_oError->AddError('לא ניתן לערוך רשומה של מנהל/ת מערכת ללא הרשאת ניהול מערכת');
      return FALSE;
    }
    
    if (!$this->Validate())
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      return FALSE;
    }
    
    try 
    {     
      $this->BeginTransaction();
    
      $sSQL =  " UPDATE T_Member " .
               " SET sName = :Name, sEMail = :EMail1, sEMail2 = :EMail2, sEMail3 = :EMail3, sEMail4 = :EMail4 ";
      $arrParams = array( "Name" => $this->m_aData[self::PROPERTY_MEMBER_NAME],
                                            "EMail1" => $this->m_aData[self::PROPERTY_EMAIL],
                                            "EMail2" => $this->m_aData[self::PROPERTY_EMAIL2],
                                            "EMail3" => $this->m_aData[self::PROPERTY_EMAIL3],
                                            "EMail4" => $this->m_aData[self::PROPERTY_EMAIL4]
          );

      if ($this->m_aData[self::PROPERTY_IS_COORDINATOR])
      {
         $sSQL .= " , PaymentMethodKeyID = " . $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID] . 
                  ", mBalance = :BalanceOrder, mBalanceHeld = :BalanceHeld, mBalanceInvested = :BalanceInvested, 
                   fPercentOverBalance = :PercentOverBalance, bDisabled = :Disabled, sComments = :Comments ";

         $arrParams["BalanceOrder"] = $this->m_aData[self::PROPERTY_BALANCE];
         $arrParams["BalanceHeld"] = $this->m_aData[self::PROPERTY_BALANCE_HELD];
         $arrParams["BalanceInvested"] = $this->m_aData[self::PROPERTY_BALANCE_INVESTED];
         
         $arrParams["PercentOverBalance"] = $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE];
         $arrParams["Disabled"] = $this->m_aData[self::PROPERTY_IS_DISABLED];
         $arrParams["Comments"] = $this->m_aData[self::PROPERTY_COMMENTS];
      }

      if ($this->m_aData[self::PROPERTY_NEW_PASSWORD] != NULL)
      {
        $sSQL .= ", sPassword = md5(:Password) ";
        if (MIGRATION_MODE)
        {
          $sSQL .= ", sPasswordForMigration = :Password ";
        }
        
        $arrParams["Password"] = $this->m_aData[self::PROPERTY_NEW_PASSWORD];
      }
      
      if ($this->m_aData[UserSessionBase::KEY_EXPORT_FORMAT] != NULL)
      {
        $sSQL .= ", nExportFormat = :ExportFormat ";
        $g_oMemberSession->ExportFormat = $this->m_aData[UserSessionBase::KEY_EXPORT_FORMAT];
        $arrParams["ExportFormat"] = $this->m_aData[UserSessionBase::KEY_EXPORT_FORMAT];
      }

      $sSQL .=  " WHERE MemberID = " . $this->m_aData[self::PROPERTY_ID];

      $this->RunSQLWithParams( $sSQL, $arrParams );
      
      //check if need to update cachier
      if ($this->m_aData[self::PROPERTY_BALANCE_HELD] != $this->m_aOriginalData[self::PROPERTY_BALANCE_HELD])
      {
        $mAmount = $this->m_aData[self::PROPERTY_BALANCE_HELD] - $this->m_aOriginalData[self::PROPERTY_BALANCE_HELD];
        
        if ($this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID]  != 0)
        {
        $sSQL = " UPDATE T_PickupLocation SET mCachier = IFNULL(mCachier,0) + (" . $mAmount . ") WHERE PickupLocationKeyID = ". 
            $this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID] . ";";
        $this->RunSQL($sSQL); 
        }
        
        $sSQL = " INSERT INTO T_Transaction (PickupLocationKeyID, MemberID, ModifiedByMemberID, mAmount, dDate) " .
                " VALUES(NullIf(:pickuplocid,0), :memberid, :modifier, :amount, :date);";
        
        $this->RunSQLWithParams($sSQL, array(
                  'pickuplocid' => $this->m_aData[self::PROPERTY_CACHIER_PICKUP_LOCATION_ID],
                  'memberid' => $this->m_aData[self::PROPERTY_ID],
                  'modifier' => $g_oMemberSession->MemberID,
                'amount' => $mAmount,
                'date' => $g_dNow->format(DATABASE_DATE_FORMAT),
              ));
      }
    
      $this->CommitTransaction();
    }
    catch(Exception $e)
    {
      $this->RollbackTransaction();
      throw $e;
    }
    
    $this->m_aData[self::PROPERTY_MAX_ORDER] = self::CalculateMaxOrder(
            $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID],
            $this->m_aData[self::PROPERTY_BALANCE],
            $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE]);
    
    $this->m_aData[self::PROPERTY_NEW_PASSWORD] = NULL; //don't send passwords back to client
    $this->m_aData[self::PROPERTY_VERIFY_PASSWORD] = NULL;
    
    $this->m_aOriginalData = $this->m_aData;

    return TRUE;
  }
  
  public function Deactivate()
  {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //must be coordinator to add members
    if ( !$this->CheckAccess())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //save unchanged data
    $this->PreserveFormData();
        
    $this->CheckMemberPermissions();
    
    if (!$this->m_aData[self::PROPERTY_IS_REGULAR_MEMBER])
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    $sSQL = " DELETE FROM T_MemberRole WHERE MemberID = ? AND RoleKeyID = ? ;";
    $this->RunSQLWithParams($sSQL, array($this->m_aData[self::PROPERTY_ID], Consts::ROLE_MEMBER));
    
    $this->m_aData[self::PROPERTY_IS_REGULAR_MEMBER] = FALSE;
    $this->m_aData[self::PROPERTY_HAS_NO_PERMISSIONS] = TRUE;
    
    return TRUE;
  }
  
  public function Activate()
  {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //must be coordinator to add members
    if ( !$this->CheckAccess())
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //save unchanged data
    $this->PreserveFormData();
        
    $this->CheckMemberPermissions();
    
    if (!$this->m_aData[self::PROPERTY_HAS_NO_PERMISSIONS])
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
   
    $sSQL = " INSERT INTO T_MemberRole(MemberID, RoleKeyID) VALUES(?,?);";
    $this->RunSQLWithParams($sSQL, array($this->m_aData[self::PROPERTY_ID], Consts::ROLE_MEMBER));
    
    $this->m_aData[self::PROPERTY_IS_REGULAR_MEMBER] = TRUE;
    $this->m_aData[self::PROPERTY_HAS_NO_PERMISSIONS] = FALSE;
    
    return TRUE;
  }
  
  public function Delete()
  {
    global $g_oError;
    global $g_oMemberSession;
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //must be coordinator to add members
    if ( !$this->CheckAccess() || !$this->HasPermission(self::PERMISSION_DELETE))
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    $this->m_aData[self::PROPERTY_NEW_PASSWORD] = NULL; //don't send passwords back to client
    $this->m_aData[self::PROPERTY_VERIFY_PASSWORD] = NULL;
    $this->PreserveFormData();
    $this->m_aOriginalData = $this->m_aData;
    
    //check that not current user
    if ($g_oMemberSession->MemberID == $this->m_aData[self::PROPERTY_ID])
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      $g_oError->AddError('לא ניתן למחוק את רשומת החבר/ה שלך עצמך.');
      return FALSE;
    }

    if ($this->CheckMemberPermissions() && !$g_oMemberSession->IsSysAdmin)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_VALIDATION_FAILED;
      $g_oError->AddError('לא ניתן לערוך רשומה של מנהל/ת מערכת ללא הרשאת ניהול מערכת');
      return FALSE;
    }
    
    try
    {
      $this->BeginTransaction();

      //try to delete the member first (cascade delete on T_CoordinatingGroupMember and T_MemberRole only)
      $sSQL = "DELETE FROM T_Member WHERE MemberID = " . $this->m_aData[self::PROPERTY_ID] . ";";
      $this->RunSQL($sSQL);
      //delete the group, if exists
      if ($this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID] > 0)
      {
        $sSQL = "DELETE FROM T_CoordinatingGroup WHERE CoordinatingGroupID = " . $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID] . ";";
        $this->RunSQL($sSQL);
      }
    
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
  
  public function GetPaymentMethods()
  {
    global $g_oMemberSession;

    if ( !$this->CheckAccess() )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }

    $sSQL =  " SELECT PM.PaymentMethodKeyID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PAYMENT_METHODS, 'sPaymentMethod');
    $sSQL .= " FROM T_PaymentMethod PM " . $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PAYMENT_METHODS);
    $sSQL .= " ORDER BY PM_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetchAllKeyPair(); 
  }
  
  public function GetCachiers()
  {
    global $g_oMemberSession;

    if ( !$this->CheckAccess() )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }

    $sSQL =   " SELECT PL.PickupLocationKeyID, " . 
                 $this->ConcatStringsSelect(Consts::PERMISSION_AREA_PICKUP_LOCATIONS, 'sPickupLocation') .
                " FROM T_PickupLocation PL " . 
                $this->ConcatStringsJoin(Consts::PERMISSION_AREA_PICKUP_LOCATIONS) .
               " WHERE (PL.bDisabled = 0 OR IfNull(PL.mCachier,0) <> 0) " .
     " ORDER BY PL.bDisabled, PL.nRotationOrder, PL_S.sString; ";

    $this->RunSQL( $sSQL );

    return $this->fetchAllKeyPair(); 
    
  }
  
  protected function PreserveFormData()
  {
    $this->m_aData[self::PROPERTY_LOGIN_NAME] = $this->m_aOriginalData[self::PROPERTY_LOGIN_NAME];
    $this->m_aData[self::PROPERTY_JOINED_ON] = $this->m_aOriginalData[self::PROPERTY_JOINED_ON];
    $this->m_aData[self::PROPERTY_COORDINATING_GROUP_ID] = $this->m_aOriginalData[self::PROPERTY_COORDINATING_GROUP_ID];
    $this->m_aData[self::PROPERTY_CAN_MODIFY] =  $this->m_aOriginalData[self::PROPERTY_CAN_MODIFY];
    $this->m_aData[self::PROPERTY_MAX_ORDER] =  $this->m_aOriginalData[self::PROPERTY_MAX_ORDER];
  }
  
  public function PreserveFieldsForProfileScreen()
  {
    $this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID] = $this->m_aOriginalData[self::PROPERTY_PAYMENT_METHOD_ID];
    $this->m_aData[self::PROPERTY_PAYMENT_METHOD_NAME] = $this->m_aOriginalData[self::PROPERTY_PAYMENT_METHOD_NAME];
    $this->m_aData[self::PROPERTY_BALANCE] = $this->m_aOriginalData[self::PROPERTY_BALANCE];
    $this->m_aData[self::PROPERTY_BALANCE_INVESTED] = $this->m_aOriginalData[self::PROPERTY_BALANCE_INVESTED];
    $this->m_aData[self::PROPERTY_BALANCE_HELD] = $this->m_aOriginalData[self::PROPERTY_BALANCE_HELD];
    $this->m_aData[self::PROPERTY_PERCENT_OVER_BALANCE] = $this->m_aOriginalData[self::PROPERTY_PERCENT_OVER_BALANCE];
  }
  
  protected function CheckMemberPermissions()
  {
    $bHasMemberRole = FALSE;
    $nCount = 0;
    $this->m_aData[self::PROPERTY_IS_SYS_ADMIN] = FALSE;
    //get whether admin
    $sSQL = "SELECT RoleKeyID FROM T_MemberRole Where MemberID = ?;";    
    $this->RunSQLWithParams($sSQL, array($this->m_aData[self::PROPERTY_ID]));
    
    $recRole = $this->fetch();
    
    while($recRole)
    {
      if ($recRole["RoleKeyID"] == Consts::ROLE_SYSTEM_ADMIN)
        $this->m_aData[self::PROPERTY_IS_SYS_ADMIN] = TRUE;
      else if ($recRole["RoleKeyID"] == Consts::ROLE_MEMBER)
        $bHasMemberRole = TRUE;
      $recRole = $this->fetch();
      $nCount++;
    }
    
    $this->m_aData[self::PROPERTY_HAS_NO_PERMISSIONS] = ($nCount == 0);
    $this->m_aData[self::PROPERTY_IS_REGULAR_MEMBER] = ($bHasMemberRole && $nCount == 1);
    
    return $this->m_aData[self::PROPERTY_IS_SYS_ADMIN];
  }
  
  public function Validate()
  {
    global $g_oError;
    
    $bValid = TRUE;
    
    if ($this->m_aData[self::PROPERTY_MEMBER_NAME] == NULL)
    {
      $g_oError->AddError(sprintf('יש להזין %s', 'שם'));
      $bValid = FALSE;
    }
    else
    {
      if (stripos($this->m_aData[self::PROPERTY_MEMBER_NAME], Consts::PAID_BY_REDUCTION_SIGN) !== FALSE)
      {
        $g_oError->AddError(sprintf('תו לא חוקי %s בשדה %s', Consts::PAID_BY_REDUCTION_SIGN, 
            'שם'));
        $bValid = FALSE;
      }
      elseif ($bValid && !$this->IsUniqueName()) //check uniqueness only if otherwise valid
      {
        $g_oError->AddError('שם החבר/ה שהוזן כבר נמצא בשימוש במערכת');
        $bValid = FALSE;
      }
    }
    
    //must insert a unique login name
    if ($this->m_aData[self::PROPERTY_ID] == 0) 
    {
      if ($this->m_aData[self::PROPERTY_LOGIN_NAME] == NULL)
      {
        $g_oError->AddError(sprintf('יש להזין %s', 'שם כניסה'));
        $bValid = FALSE;
      }
      elseif ($bValid && !$this->IsUniqueLoginName()) //check uniqueness only if otherwise valid
      {
        $g_oError->AddError('שם הכניסה שהוזן כבר נמצא בשימוש במערכת');
        $bValid = FALSE;
      }
    }
    
    
    
    if ($this->m_aData[self::PROPERTY_ID] == 0 && $this->m_aData[self::PROPERTY_NEW_PASSWORD] == NULL)
    {
      $g_oError->AddError(sprintf('יש להזין %s', 'סיסמא חדשה'));
      $bValid = FALSE;
    }
    elseif ($this->m_aData[self::PROPERTY_NEW_PASSWORD] != NULL) 
    {
      if (strlen($this->m_aData[self::PROPERTY_NEW_PASSWORD]) < PASSWORD_MIN_LENGTH )
      {
        $g_oError->AddError(sprintf('יש להזין לפחות %2$d תווים עבור %1$s', 'סיסמא חדשה', PASSWORD_MIN_LENGTH));
        $bValid = FALSE;
      }
      if ($this->m_aData[self::PROPERTY_VERIFY_PASSWORD] == NULL || 
              $this->m_aData[self::PROPERTY_VERIFY_PASSWORD] != $this->m_aData[self::PROPERTY_NEW_PASSWORD])
      {
        $g_oError->AddError('אימות סיסמא אינו מכיל את אותו ערך שהוזן עבור סיסמא חדשה');
        $bValid = FALSE;
      }
    }
    
    if ($this->m_aData[self::PROPERTY_PAYMENT_METHOD_ID] == 0)
    {
      $g_oError->AddError(sprintf('יש להזין %s', 'שיטת תשלום'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_EMAIL] == NULL)
    {
      $g_oError->AddError(sprintf('יש להזין %s', 'כתובת דוא&quot;ל'));
      $bValid = FALSE;
    }
    elseif (!preg_match(Consts::ACCEPTED_EMAIL_REGULAR_EXPRESSION, $this->m_aData[self::PROPERTY_EMAIL]))
    {
      $g_oError->AddError(sprintf('ערך לא תקין עבור %s', 'כתובת דוא&quot;ל'));
      $bValid = FALSE;
    }
    elseif (ENFORCE_UNIQUE_MAIN_EMAIL && !$this->IsUniqueEMail())
    {
      $g_oError->AddError('כתובת הדוא&quot;ל הראשונה שהוזנה כבר נמצאת בשימוש במערכת');
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_EMAIL2] != NULL 
        && !preg_match( Consts::ACCEPTED_EMAIL_REGULAR_EXPRESSION, $this->m_aData[self::PROPERTY_EMAIL2]))
    {
      $g_oError->AddError(sprintf('ערך לא תקין עבור %s', 'כתובת דוא&quot;ל 2'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_EMAIL3] != NULL 
        && !preg_match( Consts::ACCEPTED_EMAIL_REGULAR_EXPRESSION, $this->m_aData[self::PROPERTY_EMAIL3]))
    {
      $g_oError->AddError(sprintf('ערך לא תקין עבור %s', 'כתובת דוא&quot;ל 3'));
      $bValid = FALSE;
    }
    
    if ($this->m_aData[self::PROPERTY_EMAIL4] != NULL 
        && !preg_match( Consts::ACCEPTED_EMAIL_REGULAR_EXPRESSION, $this->m_aData[self::PROPERTY_EMAIL4]))
    {
      $g_oError->AddError(sprintf('ערך לא תקין עבור %s', 'כתובת דוא&quot;ל 4'));
      $bValid = FALSE;
    }
    
    return $bValid;
  }
  
  protected function IsUniqueLoginName()
  {
    $sSQL = "SELECT MemberID FROM T_Member WHERE sLoginName = :LoginName;";
    $this->RunSQLWithParams($sSQL,  array("LoginName" => $this->m_aData[self::PROPERTY_LOGIN_NAME]));
    if ($this->fetch())
      return FALSE;
    return TRUE;
  }
  
  protected function IsUniqueName()
  {
    $sSQL = "SELECT MemberID FROM T_Member WHERE sName = :Name ";
    if ($this->m_aData[self::PROPERTY_ID] > 0)
      $sSQL .= " AND MemberID <> " . $this->m_aData[self::PROPERTY_ID];
    $sSQL .= ";";
    $this->RunSQLWithParams($sSQL, array("Name" => $this->m_aData[self::PROPERTY_MEMBER_NAME]));
    if ($this->fetch())
      return FALSE;
    return TRUE;
  }
  
  protected function IsUniqueEMail()
  {
    $sSQL = "SELECT MemberID FROM T_Member WHERE sEMail = :EMail ";
    if ($this->m_aData[self::PROPERTY_ID] > 0)
      $sSQL .= " AND MemberID <> " . $this->m_aData[self::PROPERTY_ID];
    $sSQL .= ";";
    $this->RunSQLWithParams($sSQL, array("EMail" => $this->m_aData[self::PROPERTY_EMAIL]));
    if ($this->fetch())
      return FALSE;
    return TRUE;
  }
  
  //helps get max order for a member that may be the current one (in UserSession:GetMaxOrder) or not (in this class' GetMaxOrder)
  public static function CalculateMaxOrder($nPaymentMethodID, $mBalance, $fPercentOverBalance)
  {
    if ($nPaymentMethodID == Consts::PAYMENT_METHOD_AT_PICKUP)
      return NULL;
    if ($nPaymentMethodID == Consts::PAYMENT_METHOD_PLUS_EXTRA &&
         $fPercentOverBalance > 0  )
      $mBalance = $mBalance + ($mBalance * ($fPercentOverBalance/100));

    return $mBalance;
  }
}

?>
