function TogglePLExpand()
{
 var sDisplay = '';
 var ctlExpandState = document.getElementById('hidplfacetgrpexpandstate');
 var nExpandStatus = ctlExpandState.value;
 if (nExpandStatus == 0)
 {
 	sDisplay = 'block';
	nExpandStatus = 1;
        document.getElementById('imgFacetExpandArrow').src = 'img/arrow_up.gif';
 }
 else
 {
	sDisplay = 'none';
	nExpandStatus = 0;
        document.getElementById('imgFacetExpandArrow').src = 'img/arrow_down.gif';
 }

 //get child nodes of plfacetgrp and toggle display of li elements with the class unselectedfacet
 
 var ctlULFacet = document.getElementById('plfacetgrp');
 if (ctlULFacet != null && ctlULFacet.children != null && ctlULFacet.children.length > 0)
 {
   for(var i=0; i< ctlULFacet.children.length; i++)
   {
	if (ctlULFacet.children[i].className.indexOf('unselectedfacet') >= 0)
		ctlULFacet.children[i].style.display = sDisplay;
   }
 }

  ctlExpandState.value = nExpandStatus;
}

function ToggleItemSelect(sCtlID)
{
  var nExpandStatus = document.getElementById('hidplfacetgrpexpandstate').value;

  var ctlItem = document.getElementById(sCtlID);

  if (ctlItem.className.indexOf('unselectedfacet') >= 0)
  {
	ctlItem.className = ctlItem.className.replace(' unselectedfacet' , '');
   	//if the current state DOES display unselected, show the element
  	ctlItem.style.display = 'block';
  }
  else
  {
	ctlItem.className += ' unselectedfacet';
   	//if the current state does NOT display unselected, hide the element
	if (nExpandStatus == 0)
  	   ctlItem.style.display = 'none';
 	else
    	   ctlItem.style.display = 'block';
  }

  document.getElementById('btnFilter').removeAttribute('disabled');
}

function ApplyFacetFilter()
{
 var sSelected = '';
 var ctlULFacet = document.getElementById('plfacetgrp');
 if (ctlULFacet != null && ctlULFacet.children != null && ctlULFacet.children.length > 0)
 {
   for(var i=0; i< ctlULFacet.children.length; i++)
   {
 	if (ctlULFacet.children[i].className.indexOf('facetitm') >= 0 && ctlULFacet.children[i].className.indexOf('unselectedfacet') == -1)
		sSelected += ctlULFacet.children[i].id.substr(5) + ';';
   }
 }

 document.getElementById('hidSelectedPLs').value = sSelected;
 document.frmHome.submit();
}
