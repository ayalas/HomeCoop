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

function ToggleMobileExpand()
{
  var ctlExpandState = document.getElementById('hidfacetmblexpandstate');
  var nExpandStatus = ctlExpandState.value;
  if (nExpandStatus == 0)
  {
         mobileShow(document.getElementById('divPLFacet'));
         mobileHide(document.getElementById('tdMain'));
         
         nExpandStatus = 1;
         document.getElementById('imgFacetMobileExpandArrow').src = 'img/document-close-3.png';
  }
  else
  {
         mobileShow(document.getElementById('tdMain'));
         mobileHide(document.getElementById('divPLFacet'));
         
         nExpandStatus = 0;
         document.getElementById('imgFacetMobileExpandArrow').src = 'img/filter.png';
  }
  
  ctlExpandState.value = nExpandStatus;
}

function ToggleItemSelect(sCtlID)
{
  var nExpandStatus = document.getElementById('hidplfacetgrpexpandstate').value;

  var ctlItem = document.getElementById(sCtlID);

  if (ctlItem.className.indexOf('unselectedfacet') >= 0)
  {
	removeCssClass(ctlItem, 'unselectedfacet');
   	//if the current state DOES display unselected, show the element
  	ctlItem.style.display = 'block';
  }
  else
  {
        addCssClass(ctlItem, 'unselectedfacet', true);
   	//if the current state does NOT display unselected, hide the element
	if (nExpandStatus == 0)
  	   ctlItem.style.display = 'none';
 	else
        {
         ctlItem.style.display = 'block';
        }
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
