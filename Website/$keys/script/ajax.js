

function createXMLHttpRequest() {
  try { return new XMLHttpRequest(); } catch(e) {}
  try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
  try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
  alert("To use this feature, Ajax calls must be supported by your web browser.");
  return null;
}

function DateStringToDate(relPath, sDateString)
{
  var xmlhttp=createXMLHttpRequest();
  xmlhttp.open("GET", relPath + "jsdate.php?d=" + encodeURIComponent(sDateString)  ,false);
  xmlhttp.send();
  return new Date(xmlhttp.responseText);
}

function viewcalendar(relPath, ctlToUpdate) {
  var ctl = document.getElementById(ctlToUpdate);
  
  var sUrl = relPath + "calendar.php?ctl=" + ctlToUpdate;

  if (ctl.value != '')
  {
    var dDate = DateStringToDate(relPath, ctl.value);
    if (dDate.toString() != "NaN" && dDate.toString() != "Invalid Date")
    {
      sUrl = sUrl + "&year=" + dDate.getFullYear();
      sUrl = sUrl + "&month=" + (dDate.getMonth()+1); //month in JS is 0 to 11
    }
  }
  //kalendarik
  kalendarik = window.open(sUrl, "_blank" , "location=0, menubar=0, scrollbars=0, status=0, titlebar=0, toolbar=0, directories=0, resizable=1, width=220, height=220, top=<!$DATE_PICKER_Y_POSITION$!>, left=<!$DATE_PICKER_X_POSITION$!>");
  
}