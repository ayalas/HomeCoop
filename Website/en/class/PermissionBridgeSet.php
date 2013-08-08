<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//Faciliates a multi-layers permission checks through an array of PermissionBridge instances.
//Each check result is stored separately for later use. Scope and whether there is a permission can be retrieved by
//HasPermission and GetPermissionScope.
//Record specific permissions can be checked separately after the initial check, by SetRecordGroupID.
//Some functions, such as HasAnyPermission and HasPermissions analize the entire array.
//Results can also be cloned through the CopyPermission method, 
//allowing a "higher" permission to count as a few "lower" ones.
class PermissionBridgeSet {
  
  const PROPERTY_PERMISSION_BRIDGES = "PermissionBridges";
  
  protected $m_aPermissionBridge = array();
  
  public function HasPermission($nID)
  {
    return array_key_exists($nID, $this->m_aPermissionBridge) && isset($this->m_aPermissionBridge[$nID]);
  }
  
  public function HasPermissions(&$arrIDs)
  {
    foreach($arrIDs as $id)
    {
      if ($this->HasPermission($id))
        return TRUE;
    }
    
    return FALSE;
  }
  
  public function CopyPermission($nSourceID, $nDestID)
  {
    $this->m_aPermissionBridge[$nDestID] = $this->m_aPermissionBridge[$nSourceID];
  }
  
  public function HasAnyPermission()
  {
    return (count($this->m_aPermissionBridge) > 0);
  }
  
  public function GetPermissionScope($nID)
  {
    if (!$this->HasPermission($nID))
      return 0;
    
    return $this->m_aPermissionBridge[$nID]->ResultScope;
  }
  
  //main permission check method
  //$nID - any id (normally numeric, for better performances) that will identify the permission results for later use
  //$nScopes - must be the scopes that are to check
  //$nRecordGroupID - can be 0 if not checking record permission
  public function DefinePermissionBridge($nID, $nArea, $nType, $nScopes, $nRecordGroupID, $bAllowNoRecordGroup)
  {
    $oPermissionBridge = new PermissionBridge($nArea, $nType, $nScopes, $nRecordGroupID, $bAllowNoRecordGroup);
    if ($oPermissionBridge->Result)
      $this->m_aPermissionBridge[$nID] = $oPermissionBridge;
    else if ($this->HasPermission($nID))
      $this->RemovePermission($nID); 
    return $oPermissionBridge->Result;
  }
  
  public function SetRecordGroupID($nID,$nRecordGroupID, $bAllowNoRecordGroup)
  {
    if (!$this->HasPermission($nID))
      return FALSE;
    
    //already checked with the group
    if ($this->m_aPermissionBridge[$nID]->RecordGroupID == $nRecordGroupID && $nRecordGroupID > 0)
      return TRUE;
    
    $this->m_aPermissionBridge[$nID]->RecordGroupID = $nRecordGroupID;
    $this->m_aPermissionBridge[$nID]->AllowNoRecordGroup = $bAllowNoRecordGroup;
    
    $bHasPermission = $this->m_aPermissionBridge[$nID]->CheckScope();
    if (!$bHasPermission && !$bAllowNoRecordGroup) //need to remove the permission, because now it is being enforced more strictly
      $this->RemovePermission($nID);
    
    return $bHasPermission;
  }
  
  protected function RemovePermission($nID)
  {
    unset($this->m_aPermissionBridge[$nID]);
  }
}

?>
