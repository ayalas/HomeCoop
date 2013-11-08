<?php

include_once 'settings.php';

?>
<!DOCTYPE HTML>
<html>
 <head>
 <?php include_once 'control/headtags.php'; ?>
 <title><!$ABOUT_SOFTWARE$!></title>
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
     <table class="fullwidth" cellpadding="0" cellspacing="0">
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
          <textarea readonly="true" dir="ltr" rows="20" class="fullwidth" >
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
