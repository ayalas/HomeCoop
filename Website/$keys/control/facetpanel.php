<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//handle post
if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
{
  $sSelectedPLS = '';
  if ( isset( $_POST['hidSelectedPLs'] ) )
  {
      $sSelectedPLS = $_POST['hidSelectedPLs'];
  
      if ($g_oMemberPickupLocations->ApplyFilter($sSelectedPLS))
        FacetLoad(); //eload facet data
  }
}
    
?>

<div id="divPLFacet" class="facet">
  <span class="facettitle"><!$PAGE_TITLE_PICKUP_LOCATIONS$!></span>
<ul id="plfacetgrp" class="facetgrp">
<?php
  

  $nIndex = 0;
  $nRemoved = 0;
  $nLenSelected = count($g_aMemberPickupLocationIDs);
  $nLenAll = count($g_aMemberPickupLocations);
  foreach($g_aMemberPickupLocations as $PLID => $PL)
  {
    if ($PL['bBlocked'] || $PL['bDisabled'])     
      continue;
      
    echo '<li id="lipl_', $PLID, '" onclick="JavaScript:ToggleItemSelect(\'lipl_', $PLID, '\');" class="facetitm';
    
    if ($PL['bRemoved'])
    {
      $nRemoved++;
      
      if ($nRemoved == 1)
        echo ' firstfacet';
      if ($nRemoved == $g_nMemberPickupLocationsRemoved)
        echo ' lastfacet';
      
      echo ' unselectedfacet';
    }
    else
    {
      $nIndex++;
      if ($nIndex == 1)
        echo ' firstfacet';
      if ($nIndex == $nLenSelected)
        echo ' lastfacet';
    }

    echo '">', htmlspecialchars($PL['sPickupLocation']), '<li>';
  }
?>
</ul>
<div id="plfacetgrp_expand" class="facetexpander" onclick="JavaScript:TogglePLExpand();"><img id="imgFacetExpandArrow" src="img/arrow_down.gif"/></div>
<div id="divFacetFilter"><button type="button" id="btnFilter" disabled="disabled" class="facetapply" onclick="JavaScript:ApplyFacetFilter();"><!$BTN_FACET_APPLY_FILTER$!></button></div>
</div>
