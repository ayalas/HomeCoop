function Logout()
{
    document.getElementById('hidLogout').value = '1';
    document.forms[0].submit();
}

function OpenProductOverview(sPathToRoot, CoopOrderID, ProductID)
{
  var sUrl = sPathToRoot + 'product.php?prd=' + ProductID + "&coid=" + CoopOrderID;
  var nLeft = screen.availWidth/2 - screen.availWidth/4;
  if (nLeft < 0) nLeft = 0;

  var sParams = 'status=0,toolbar=0,menubar=0,height=' + (screen.availHeight*2/3) + ', width=' + (screen.availWidth/2) + ',top=100,left=' + nLeft;
  window.open(sUrl, '_blank', sParams );
}

function ToggleTabDisplay(sTabNameToShow) {
  var sTabToHide = document.getElementById('hidCurrentMainTab').value;
  
  if (sTabNameToShow == sTabToHide) return;
  
  document.getElementById(sTabToHide).style.display = "none";
  document.getElementById(sTabNameToShow).style.display = "block";
  
  document.getElementById(sTabToHide + "Item").style.backgroundColor = "#D8D8D8";
  document.getElementById(sTabNameToShow + "Item").style.backgroundColor = "#FFF";
  
  document.getElementById('hidCurrentMainTab').value = sTabNameToShow;
}

