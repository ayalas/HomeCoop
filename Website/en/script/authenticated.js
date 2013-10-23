function Logout()
{
    document.getElementById('hidLogout').value = '1';
    document.forms[0].submit();
}

function OpenProductOverview(sPathToRoot, CoopOrderID, ProductID)
{
  var sUrl = sPathToRoot + 'product.php?prd=' + ProductID + "&coid=" + CoopOrderID;
  var nLeft = (screen.availWidth - 800)/2;
  if (nLeft < 0) nLeft = 0;

  var sParams = 'status=0,toolbar=0,menubar=0,top=100, left=' + nLeft + ', width=800,height=' + (screen.availHeight-200) ;
  window.open(sUrl, '_blank', sParams );
}

function ToggleTabDisplay(sTabNameToShow) {
  var sTabToHide = document.getElementById('hidCurrentMainTab').value;
  
  document.getElementById(sTabToHide).style.display = "none";
  document.getElementById(sTabNameToShow).style.display = "block";
  
  document.getElementById(sTabToHide + "Item").style.backgroundColor = "#D8D8D8";
  document.getElementById(sTabNameToShow + "Item").style.backgroundColor = "#FFF";
  
  document.getElementById('hidCurrentMainTab').value = sTabNameToShow;
}

