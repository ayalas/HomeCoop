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
<html>
<head>
<?php include_once '../control/headtags.php'; ?>
<title><!$COOPERATIVE_NAME$!>: <!$PAGE_TITLE_PICKUP_LOCATIONS$!></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td class="fullwidth"><span class="pagename"><!$PAGE_TITLE_PICKUP_LOCATIONS$!></span></td>
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
                    <td colspan="5"><a href="pickuploc.php" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a></td>
                  </tr>
                  <?php
                  }
                  ?>
                <tr>
                  <td class="columntitlelong"><!$FIELD_PICKUP_LOCATION_NAME$!></td>
                  <td class="columntitletiny"><!$FIELD_PICKUP_ROTATION_ORDER_SHORT$!></td>
                  <td class="columntitlelong"><!$FIELD_PICKUP_LOCATION_ADDRESS$!></td>
                  <td class="columntitle"><a id="maxburdenhlp" name="maxburdenhlp" class="tooltip" href="#maxburdenhlp" ><!$FIELD_PICKUP_LOCATION_MAX_BURDEN$!><span><!$TOOLTIP_PICKUP_LOCATION_MAX_BURDEN$!></span></a></td>
                  <td class="columntitleshort"><!$FIELD_CACHIER$!></td>
                  <td class="columntitleshort"><!$FIELD_CACHIER_DATE$!></td>
                  <td class="columntitleshort"><!$FIELD_IS_DISABLED$!></td>
                  <td class="columntitlenowidth"><?php if ($bCanSetCoord) echo '<!$FIELD_COORD$!>'; ?></td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='5'>&nbsp;</td></tr><tr><td align='center' colspan='5'><!$NO_RECORD_FOUND$!></td></tr>";
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
                          echo "<!$FIELD_VALUE_DISABLED$!>";
                      else
                          echo "<!$FIELD_VALUE_ENABLED$!>";
                      echo  "</a></td>";
                      
                      echo "<td>";
                      if ($bCanSetCoord)
                      {
                        echo "<a href='coordinate.php?rid=" , $recTable["PickupLocationKeyID"] ,
                                "&pa=" , Consts::PERMISSION_AREA_PICKUP_LOCATIONS;
                        if ($recTable["CoordinatingGroupID"])
                          echo "&id=" ,  $recTable["CoordinatingGroupID"];
                        echo "' ><!$RECORD_COORD$!></a>";
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
