<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//coordinate specific records permissions: used primarily in coop orders, producers and pickup locations
//sub areas also derive their permissions from these three, sometimes in combination
class Coordinate extends SQLBase {
  const POST_ACTION_CHANGE_GROUP = 10;
  const POST_ACTION_SET_MEMBER_AS_COORDINATOR = 11; 
  const POST_ACTION_REMOVE_GROUP = 12;
  
  const PERMISSION_GROUPS_EXTENDED = 100;
  
  const PROPERTY_RECORD_ID = "RecordID";
  const PROPERTY_RECORD_NAME = "RecordName";
  const PROPERTY_GROUP_ID = "GroupID";
  const PROPERTY_PERMISSION_AREA = "PermissionArea";
  const PROPERTY_GROUP_NAME = "GroupName";
  const PROPERTY_MEMBERS = "Members";
  const PROPERTY_SKIP_RECORD_GROUP_CHECK = "SkipRecordGroupCheck";
  const PROPERTY_IS_PRIVATE_GROUP = "IsPrivateGroup";
  const PROPERTY_IS_ORIGINAL_PRIVATE_GROUP = "IsOriginalPrivateGroup";
  const PROPERTY_PRIVATE_GROUP_MEMBERID = "PrivateGroupMemberID";
  const PROPERTY_PRIVATE_GROUP_MEMBER_NAME = "PrivateGroupMemberName";
  const PROPERTY_ORIGINAL_GROUP_ID = "OriginalGroupID";
  const PROPERTY_HAS_UNAUTHORIZED_MEMBERS = "HasUnauthorizedMembers";
  
  protected $m_nRecordGroup = 0; 
  protected $m_nDataRetreivedForGroup = 0;
  
  protected $m_aUnauthorizedMembers = NULL;

  public function __construct()
  {
    $this->m_aData = array(
      self::PROPERTY_RECORD_ID => 0, 
      self::PROPERTY_RECORD_NAME => NULL,
      self::PROPERTY_GROUP_ID => 0,
      self::PROPERTY_PERMISSION_AREA => 0,
      self::PROPERTY_GROUP_NAME => NULL,
      self::PROPERTY_MEMBERS => NULL,
      self::PROPERTY_IS_PRIVATE_GROUP => FALSE,
      self::PROPERTY_PRIVATE_GROUP_MEMBERID => 0,
      self::PROPERTY_PRIVATE_GROUP_MEMBER_NAME => NULL
      );

     $this->m_aOriginalData = $this->m_aData;
  }
  
  public function CopyBasicValuesFromOriginal()
  {
    $this->m_aData[self::PROPERTY_RECORD_ID] = $this->m_aOriginalData[self::PROPERTY_RECORD_ID];
    $this->m_aData[self::PROPERTY_PERMISSION_AREA] = $this->m_aOriginalData[self::PROPERTY_PERMISSION_AREA];
    $this->m_aData[self::PROPERTY_RECORD_NAME] = $this->m_aOriginalData[self::PROPERTY_RECORD_NAME];
  }
  
  public function __get( $name ) {
      switch ($name)
      {
        case self::PROPERTY_ORIGINAL_GROUP_ID:
          return $this->m_aOriginalData[self::PROPERTY_GROUP_ID];
        case self::PROPERTY_IS_ORIGINAL_PRIVATE_GROUP:
          return $this->m_aOriginalData[self::PROPERTY_IS_PRIVATE_GROUP];
        case self::PROPERTY_HAS_UNAUTHORIZED_MEMBERS:
          return (is_array($this->m_aUnauthorizedMembers) && count($this->m_aUnauthorizedMembers) > 0);
        default:
          return parent::__get($name);
      }
    }
    
  public function CheckGroupsExtendedPermission()
  {
    if ($this->HasPermission(self::PERMISSION_GROUPS_EXTENDED))
      return TRUE;
    
    return $this->AddPermissionBridge(self::PERMISSION_GROUPS_EXTENDED, Consts::PERMISSION_AREA_COORDINATING_GROUPS, Consts::PERMISSION_TYPE_MODIFY, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
  }

  //to support changing the group
  public function SkipRecordGroupCheck()
  {  
    $this->m_nRecordGroup = $this->m_aData[self::PROPERTY_GROUP_ID];
  }
  
 //returns the table's first row. Other rows are retreived by calling base::fetch()
  public function GetTable($bFromRecord)
  {
      global $g_oMemberSession;
      
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
          
      //check sufficient data
      if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
            $this->m_aData[self::PROPERTY_RECORD_ID] == 0 )
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
          if ($bFromRecord) $this->m_aOriginalData = $this->m_aData;
          return NULL;
      }
       
      //check permissions: must have both coord and coord set
      
      $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
              Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);
      
      if (!$bCoord || !$bCoordSet)
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          if ($bFromRecord) $this->m_aOriginalData = $this->m_aData;
          return NULL;
      }
      
      //get the record name
      $this->m_aData[self::PROPERTY_RECORD_NAME] = $this->GetKeyString($this->m_aData[self::PROPERTY_RECORD_ID]);
      
      //exit if a group was not supplied
      if ($this->m_aData[self::PROPERTY_GROUP_ID] == 0)
      {
        if ($bFromRecord) $this->m_aOriginalData = $this->m_aData;
        return NULL;
      }
      
      if (!$this->SetRecordGroupID(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_GROUP_ID], FALSE))
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED;
        if ($bFromRecord) $this->m_aOriginalData = $this->m_aData;
        return NULL;
      }      
      
      $this->GetGroupData();
      
      $this->ValidateGroupMembersForArea();

      //get the members, if a group was supplied
      $sSQL = " SELECT M.MemberID, M.sName, CGM.bContactPerson  " .
              " FROM T_Member M INNER JOIN T_CoordinatingGroupMember CGM " .
              " ON M.MemberID = CGM.MemberID " .
              " Where CGM.CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID];
      //exclude anauthorized members
      if ($this->m_aUnauthorizedMembers != NULL && count($this->m_aUnauthorizedMembers) > 0)
      {
        $sSQL .= " AND CGM.MemberID NOT IN (";
        foreach($this->m_aUnauthorizedMembers as $nMemberID)
        {
          $sSQL .= $nMemberID . ", ";
        }
        $sSQL .= "0 ) ";
      }
      
      $sSQL .= " Order By M.MemberID;"; //must be ordered by memberid, so TableDiff will get sorted arrays

      $this->RunSQL( $sSQL );
      
      $this->m_aData[self::PROPERTY_MEMBERS] =  $this->fetchAll();
      
      if ($bFromRecord) 
        $this->m_aOriginalData = $this->m_aData;
      else //anyways save loaded members for existing group
        $this->m_aOriginalData[self::PROPERTY_MEMBERS] = $this->m_aData[self::PROPERTY_MEMBERS];

      return $this->m_aData[self::PROPERTY_MEMBERS];
  }

  public function Save()
  {
    global $g_oMemberSession;
    global $g_oError;
    $bLoadOwnCoordinatingGroups = FALSE;

    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //check sufficient data
    if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
          $this->m_aData[self::PROPERTY_RECORD_ID] == 0 )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
    }

    //check basic permissions
    //check permissions

    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
       Consts::PERMISSION_SCOPE_BOTH, $this->m_aData[self::PROPERTY_GROUP_ID], TRUE);

    $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
            Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    if (!$bCoord || !$bCoordSet)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }

    //validate membership, name, if can change it
    if ($this->CheckGroupsExtendedPermission())
    {
      if (!is_array($this->m_aData[self::PROPERTY_MEMBERS]) || count($this->m_aData[self::PROPERTY_MEMBERS]) == 0)
      {
         $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_LIST_ITEM_SELECTED;
         return FALSE;
      }

      //data validations for creating new group: has name, and has members
      if ($this->m_aData[self::PROPERTY_GROUP_NAME] == NULL)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_REQUIRED_FIELD_MISSING;
        return FALSE;
      }
      $this->m_aData[self::PROPERTY_GROUP_NAME] = trim($this->m_aData[self::PROPERTY_GROUP_NAME]);
      if ($this->m_aData[self::PROPERTY_GROUP_NAME] == "")        
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_REQUIRED_FIELD_MISSING;
        return FALSE;
      }

      foreach($this->m_aData[self::PROPERTY_MEMBERS] as $aMember)
      {
         if ($aMember["MemberID"] == $g_oMemberSession->MemberID)
         {
           $bLoadOwnCoordinatingGroups = TRUE;
           break;
         }
      }

      //if only has group permissions, check that the curreent user did not remove hirself
      if ($this->GetPermissionScope(self::PERMISSION_COORD) == Consts::PERMISSION_SCOPE_GROUP_CODE && !$bLoadOwnCoordinatingGroups)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_CANT_REMOVE_OWN_PERMISSION;
        return FALSE;
      }
    }

    $sSQL = NULL;   

    $bUpdateRecordGroup = FALSE;

    //if original is a personal group
    if ( $this->m_aOriginalData[self::PROPERTY_IS_PRIVATE_GROUP] 
         &&    $this->m_aOriginalData[self::PROPERTY_GROUP_ID] == $this->m_aData[self::PROPERTY_GROUP_ID] )
      $this->m_aData[self::PROPERTY_GROUP_ID] = 0; //don't update it, but rather create a new group

    try
    {

      if ($this->m_aData[self::PROPERTY_GROUP_ID] == 0) //new group
      {
        //if new group, must have extended permissions
        if (!$this->CheckGroupsExtendedPermission())
        {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
        }

          $this->m_bUseClassConnection = TRUE;
          $this->BeginTransaction();

           //creating the group
           $sSQL = "INSERT INTO T_CoordinatingGroup (sCoordinatingGroup) VALUES (?);";
           $this->RunSQLWithParams($sSQL, array($this->m_aData[self::PROPERTY_GROUP_NAME]));
           $this->m_aData[self::PROPERTY_GROUP_ID] = $this->GetLastInsertedID();

           //adding the members
           foreach($this->m_aData[self::PROPERTY_MEMBERS] as $aMember)
           {
             $sSQL = "INSERT INTO T_CoordinatingGroupMember (CoordinatingGroupID, MemberID, bContactPerson) VALUES (?, ?, ?);";
             $this->RunSQLWithParams($sSQL, array(
                      $this->m_aData[self::PROPERTY_GROUP_ID],
                      $aMember["MemberID"],
                      $aMember["bContactPerson"]
                    )
                  );
            }
        }
        else //update existing group
        {
          $this->ValidateGroupMembersForArea();

          $this->BeginTransaction();

          if (is_array($this->m_aUnauthorizedMembers) && count($this->m_aUnauthorizedMembers) > 0)
          {
            //remove unauthorized members and notify on the removal
            if (!$this->RemoveUnautorized())
            {
               //no members left, so exit (message already issued)
               $this->RollbackTransaction(); //cancel transaction (no changes made)
               return FALSE;
            }
          }

          //change group members only if have extended permissions
          if ($this->CheckGroupsExtendedPermission())
          {
            //see if updating group name
            if ($this->m_aData[self::PROPERTY_GROUP_NAME] != $this->m_aOriginalData[self::PROPERTY_GROUP_NAME])
            {
              $sSQL = "UPDATE T_CoordinatingGroup SET sCoordinatingGroup = ? WHERE CoordinatingGroupID = ?;";
              $this->RunSQLWithParams($sSQL, array( $this->m_aData[self::PROPERTY_GROUP_NAME],
                                                    $this->m_aData[self::PROPERTY_GROUP_ID] ));
            }
            //check changes in members
            $oTableDiff = new TableDiff;
            $oTableDiff->NumericKeys = array("MemberID");
            $oTableDiff->Attributes = array("bContactPerson");
            $oTableDiff->ComputeTableDiff($this->m_aOriginalData[self::PROPERTY_MEMBERS], $this->m_aData[self::PROPERTY_MEMBERS]);

            foreach($oTableDiff->Removed as $aMember)
            {
              if ($aMember != NULL)
              {
                $sSQL = "DELETE FROM T_CoordinatingGroupMember WHERE CoordinatingGroupID = ? AND MemberID = ?;";
                $this->RunSQLWithParams($sSQL, array(
                          $this->m_aData[self::PROPERTY_GROUP_ID],
                          $aMember["MemberID"]
                        )
                      );
              }
            }

            foreach($oTableDiff->Changed as $aMember)
            {
              if ($aMember != NULL)
              {
                $sSQL = "UPDATE T_CoordinatingGroupMember SET bContactPerson = ? WHERE CoordinatingGroupID = ? AND MemberID = ?;";
                $this->RunSQLWithParams($sSQL, array(
                          $aMember["bContactPerson"],
                          $this->m_aData[self::PROPERTY_GROUP_ID],
                          $aMember["MemberID"]
                        )
                      );
               }
            }

            foreach($oTableDiff->Added as $aMember)
            {
              if ($aMember != NULL)
              {
                $sSQL = "INSERT INTO T_CoordinatingGroupMember (CoordinatingGroupID, MemberID, bContactPerson) VALUES (?, ?, ?);";
                $this->RunSQLWithParams($sSQL, array(
                          $this->m_aData[self::PROPERTY_GROUP_ID],
                          $aMember["MemberID"],
                          $aMember["bContactPerson"]
                        )
                      );
              }
            }
          }
        }
        //update record group
        $sTable = NULL;
        $sRecordIdentifier = NULL;

        $oArea = new PermissionArea($this->m_aData[self::PROPERTY_PERMISSION_AREA]);

        if ($oArea->TableName != NULL)
        { 
          $sSQL = "UPDATE " . $oArea->TableName . " SET CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID] . " WHERE " . 
                  $oArea->TablePrimaryKey . " = " . $this->m_aData[self::PROPERTY_RECORD_ID];

          $this->RunSQL($sSQL);
        }

        $this->CommitTransaction();
        if ($this->m_bUseClassConnection)
        {
          $this->CloseConnection();
          $this->m_bUseClassConnection = FALSE;
        }
      }
      catch(Exception $e)
      {
        $this->RollbackTransaction();
        if ($this->m_bUseClassConnection)
        {
          $this->CloseConnection();
          $this->m_bUseClassConnection = FALSE;
        }
        throw $e;
      }

      //reload Own Coordinating Groups
      if ($bLoadOwnCoordinatingGroups)
        $g_oMemberSession->LoadCoordinatingGroups();

      $this->m_aOriginalData = $this->m_aData;

      return TRUE;
  }
  
  public function DeleteGroup()
  {
    global $g_oMemberSession;
      
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //check sufficient data
    if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
          $this->m_aData[self::PROPERTY_GROUP_ID] == 0
     )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
    }
    
    //check basic permissions
    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
    $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
            Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    if (!$bCoord || !$bCoordSet)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }

    //if is a personal group, can't delete it
    if ( $this->m_aData[self::PROPERTY_IS_PRIVATE_GROUP] )
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
      return FALSE;
    }
    
     if (!$this->CheckGroupsExtendedPermission())
     {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
     }
    
    //everything works in cascading relationship, so just delete the group (members deleted, other foreign keys set to null)
    $sSQL = "DELETE FROM T_CoordinatingGroup WHERE CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID];
    
    $this->RunSQL($sSQL);
    
    //reset values. If an unrelated group was deleted, return to original group, otherwise reset all values to 'no coordination' state
    if ($this->m_aData[self::PROPERTY_GROUP_ID] == $this->m_aOriginalData[self::PROPERTY_GROUP_ID])
      $this->ResetData();
    else //return to original values
      $this->m_aData = $this->m_aOriginalData;
    
    return TRUE;
    
  }
  
  public function RemoveGroup()
  {
    global $g_oMemberSession;
      
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //check sufficient data
    if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
          $this->m_aOriginalData[self::PROPERTY_RECORD_ID] == 0 ||
          $this->m_aOriginalData[self::PROPERTY_GROUP_ID] == 0
     )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return FALSE;
    }

    //check permissions
    //this operation requires coop-level permission
    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_COOP_CODE, 0, FALSE); //must have coop scope permissions
      
    $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
            Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    if (!$bCoord || !$bCoordSet)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    $oArea = new PermissionArea($this->m_aData[self::PROPERTY_PERMISSION_AREA]);
    
    if ($oArea->TableName == NULL)
    {
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_PERMISSION_AREA_NOT_LOADED;
      return FALSE;
    }
    
    $sSQL = 'UPDATE ' .  $oArea->TableName .   
            ' SET CoordinatingGroupID = NULL WHERE  ' . $oArea->TablePrimaryKey . ' = ' . $this->m_aOriginalData[self::PROPERTY_RECORD_ID] . ';';
    
    $this->RunSQL($sSQL);
    
    $this->ResetData();
    
    return TRUE;
    
  }
  
  
  public function SetMemberAsCoordinator($nNewCoordinator)
  {
    global $g_oMemberSession;
      
      $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;
          
      //check sufficient data
      if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
            $this->m_aOriginalData[self::PROPERTY_RECORD_ID] == 0 ||
            $nNewCoordinator == 0 )
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
          return FALSE;
      }
      
      //check permissions
      $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
      $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
              Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

      if (!$bCoord || !$bCoordSet)
      {
          $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
          return FALSE;
      }
      
      //get member private group
      $sSQL = 'SELECT CGM.CoordinatingGroupID FROM T_CoordinatingGroupMember CGM INNER JOIN T_CoordinatingGroup CG ' .
      'ON CG.CoordinatingGroupID = CGM.CoordinatingGroupID AND CG.sCoordinatingGroup IS NULL WHERE CGM.MemberID = ' . $nNewCoordinator . ';';
      
      $this->RunSQL($sSQL);
      
      $rec = $this->fetch();
      if (!$rec)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_LOAD_RECORD_FAILED;
        return FALSE;
      }
      
      //load permission area data
      $oArea = new PermissionArea($this->m_aData[self::PROPERTY_PERMISSION_AREA]);
    
      if ($oArea->TableName == NULL)
      {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_PERMISSION_AREA_NOT_LOADED;
        return FALSE;
      }

      //update record
      $sSQL = 'UPDATE ' .  $oArea->TableName .   
              ' SET CoordinatingGroupID = ' . $rec["CoordinatingGroupID"]  . 
              ' WHERE  ' . $oArea->TablePrimaryKey . ' = ' . $this->m_aOriginalData[self::PROPERTY_RECORD_ID] . ';';


      $this->RunSQL($sSQL);
           
      $this->m_aData[self::PROPERTY_GROUP_ID] = $rec["CoordinatingGroupID"];
      
      $this->m_aOriginalData = $this->m_aData;
      
      return TRUE;
    
  }
  
  //get all members that have permissions to coordinate this record
  public function GetNonMembers()
  {
    
    global $g_oMemberSession;
      
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //check sufficient data
    if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 ||
          $this->m_aData[self::PROPERTY_RECORD_ID] == 0 )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return NULL;
    }

    //check basic permissions
    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
    $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
            Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    if (!$bCoord || !$bCoordSet)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    $sSQL =   " SELECT DISTINCT M.MemberID, M.sName  " .
              " FROM T_Member M INNER JOIN T_MemberRole MR " .
              " ON MR.MemberID = M.MemberId " .
              " INNER JOIN T_RolePermission RP " .
              " ON RP.RoleKeyID = MR.RoleKeyID " .
              " AND RP.PermissionAreaKeyID = " . $this->m_aData[self::PROPERTY_PERMISSION_AREA] .
              " AND RP.PermissionTypeKeyID =  " . Consts::PERMISSION_TYPE_COORD . 
              " WHERE M.bDisabled = 0 ";
    if ($this->m_aData[self::PROPERTY_GROUP_ID] > 0)
    {
      $sSQL .=  " AND 0 = (SELECT Count(CGM.MemberID) FROM T_CoordinatingGroupMember CGM WHERE CGM.MemberID = M.MemberID AND CoordinatingGroupID = " 
                . $this->m_aData[self::PROPERTY_GROUP_ID] . " ) ";
    }
    
    $sSQL .=  " Order By M.MemberID;"; //must be ordered by memberid, so TableDiff will get sorted arrays

    $this->RunSQL( $sSQL );

    return $this->fetchAll();

  }
  
  public function AddMember($aArr)
  {
    $this->m_aData[self::PROPERTY_MEMBERS][] = $aArr;
  }
  
  public function ResetGroupName($sGroup)
  {
    $this->m_aData[self::PROPERTY_GROUP_NAME] = $sGroup;
    $this->m_aOriginalData[self::PROPERTY_GROUP_NAME] = $sGroup;
  }
  
  public function ValidateRecordGroup()
  {
     //check the group id against record
    return ($this->GetRecordGroup() == $this->m_aData[self::PROPERTY_GROUP_ID]);
  }
  
  protected function ValidateGroupMembersForArea()
  {
    if ($this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0
            || $this->m_aData[self::PROPERTY_GROUP_ID] == 0)
      return;
    
    //get the group members that do not have minimal permission to this area    
    $sSQL = " SELECT M.sName, M.MemberID fROM T_CoordinatingGroupMember CGM " .
            " INNER JOIN T_Member M " .
            " ON CGM.CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID] .
            " AND CGM.MemberID = M.MemberID " .
            " WHERE M.bDisabled = 1 OR 0 = (SELECT Count(*)  FROM T_MemberRole MR " .
                        " INNER JOIN T_RolePermission RP " .
                        " ON RP.RoleKeyID = MR.RoleKeyID AND RP.PermissionAreaKeyID = " . $this->m_aData[self::PROPERTY_PERMISSION_AREA] .
                        " AND RP.PermissionTypeKeyID = " . Consts::PERMISSION_TYPE_COORD .
                        " WHERE MR.MemberID = M.MemberID );";
    $this->RunSQL($sSQL);
    $this->m_aUnauthorizedMembers = $this->fetchAllKeyPair();
  }
  
  protected function ResetData()
  {
    $this->m_aData[self::PROPERTY_GROUP_ID] = 0;
    $this->m_aData[self::PROPERTY_GROUP_NAME] = NULL;
    $this->m_aData[self::PROPERTY_MEMBERS] = NULL;
    $this->m_aData[self::PROPERTY_IS_PRIVATE_GROUP] = FALSE;
    $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBERID] = NULL;
    $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBER_NAME] = NULL;


    $this->m_aOriginalData = $this->m_aData;
  }
  
  
  
  protected function GetRecordGroup()
  {
    $sTable = NULL;
    $sRecordIdentifier = NULL;
    
    if ($this->m_nRecordGroup > 0)
      return $this->m_nRecordGroup;
    
    $oArea = new PermissionArea($this->m_aData[self::PROPERTY_PERMISSION_AREA]);
    
    if ($oArea->TableName == NULL)
      return 0;
    
    $sSQL = "SELECT T1.CoordinatingGroupID " .
            " FROM " . $oArea->TableName . 
            " T1 WHERE " . $oArea->TablePrimaryKey . " = " . $this->m_aData[self::PROPERTY_RECORD_ID] . ";";
    
    unset($oArea);
    
    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!$rec)
      return 0;
    
    $this->m_nRecordGroup = intval($rec["CoordinatingGroupID"]);
    
    return $this->m_nRecordGroup;
  }
  
   protected function GetGroupData()
  {      
    if($this->m_nDataRetreivedForGroup == $this->m_aData[self::PROPERTY_GROUP_ID] && $this->m_nDataRetreivedForGroup > 0)
      return;
    
    //init values
    $this->m_aData[self::PROPERTY_IS_PRIVATE_GROUP] = FALSE;
    $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBERID] = 0;
    $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBER_NAME] = NULL;
    $this->m_aData[self::PROPERTY_GROUP_NAME] = NULL;
    
    if ($this->m_aData[self::PROPERTY_GROUP_ID] == 0)
      return;
    
    $sSQL = "SELECT CG.sCoordinatingGroup " .
            " FROM T_CoordinatingGroup CG WHERE CG.CoordinatingGroupID = " . 
            $this->m_aData[self::PROPERTY_GROUP_ID] . ";";
       
    $this->RunSQL( $sSQL );

    $rec = $this->fetch();
    
    if (!$rec)
      return;

    if ($rec["sCoordinatingGroup"])
    {
      $this->m_aData[self::PROPERTY_GROUP_NAME] = $rec["sCoordinatingGroup"];
    }
    else //get member details
    {
      $sSQL = "SELECT CGM.MemberID, M.sName FROM T_CoordinatingGroupMember CGM INNER JOIN T_Member M ON CGM.MemberID = M.MemberID " .
              " WHERE CGM.CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID];   
    
      $this->RunSQL( $sSQL );

      $rec = $this->fetch();
      if ($rec != NULL && $rec["MemberID"])
      {
        $this->m_aData[self::PROPERTY_IS_PRIVATE_GROUP] = TRUE;
        $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBERID] = intval($rec["MemberID"]);
        $this->m_aData[self::PROPERTY_PRIVATE_GROUP_MEMBER_NAME] = $rec["sName"];
      }
    }
    
    $this->m_nDataRetreivedForGroup = $this->m_aData[self::PROPERTY_GROUP_ID];
  }
  
  public function GetGroupList()
  {
    global $g_oMemberSession;
      
    $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NONE;

    //check sufficient data
    if (  $this->m_aData[self::PROPERTY_PERMISSION_AREA] == 0 )
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED;
        return NULL;
    }

    //check permissions
    $bCoord = $this->AddPermissionBridge(self::PERMISSION_COORD, $this->m_aData[self::PROPERTY_PERMISSION_AREA], Consts::PERMISSION_TYPE_COORD, 
         Consts::PERMISSION_SCOPE_BOTH, 0, TRUE);
      
    $bCoordSet = $this->AddPermissionBridge(self::PERMISSION_COORD_SET, $this->m_aData[self::PROPERTY_PERMISSION_AREA], 
            Consts::PERMISSION_TYPE_COORD_SET, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE);

    if (!$bCoord || !$bCoordSet)
    {
        $this->m_nLastOperationStatus = parent::OPERATION_STATUS_NO_PERMISSION;
        return FALSE;
    }
    
    $nScope = $this->GetPermissionScope(self::PERMISSION_COORD);
    
    $sSQL = " SELECT CG.sCoordinatingGroup, CG.CoordinatingGroupID FROM T_CoordinatingGroup CG ";
    if ($nScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
      $sSQL .= " INNER JOIN T_CoordinatingGroupMember CGM ON CGM.CoordinatingGroupID = CG.CoordinatingGroupID WHERE CGM.MemberID = ? AND ";
    else
      $sSQL .= " WHERE ";
    $sSQL .= " CG.sCoordinatingGroup IS NOT NULL ";
    
    $sSQL .= " ORDER BY CG.CoordinatingGroupID;";
    
    if ($nScope == Consts::PERMISSION_SCOPE_GROUP_CODE)
      $this->RunSQLWithParams ($sSQL, array($g_oMemberSession->MemberID));
    else
      $this->RunSQL($sSQL);
    
    return $this->fetchAllKeyPair();
    
  }
  
  public function GetUnauthorizedMemberNames()
  {
    $sUnauthorizedMembers = '';
    if (!is_array($this->m_aUnauthorizedMembers) || count($this->m_aUnauthorizedMembers) == 0)
      return $sUnauthorizedMembers;
    
    $bIsFirstMember = TRUE;
    foreach($this->m_aUnauthorizedMembers as $MName => $MID)
    {
      if (!$bIsFirstMember)
         $sUnauthorizedMembers .= ', ';
      $sUnauthorizedMembers .= $MName;
      $bIsFirstMember = FALSE;
    }
    
    return $sUnauthorizedMembers;
  }
  
  protected function RemoveUnautorized()
  {
    global $g_oError;

    $sUnauthorizedMembers = '';
    $sUnauthorizedMemberIDs = '';
    
    $bIsFirstMember = TRUE;
    foreach($this->m_aUnauthorizedMembers as $MName => $MID)
    {
      if (!$bIsFirstMember)
      {
         $sUnauthorizedMembers .= ', ';
         $sUnauthorizedMemberIDs .= ', ';
      }
      $sUnauthorizedMembers .= $MName;
      $sUnauthorizedMemberIDs .= $MID;
      $bIsFirstMember = FALSE;
      
      //remove first from members array
      $i = 0;
      foreach($this->m_aData[self::PROPERTY_MEMBERS] as $aMember)
      {
         if ($aMember["MemberID"] == $MID)
         {
           unset($this->m_aData[self::PROPERTY_MEMBERS][$i]);
           break;
         }
         $i++;
      }
      //have to remove also from original array, because of TableDiff comparison
      foreach($this->m_aOriginalData[self::PROPERTY_MEMBERS] as $aMember)
      {
         if ($aMember["MemberID"] == $MID)
         {
           unset($this->m_aOriginalData[self::PROPERTY_MEMBERS][$i]);
           break;
         }
         $i++;
      }
    }
    
    if (count($this->m_aData[self::PROPERTY_MEMBERS]) == 0)
    {
      $g_oError->AddError(sprintf('<!$ALL_MEMBERS_LEFT_ARE_UNAUTHORIZED$!>', $sUnauthorizedMembers));
      return FALSE;
    }
    
    $sSQL = "DELETE FROM T_CoordinatingGroupMember WHERE CoordinatingGroupID = " . $this->m_aData[self::PROPERTY_GROUP_ID] .
            " AND MemberID IN (" . $sUnauthorizedMemberIDs . ");";
    $this->RunSQL($sSQL);
    
    $g_oError->AddError(sprintf('<!$SOME_UNAUTHORIZED_MEMBERS_REMOVED_FROM_COORDINATING$!>', $sUnauthorizedMembers));
    return TRUE;
  }

}

?>
