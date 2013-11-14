<?php

include_once 'settings.php';

?>
<!DOCTYPE HTML>
<html>
 <head>
 <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, width=device-width, user-scalable=0" />
 <?php include_once 'control/headtags.php'; ?>
 <title>About the software</title>
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
      <tr><td ><span class="cooptitle">About the software</span></td></tr>
      <tr>
        <td><span>HomeCoop is a tiny local consumer cooperatives web ordering system.</span></td>
      </tr>
      <tr>
        <td><div dir="ltr" align="center">Copyright © 2012 Ayala Shani - ayala (at) code-op [dot] org, or ayalashah (at) joindiaspora [dot] com</div></td>
      </tr>
      <tr>
        <td><span><?php echo sprintf('The software website, including a download installation package link: %1$s. Source code: %2$s', 
           '<a href="http://sourceforge.net/projects/homecoop/" target="_blank">sourceforge</a>',
           '<a href="https://github.com/ayalas/HomeCoop" target="_blank">github</a>'); ?></span></td>
      </tr>
      <tr>
        <td><span>This is a free software: you can redistribute it and/or modify it under the terms of the GNU General Public License included below, as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.</span></td>
      </tr>
      <tr>
        <td><span>This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the below GNU General Public License for more details:</span></td>
      </tr>
      <tr>
        <td>
          <textarea readonly="true" dir="ltr" rows="20" cols="40" class="fullwidth" >
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
