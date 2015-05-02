<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oProducts = new Products;
$recProducts = NULL;
$sQuantity = NULL;
$sToolTipQuantityIntervalLine = NULL;
$sToolTipPackageSizeLine = NULL;
$bQuantityTooltip = FALSE;
$bReadOnly = TRUE;
$g_nCountRecords = 0; //PAGING
try
{
  $recProducts = $oProducts->GetTable();

  if ($oProducts->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION)
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  $bReadOnly = !$oProducts->HasPermission(Products::PERMISSION_COORD);
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
<title><!$COOPERATIVE_NAME$!>: <!$PAGE_TITLE_PRODUCTS$!></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td class="fullwidth"><span class="pagename"><!$PAGE_TITLE_PRODUCTS$!></span></td>
    </tr>
    <tr >
        <td >
                <table cellspacing="0" cellpadding="2" width="100%">
                  <tr>
                    <td colspan="8"><?php 
                  include_once '../control/error/ctlError.php';
                    ?></td>
                  </tr>
                  <?php if (!$bReadOnly) 
                  echo '<tr>',
                    '<td colspan="8"><a href="product.php" ><img border="0" title="<!$TABLE_ADD$!>" src="../img/edit-add-2.png" /></a></td>',
                    '</tr>';
                   ?>
                <tr>
                  <td class="columntitletiny"><!$FIELD_SORT_ORDER_SHORT$!></td>
                  <td class="columntitlelong"><!$FIELD_PRODUCT$!></td>
                  <td class="columntitle"><!$FIELD_PRODUCER$!></td>
                  <td class="columntitleshort"><?php
                    $headQuantity = new HtmlGridCellText('<!$FIELD_QUANTITY$!>', HtmlGridCellText::CELL_TYPE_SHORT);
                    $headQuantity->EchoHtml();
                    unset($headQuantity);
                  ?></td>
                  <td class="columntitleshort"><!$FIELD_PRODUCER_PRICE_SHORT$!></td>
                  <td class="columntitleshort"><!$FIELD_COOP_PRICE_SHORT$!></td>
                  <td class="columntitleshort" ><a id="burdenhlp" name="burdenhlp" class="tooltip" href="#burdenhlp" ><!$FIELD_BURDEN$!><span><!$TOOLTIP_BURDEN$!></span></a></td>
                  <td class="columntitlenowidth"><?php
                    $headDisabled = new HtmlGridCellText('<!$FIELD_IS_DISABLED$!>', HtmlGridCellText::CELL_TYPE_TINY);
                    $headDisabled->EchoHtml();
                    unset($headDisabled);
                  ?></td>
                </tr>
<?php
                if (!$recProducts)
                {
                  echo "<tr><td colspan='8'>&nbsp;</td></tr><tr><td align='center' colspan='8'><!$NO_RECORD_FOUND$!></td></tr>";
                }
                else
                {
                  while ( $recProducts )
                  {
                      //PAGING START
                      $g_nCountRecords++;
                      if ($g_nCountRecords > HOMECOOP_RECORDS_PER_PAGE) {
                        //do not display the row over the page reocrds - it's for checking if there is a next page
                        break;
                      }
                      //PAGING END
                      //
                      //sort
                      echo "<tr><td>" , $recProducts["nSortOrder"] , "</td>";
                      //product name
                      echo "<td nowrap><a href='product.php?id=" ,  $recProducts["ProductKeyID"] , "' >";
                                            
                      $cellProduct = new HtmlGridCellText($recProducts["sProduct"], HtmlGridCellText::CELL_TYPE_LONG);
                      $cellProduct->EchoHtml();
                      unset($cellProduct);
                      
                      echo "</a></td>";
                      
                      //producer
                      echo "<td nowrap><a href='producer.php?id=" ,  $recProducts["ProducerKeyID"] , "' >";
                           
                      $cellProducer = new HtmlGridCellText($recProducts["sProducer"], HtmlGridCellText::CELL_TYPE_NORMAL);
                      $cellProducer->EchoHtml();
                      unset($cellProducer);

                      echo "</a></td>";
                      
                      $oProductPackage = new ProductPackage($recProducts["nItems"], $recProducts["fItemQuantity"], 
                                $recProducts["sItemUnitAbbrev"], $recProducts["fUnitInterval"], $recProducts["sUnitAbbrev"], $recProducts["fPackageSize"], 
                                $recProducts["fQuantity"],0, 0,
                           'tooltiphelp', 'ProductPackage' . $recProducts["ProductKeyID"]);
                      
                      //package size and unit quantity 
                      echo '<td>';
                      $oProductPackage->EchoHtml();
                      echo '</td>';
                                            
                      //producer price
                      echo "<td>" , $recProducts["mProducerPrice"] , "</td>";
                      
                      //coop price
                      echo "<td>" , $recProducts["mCoopPrice"] , "</td>";
                      
                      //burden
                      echo "<td>" , $recProducts["fBurden"] , "</td>";
                      
                      echo "<td><a href='product.php?id=" ,  $recProducts["ProductKeyID"] , "' >";
                      
                      if ($recProducts["bDisabled"])
                          echo '<!$FIELD_VALUE_DISABLED$!>';
                      else
                          echo '<!$FIELD_VALUE_ENABLED$!>';

                      echo  "</a></td>";
                      
                      echo '</tr>';
   
                      $recProducts = $oProducts->fetch();
                  }
                }
?>
                </table>
           <?php
          //PAGING
          $g_BasePageUrl = 'products.php';

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
