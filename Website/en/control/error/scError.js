function SetError(sErr)
{
        var ctlError = document.getElementById("ctlError");
        
        if (ctlError)
        {
            ctlError.innerHTML= '<div class="message">' + sErr + '</div>' ;
        }
        else
          alert(sErr);
}
