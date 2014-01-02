<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//handle post
if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
{
  $sSelectedPLS = '';
  $sSelectedPRS = '';
  $bFacetLoad = FALSE;
  if ( isset( $_POST['hidSelectedPLs'] ) )
  {
      $sSelectedPLS = $_POST['hidSelectedPLs'];
  
      if ($g_oMemberPickupLocations->ApplyFilter($sSelectedPLS))
        $bFacetLoad = TRUE;
  }
  
  if ( isset( $_POST['hidSelectedPRs'] ) )
  {
      $sSelectedPRS = $_POST['hidSelectedPRs'];
  
      if ($g_oMemberProducers->ApplyFilter($sSelectedPRS))
        $bFacetLoad = TRUE;
  }
  
  if ($bFacetLoad)
    FacetLoad(); //reload facet data
}

function EchoFacetList(&$aFacetIDs, &$aFacet, &$nFacetRemoved, $sNameField, $sListItemIDPrefix, $sStateID)
{
  $nIndex = 0;
  $nRemoved = 0;
  $nLenSelected = count($aFacetIDs);
  $nLenAll = count($aFacet);
  foreach($aFacet as $ID => $Data)
  {
    if ($Data['bBlocked'] || $Data['bDisabled'])     
      continue;
      
    echo '<li id="', $sListItemIDPrefix, $ID, '" onclick="JavaScript:ToggleItemSelect(\'', $sListItemIDPrefix, $ID, '\', \'', $sStateID , 
        '\');" class="facetitm';
    
    if ($Data['bRemoved'])
    {
      $nRemoved++;
      
      if ($nRemoved == 1)
        echo ' firstfacet';
      if ($nRemoved == $nFacetRemoved)
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

    echo '">', htmlspecialchars($Data[$sNameField]), '<li>';
  }
}

?>
<div id="divPLFacet" class="facet mobilehide">
  <span class="facettitle">מקומות איסוף</span>
<ul id="plfacetgrp" class="facetgrp">
<?php
  EchoFacetList($g_aMemberPickupLocationIDs, $g_aMemberPickupLocations, $g_nMemberPickupLocationsRemoved, 'sPickupLocation',
      'lipl_', 'hidplfacetgrpexpandstate');
?>
</ul>
<div id="plfacetgrp_expand" class="facetexpander" onclick="JavaScript:TogglePLExpand();"><img id="imgPLFacetExpandArrow" src="img/arrow_down.gif"/></div>
<div class="facetsep"></div>
<span class="facettitle">יצרנים</span>
<ul id="prfacetgrp" class="facetgrp">
<?php
  EchoFacetList($g_aMemberProducerIDsIDs, $g_aMemberProducers, $g_nMemberProducersRemoved, 'sProducer',
      'lipr_', 'hidprfacetgrpexpandstate');
?>
</ul>  
<div id="prfacetgrp_expand" class="facetexpander" onclick="JavaScript:TogglePRExpand();"><img id="imgPRFacetExpandArrow" src="img/arrow_down.gif"/></div>

<div id="divFacetFilter"><button type="button" id="btnFilter" disabled="disabled" class="facetapply" onclick="JavaScript:ApplyFacetFilter();">החלת סינון</button></div>
</div>
