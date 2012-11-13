<?php

include_once 'settings.php';

?>
<!DOCTYPE HTML>
<html>
 <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style/main.css" /> 
<title><!$ABOUT_SOFTWARE$!></title>

<script type="text/javascript" src="script/public.js" ></script>
 </head>
 <body class="centered">
   <form id="frmMain" name="frmMain" method="post" >
    <header>
    <br/><br/>
    <table>
      <tr>
        <td><a href="home.php" ><img border="0" src="logo.gif"/></a></td>
      </tr>
      <tr>
      <td>
        <?php
            include_once 'control/language.php';
        ?>
      </td>
      </tr>
    </table>
    <br/><br/>
    </header>
     <table width="800" cellpadding="0" cellspacing="0">
      <tr><td ><span class="cooptitle"><!$ABOUT_SOFTWARE$!></span></td></tr>
      <tr>
        <td><span><!$ABOUT_SOFTWARE_LINE1$!></span></td>
      </tr>
      <tr>
        <td><div dir="ltr" align="center">Copyright © 2012 Ayala Shani - ayala (at) code-op [dot] org, or ayalashah (at) joindiaspora [dot] com</div></td>
      </tr>
      <tr>
        <td><span><?php echo sprintf('<!$ABOUT_SOFTWARE_LINKS$!>', 
           '<a href="http://sourceforge.net/projects/homecoop/" target="_blank">sourceforge</a>',
           '<a href="https://github.com/ayalas/HomeCoop" target="_blank">github</a>'); ?></span></td>
      </tr>
      <tr>
        <td><span><!$ABOUT_SOFTWARE_LINE2$!></span></td>
      </tr>
      <tr>
        <td><span><!$ABOUT_SOFTWARE_LINE3$!></span></td>
      </tr>
      <tr>
        <td>
          <textarea readonly="true" dir="ltr" rows="20" cols="100">
            <?php
              include_once 'license.txt';
            ?>
          </textarea>
          
        </td>
      </tr>
      </table>
    </form>
  </body>
</html>
