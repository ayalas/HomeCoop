function SetError(sErr, sType)
{
        var ctlError = document.getElementById("ctlError");
        
        if (ctlError != null)
        {
            ctlError.innerHTML= '<div class="message ' + sType + '">' + sErr + '</div>' ;
        }
        else {
          alert(unescape(sErr));
        }
}
