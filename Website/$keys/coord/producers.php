<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oProducers = new Producers;
$recProducers = NULL;
$bCanSetCoord = FALSE;
$g_nCountRecords = 0; //PAGING

try
{
  $recProducers = $oProducers->GetTable();

  if ($oProducers->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION)
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $bCanSetCoord = $oProducers->HasPermission(SQLBase::PERMISSION_COORD_SET);
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
<title><!$COOPERATIVE_NAME$!>: <!$PAGE_TITLE_PRODUCERS$!></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td class="fullwidth"><span class="pagename"><!$PAGE_TITLE_PRODUCERS$!></span></td>
    </tr>
    <tr >
        <td >
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="3"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                  <?php if ($oProducers->HasPermission(SQLBase::PERMISSION_ADD)) { ?>
                  <tr>
                    <td colspan="3"><a href="producer.php" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a></td>
                  </tr>
                  <?php } ?>
                <tr>
                  <td class="columntitleextralong"><!$FIELD_PRODUCER_NAME$!></td>
                  <td class="columntitleshort"><!$FIELD_IS_DISABLED$!></td>
                  <td class="columntitlenowidth"><?php if ($bCanSetCoord) echo '<!$FIELD_COORD$!>'; ?></td></tr>
<?php
                if (!$recProducers)
                {
                  echo "<tr><td colspan='3'>&nbsp;</td></tr><tr><td align='center' colspan='3'><!$NO_RECORD_FOUND$!></td></tr>";
                }
                else
                {
                  while ( $recProducers )
                  {
                      //PAGING START
                      $g_nCountRecords++;
                      if ($g_nCountRecords > HOMECOOP_RECORDS_PER_PAGE) {
                        //do not display the row over the page reocrds - it's for checking if there is a next page
                        break;
                      }
                      //PAGING END
                      
                      echo "<tr><td><a href='producer.php?id=" ,  $recProducers["ProducerKeyID"] , "' >" ,  
                              htmlspecialchars($recProducers["sProducer"]) , "</a></td><td>";
                      if ($recProducers["bDisabled"])
                          echo "<!$FIELD_VALUE_DISABLED$!>";
                      else
                          echo "<!$FIELD_VALUE_ENABLED$!>";
                      echo  "</td>";
                      
                      echo "<td>";
                      if ($bCanSetCoord)
                      {
                        echo "<a href='coordinate.php?rid=" , $recProducers["ProducerKeyID"] ,
                                "&pa=" , Consts::PERMISSION_AREA_PRODUCERS;
                        if ($recProducers["CoordinatingGroupID"])
                          echo "&id=" ,  $recProducers["CoordinatingGroupID"];
                        echo "' ><!$RECORD_COORD$!></a>";
                      }
                      echo "</td>";
                      echo '</tr>';
                                            
                      $recProducers = $oProducers->fetch();
                  }
                }
?>
                </table>
  <?php
          //PAGING
          $g_BasePageUrl = 'producers.php';

          include_once '../control/paging.php';
          ?>
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
