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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>Enter Your Cooperative Name: Pickup Locations</title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename">Pickup Locations</span></td>
    </tr>
    <tr >
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" height="100%" >
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
                    <td colspan="5"><a href="pickuploc.php" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a></td>
                  </tr>
                  <?
                  }
                  ?>
                <tr>
                  <td class="columntitlelong">Location Name</td>
                  <td class="columntitletiny">Rotation</td>
                  <td class="columntitlelong">Address</td>
                  <td class="columntitle"><a class="tooltip" href="#" >Delivery Capacity<span>The maximum capacity of the pickup location in terms of the product field &quot;Burden&quot;. the sum for all products of &quot;Burden&quot; times product quantity will be compared to this value for all the members&#x27; orders that have this pickup location selected. This is only a default value and can be overwritten in the cooperative order&#x27;s pickup location settings. If not overridden in the coop order, members will not be able to place an order that exceeds the limitation set here.</span></a></td>
                  <td class="columntitleshort">Cachier</td>
                  <td class="columntitleshort">Cachier Update</td>
                  <td class="columntitleshort">Status</td>
                  <td class="columntitlenowidth"><?php if ($bCanSetCoord) echo ''; ?></td>
                </tr>
<?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='5'>&nbsp;</td></tr><tr><td align='center' colspan='5'>No records.</td></tr>";
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
                      
                      echo "<td>";
                      if ($recTable["bDisabled"])
                          echo "Inactive";
                      else
                          echo "Active";
                      echo  "</td>";
                      
                      echo "<td>";
                      if ($bCanSetCoord)
                      {
                        echo "<a href='coordinate.php?rid=" , $recTable["PickupLocationKeyID"] ,
                                "&pa=" , Consts::PERMISSION_AREA_PICKUP_LOCATIONS;
                        if ($recTable["CoordinatingGroupID"])
                          echo "&id=" ,  $recTable["CoordinatingGroupID"];
                        echo "' >Coordination</a>";
                      } 
                      
                      echo '</td></tr>';
   
                      $recTable = $oTable->fetch();
                  }
                }
?>
                </table>
                </td>
                <td width="128" >
                <?php 
                    include_once '../control/coordpanel.php'; 
                ?>
                </td>
            </tr>
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
