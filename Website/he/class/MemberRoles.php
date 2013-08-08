<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//faciliate member roles page (editable for admins only)
class MemberRoles extends SQLBase {
  
  const PROPERTY_MEMBER_NAME = "Name";
   
  const POST_ACTION_REMOVE_ROLE = 10;
  const POST_ACTION_ADD_ROLE = 11;
   
  public function __construct()
  {
    $this->m_aData = array( self::PROPERTY_ID => 0, 
                            self::PROPERTY_MEMBER_NAME => NULL);
  }
  
  public function CheckAccess()
  {
    $bCoord = FALSE;
    
    if (ALLOW_ROLES_MODIFICATIONS) //don't build coordination permission bridge if this settings.php flag is raised
    {
      $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBER_ROLES, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
    }
    
    $bView = $this->AddPermissionBridge(self::PERMISSION_VIEW, Consts::PERMISSION_AREA_MEMBER_ROLES, Consts::PERMISSION_TYPE_VIEW, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
    
    return ($bView || $bCoord);
  }
  
  public function GetTable()
  {
    
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
    $sSQL = "SELECT sName FROM T_Member WHERE MemberID = " . $this->m_aData[self::PROPERTY_ID];
    $this->RunSQL( $sSQL );

    $recMember = $this->fetch();
    if (!$recMember)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
      return NULL;
    }
    $this->m_aData[self::PROPERTY_MEMBER_NAME] = $recMember["sName"];
    
    $sSQL = " SELECT MR.RoleKeyID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ROLES, 'sRole') .
            " FROM T_MemberRole MR " .
            " INNER JOIN T_Role R ON R.RoleKeyID = MR.RoleKeyID " . 
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ROLES) .
            " WHERE MR.MemberID = " . $this->m_aData[self::PROPERTY_ID] . 
            " ORDER BY R_S.sString;";
    
    $this->RunSQL( $sSQL );

    return $this->fetch();
  }
  
  public function GetOtherRoles()
  {
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return NULL;
    }
    
    if (!ALLOW_ROLES_MODIFICATIONS)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBER_ROLES, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    $sSQL = " SELECT R.RoleKeyID, " . $this->ConcatStringsSelect(Consts::PERMISSION_AREA_ROLES, 'sRole') . 
            " FROM T_Role R  " .
            $this->ConcatStringsJoin(Consts::PERMISSION_AREA_ROLES) .
            " WHERE 0 = (SELECT Count(*) FROM T_MemberRole MR WHERE MR.RoleKeyID = R.RoleKeyID AND MR.MemberID = " . 
                    $this->m_aData[self::PROPERTY_ID] .
            ") ORDER BY R_S.sString;";

    $this->m_bUseSecondSqlPreparedStmt = TRUE;
    $this->RunSQL( $sSQL );

    $arrResult = $this->fetchAllKeyPair();
    $this->m_bUseSecondSqlPreparedStmt = FALSE;
    
    return $arrResult;
  }
  
  public function RemoveRole($nRoleID)
  {
    global $g_oMemberSession;
    global $g_oError;
    
    //block modifications (even with postback manipulation) if this flag was raised
    if (!ALLOW_ROLES_MODIFICATIONS)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBER_ROLES, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 || $nRoleID <= 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //when removing system admin roles some conditions should be met
    if ($nRoleID == Consts::ROLE_SYSTEM_ADMIN)
    {
      //user must be system admin
      if (!$g_oMemberSession->IsSysAdmin)
      {
        $g_oError->AddError('לא ניתן לערוך רשומה של מנהל/ת מערכת ללא הרשאת ניהול מערכת');
        return FALSE;
      }
      //admin cannot remove hir own role (this addition insures there will always be at least one admin)
      if ($g_oMemberSession->MemberID == $this->m_aData[self::PROPERTY_ID])
      {
        $g_oError->AddError('לא ניתן למחוק את הרשאת ניהול המערכת של עצמך.');
        return FALSE;
      }
    }
    
    $sSQL = " DELETE FROM T_MemberRole WHERE MemberID = " . $this->m_aData[self::PROPERTY_ID] .
            " AND RoleKeyID = " . $nRoleID . ";";
    
    $this->RunSQL( $sSQL );
    
    return TRUE;
  }
  
  public function AddRole($nRoleID)
  {
    global $g_oMemberSession;
    global $g_oError;
    
    //block modifications (even with postback manipulation) if this flag was raised
    if (!ALLOW_ROLES_MODIFICATIONS)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return NULL;
    }
    
    if (!$this->AddPermissionBridge(self::PERMISSION_COORD, Consts::PERMISSION_AREA_MEMBER_ROLES, Consts::PERMISSION_TYPE_MODIFY, 
       Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
    if ( $this->m_aData[self::PROPERTY_ID] <=0 || $nRoleID <= 0)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
      return FALSE;
    }
    
    //when adding system admin roles, must be system admin
    if ($nRoleID == Consts::ROLE_SYSTEM_ADMIN && !$g_oMemberSession->IsSysAdmin)
    {
      $g_oError->AddError('לא ניתן לערוך רשומה של מנהל/ת מערכת ללא הרשאת ניהול מערכת');
      return FALSE;
    }
    
    $sSQL = " INSERT INTO T_MemberRole(MemberID, RoleKeyID) VALUES(" . $this->m_aData[self::PROPERTY_ID] .
            " , " . $nRoleID . ");";
    
    $this->RunSQL( $sSQL );
    
    return TRUE;
  }
}

?>
