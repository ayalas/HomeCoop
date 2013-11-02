<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oTable = new PickupLocations;
$recTable = NULL;
$bCanSetCoord = FALSE;

try
{
  $recTable = $oTable->GetTable();

  if ($oTable->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION)
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $bCanSetCoord = $oTable->HasPermission(SQLBase::PERMISSION_COORD_SET);
}
catch(Exception $e)
{
  $g_oError->HandleException($e);
}

//close session opened in 'authenticate.php' when not required anymore
//must be after any call to HandleException, because it writes to the session
UserSessionBase::Close();

?>
<!DOCTYPE HTML>
<html dir='rtl' >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>הזינו את שם הקואופרטיב שלכם: מקומות איסוף</title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td width="948"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename">מקומות איסוף</span></td>
    </tr>
    <tr >
        <td >
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="5"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                  <?php
                  if ($oTable->HasPermission(SQLBase::PERMISSION_ADD))
                  {
                  ?>
                  <tr>
                    <td colspan="5"><a href="pickuploc.php" ><img border="0" title="הוספה" src="../img/edit-add-2.png" /></a></td>
                  </tr>
                  <?php
                  }
                  ?>
                <tr>
                  <td class="columntitlelong">מקום האיסוף</td>
                  <td class="columntitletiny">סבב</td>
                  <td class="columntitlelong">כתובת</td>
                  <td class="columntitle"><a class="tooltip" href="#" >קבולת משלוח<span>הקיבולת המקסימאלית של מקום האיסוף במונחים של שדה המוצר &quot;מעמסה&quot;. הסכום של המעמסות של כל המוצרים כפול הכמות שהוזמנה מכל מוצר יושווה לערך זה עבור כל הזמנות החברות/ים שמקום איסוף זה נבחר בהם. זהו רק ערך ברירת מחדל, וניתן להחליפו בהגדרות מקום האיסוף של הזמנת הקואופרטיב. אם לא הוחלף הערך בהגדרות מקום האיסוף של הזמנת הקואופרטיב,  חברות/ים לא יוכלו להשלים הזמנה שחורגת מההגבלה שהוגדרה כאן.</span></a></td>
                  <td class="columntitleshort">קופה</td>
                  <td class="columntitleshort">עדכון קופה</td>
                  <td class="columntitleshort">מצב</td>
                  <td class="columntitlenowidth"><?php if ($bCanSetCoord) echo ''; ?></td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='5'>&nbsp;</td></tr><tr><td align='center' colspan='5'>לא נמצאו רשומות.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      //name
                      echo "<tr><td><a href='pickuploc.php?id=" ,  $recTable["PickupLocationKeyID"] , "' >" ,
                              htmlspecialchars( $recTable["sPickupLocation"]) ,  "</a></td>";
                      
                      //rotation
                      echo '<td>' , $recTable["nRotationOrder"] , '</td>';
                      
                      //address
                      echo "<td>";
                      
                      $cellAddress = new HtmlGridCellText( $recTable["sAddress"], HtmlGridCellText::CELL_TYPE_EXTRA_LONG );
                      $cellAddress->EchoHtml();
                      unset($cellAddress);
    
                      echo "</td>";
                      
                      //max burden
                      echo '<td>' , $recTable["fMaxBurden"] , '</td>';
                      
                      //cachier
                      echo '<td>' , $recTable["mCachier"] , '</td>';
                      
                      //cachier update date
                      echo "<td>";
                      if ($recTable["dCachierUpdate"] != NULL)
                      {
                        $oHtmlDateString = new HtmlDateString($recTable["dCachierUpdate"], HtmlDateString::TYPE_NO_CURRENT_YEAR);
                        $oHtmlDateString->EchoHtml();
                      }
                      echo "</td>";
                      
                      echo "<td><a href='pickuploc.php?id=" ,  $recTable["PickupLocationKeyID"] , "' >";
                      if ($recTable["bDisabled"])
                          echo "לא פעיל";
                      else
                          echo "פעיל";
                      echo  "</a></td>";
                      
                      echo "<td>";
                      if ($bCanSetCoord)
                      {
                        echo "<a href='coordinate.php?rid=" , $recTable["PickupLocationKeyID"] ,
                                "&pa=" , Consts::PERMISSION_AREA_PICKUP_LOCATIONS;
                        if ($recTable["CoordinatingGroupID"])
                          echo "&id=" ,  $recTable["CoordinatingGroupID"];
                        echo "' >תיאום</a>";
                      } 
                      
                      echo '</td></tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
?>
                </table>
                </td>
    </tr>
    <tr>
      <td>
        <?php 
        include_once '../control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>
