<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//prepare facet data
$g_oMemberPickupLocations = NULL;
$g_aMemberPickupLocations = NULL;
$g_aMemberPickupLocationIDs = NULL;
$g_nMemberPickupLocationsRemoved = 0;

function FacetLoad()
{
  global $g_oMemberPickupLocations, $g_aMemberPickupLocations, $g_aMemberPickupLocationIDs, $g_nMemberPickupLocationsRemoved;
  
  $g_oMemberPickupLocations = new MemberPickupLocations();
  $g_aMemberPickupLocations = array();
  $g_aMemberPickupLocationIDs = array();
  $g_nMemberPickupLocationsRemoved = 0;
  
  $arrTemp = $g_oMemberPickupLocations->GetTableForFacet();


  //add pickup location id as key
  foreach ($arrTemp as $oTemp)
  {
    $g_aMemberPickupLocations[$oTemp['PickupLocationKeyID']] = $oTemp;
    if (!$oTemp['bDisabled'] && !$oTemp['bBlocked'])
    {
      if ($oTemp['bRemoved'])
        $g_nMemberPickupLocationsRemoved++;
      else
        $g_aMemberPickupLocationIDs[$oTemp['PickupLocationKeyID']] = $oTemp['PickupLocationKeyID'];
    }
  }
  
}

FacetLoad();

?>
