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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../style/main.css" />
<title>Enter Your Cooperative Name: Products</title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0" >
    <tr>
        <td width="908"><span class="coopname">Enter Your Cooperative Name:&nbsp;</span><span class="pagename">Products</span></td>
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
                    '<td colspan="8"><a href="product.php" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a></td>',
                    '</tr>';
                   ?>
                <tr>
                  <td class="columntitletiny">#</td>
                  <td class="columntitlelong">Product</td>
                  <td class="columntitle">Producer</td>
                  <td class="columntitleshort"><?php
                    $headQuantity = new HtmlGridCellText('Quantity', HtmlGridCellText::CELL_TYPE_SHORT);
                    $headQuantity->EchoHtml();
                    unset($headQuantity);
                  ?></td>
                  <td class="columntitleshort">Prod. P.</td>
                  <td class="columntitleshort">Coop P.</td>
                  <td class="columntitleshort" ><a class="tooltip" href="#" >Burden<span>An index that indicates how much this product &quot;burdens&quot; a delivery. Helps to uphold a cooperative order&#x27;s Delivery Capacity, if set</span></a></td>
                  <td class="columntitlenowidth"><?php
                    $headDisabled = new HtmlGridCellText('Status', HtmlGridCellText::CELL_TYPE_TINY);
                    $headDisabled->EchoHtml();
                    unset($headDisabled);
                  ?></td>
                </tr>
<?php
                if (!$recProducts)
                {
                  echo "<tr><td colspan='8'>&nbsp;</td></tr><tr><td align='center' colspan='8'>No records.</td></tr>";
                }
                else
                {
                  while ( $recProducts )
                  {
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
                                $recProducts["fQuantity"],0, 0);
                      
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
                          echo 'Inactive';
                      else
                          echo 'Active';

                      echo  "</a></td>";
                      
                      echo '</tr>';
   
                      $recProducts = $oProducts->fetch();
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
