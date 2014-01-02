<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

interface IMemberFacet {
  public function GetTableForFacet();
  public function GetTable();
  public function BlockFromFacet($ID, $bBlock = 1, $bInsert = TRUE);
  public function RemoveFromFacet($ID, $bRemove = 1, $bInsert = TRUE);
  public function ApplyFilter($sSelectedIDs);
}

?>
