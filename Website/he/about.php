<?php

include_once 'settings.php';

?>
<!DOCTYPE HTML>
<html dir='rtl' >
 <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="style/main.css" /> 
<title>אודות התוכנה</title>

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
      <tr><td ><span class="cooptitle">אודות התוכנה</span></td></tr>
      <tr>
        <td><span>HomeCoop היא מערכת הזמנות אינטרנטית לקואופרטיבים צרכניים זעירים.</span></td>
      </tr>
      <tr>
        <td><div dir="ltr" align="center">Copyright © 2012 Ayala Shani - ayala (at) code-op [dot] org, or ayalashah (at) joindiaspora [dot] com</div></td>
      </tr>
      <tr>
        <td><span><?php echo sprintf('אתר התוכנה, כולל קישור להורדת חבילת ההתקנה: %1$s. קוד התוכנה: %2$s', 
           '<a href="http://sourceforge.net/projects/homecoop/" target="_blank">sourceforge</a>',
           '<a href="https://github.com/ayalas/HomeCoop" target="_blank">github</a>'); ?></span></td>
      </tr>
      <tr>
        <td><span>זוהי תוכנה חופשית: ניתן להפיץ ו\או לעדכן אותה תחת התנאים של רישיון ציבורי כללי של GNU המובא להלן, כפי שפורסם ע&quot;י קרן התוכנה החופשית, גרסה 3 של הרישיון, או, לבחירתך, כל גרסה מאוחרת יותר.</span></td>
      </tr>
      <tr>
        <td><span>תוכנה זו מופצת בתקווה שהיא תהיה שימושית, אבל ללא כל אחריות; ללא אפילו אחריות משתמעת של תאימות למסחר או למטרה ספציפית. ראו הרישיון להלן לפרטים נוספים:</span></td>
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
