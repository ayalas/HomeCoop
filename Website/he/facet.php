<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//prepare facet data
$g_oMemberPickupLocations = NULL;
$g_aMemberPickupLocations = NULL;
$g_aMemberPickupLocationIDs = NULL;
$g_nMemberPickupLocationsRemoved = 0;

$g_oMemberProducers = NULL;
$g_aMemberProducers = NULL;
$g_aMemberProducerIDs = NULL;
$g_nMemberProducersRemoved = 0;

function FacetLoad()
{
  global $g_oMemberPickupLocations, $g_aMemberPickupLocations, $g_aMemberPickupLocationIDs, $g_nMemberPickupLocationsRemoved;
  global $g_oMemberProducers, $g_aMemberProducers, $g_aMemberProducerIDs, $g_nMemberProducersRemoved;
  
  $g_oMemberPickupLocations = new MemberPickupLocations();
  LoadSpecificFacet($g_oMemberPickupLocations, $g_aMemberPickupLocations, $g_aMemberPickupLocationIDs, $g_nMemberPickupLocationsRemoved,
      'PickupLocationKeyID');
  
  $g_oMemberProducers = new MemberProducers();
  LoadSpecificFacet($g_oMemberProducers, $g_aMemberProducers, $g_aMemberProducerIDs, $g_nMemberProducersRemoved,
      'ProducerKeyID');  
}

function LoadSpecificFacet(&$oFacet, &$aFacet, &$aFacetIDs, &$nFacetRemoved, $sIDField)
{
  $aFacet = array();
  $aFacetIDs = array();
  $nFacetRemoved = 0;
  
  $arrTemp = $oFacet->GetTableForFacet();
  
  //add producer id as key
  foreach ($arrTemp as $oTemp)
  {
    $aFacet[$oTemp[$sIDField]] = $oTemp;
    if (!$oTemp['bDisabled'] && !$oTemp['bBlocked'])
    {
      if ($oTemp['bRemoved'])
        $nFacetRemoved++;
      else
        $aFacetIDs[$oTemp[$sIDField]] = $oTemp[$sIDField];
    }
  }
}

FacetLoad();

?>
