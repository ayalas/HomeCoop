<?php

include_once 'settings.php';
include_once 'authenticate.php';

$sPageTitle = '';
$oRecord = new CoopOrderProduct;
$recProduct = NULL;

$sPackageSize = NULL;
$sUnitInterval = NULL;
$fCoopCosts = NULL;
$sQuantity = NULL;

try
{
  if ( isset($_GET['prd']) && isset($_GET['coid']) )
  {
    $oRecord->ProductID = intval($_GET['prd']);
    $oRecord->CoopOrderID = intval($_GET['coid']);
    
    //editing existing producer, for loading a specific producer, access may be denied completely
    $recProduct = $oRecord->LoadRecordForViewOnly();
  }
  
  if ( ($recProduct == NULL) || ($oRecord->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION) )
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $sPageTitle = $recProduct["sProduct"];
  
  if ( $recProduct["nItems"] > 1 && $recProduct["fItemQuantity"] != NULL && $recProduct["sItemUnitAbbrev"] != NULL )
    $sPackageSize = $recProduct["nItems"] . '‏X‏' . $recProduct["fItemQuantity"] . $recProduct["sItemUnitAbbrev"];
  else
  {
    //no need to show "1 item"
    if ($recProduct["fQuantity"] != 1 || $recProduct["UnitKeyID"] != Consts::UNIT_ITEMS)
      $sQuantity= $recProduct["fQuantity"] .' '. $recProduct["sUnitAbbrev"];
    if ($recProduct["fUnitInterval"] != NULL && $recProduct["fUnitInterval"] != 1)
      $sUnitInterval = $recProduct["fUnitInterval"] . '&nbsp;' . $recProduct["sUnitAbbrev"];
      
    if ($recProduct["fPackageSize"] != NULL && $recProduct["fPackageSize"] != $recProduct["fQuantity"])
      $sPackageSize = 'מגיע בגודל חבילה מינימלי של ' . $recProduct["fPackageSize"] . ' ' . $recProduct["sUnitAbbrev"];
  }
  
  $fCoopCosts = $recProduct["mCoopPrice"] - $recProduct["mProducerPrice"];
  
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
<link rel="stylesheet" type="text/css" href="style/main.css" />
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="script/public.js" ></script>
<script type="text/javascript" src="script/authenticated.js" ></script>
<script type="text/javascript">

function OpenPicViewer(sPicName)
{
 var nLeft = (screen.availWidth - 800)/2;
 if (nLeft < 0) nLeft = 0;

  var sParams = 'status=0,toolbar=0,menubar=0,top=100, left=' + nLeft + ', width=800,height=' + (screen.availHeight-200) ;
  window.open('<?php echo $g_sRootRelativePath, URL_UPLOAD_DIR; ?>' + sPicName, '_blank', sParams );
}

</script>
</head>
<body class="product" >
<form id="frmMain" name="frmMain" method="get" >
<table cellspacing="0" cellpadding="8" width="98%">
    <tr>
      <td>
        <table cellspacing="0" cellpadding="0">
        <tr><td colspan="2"><span class="pagename"><?php echo $sPageTitle; ?></span></td></tr>
        <?php 
        
          if ( $sQuantity != NULL )
          {
            echo '<tr><td colspan="2"><span>', $sQuantity,'</span></td></tr>';
          }
          
          if ($recProduct["sSpec"] != NULL)
            echo '<tr><td colspan="2"><span>', nl2br(htmlspecialchars ($recProduct["sSpec"])), '</span></td></tr><tr><td colspan="2">&nbsp;</td></tr>';
       
          if ( $sPackageSize != NULL )
          {
            echo '<tr><td colspan="2"><span>', $sPackageSize,'</span></td></tr>';
          }
          if ($sUnitInterval != NULL)
          {
            echo '<tr><td><span>הזמנה בהפרשים של‏:‏&nbsp;</span></td>',
                    '<td><span>', $sUnitInterval,'</span></td></tr>';
          }
          
          echo '<tr><td><span>יצרן‏:‏&nbsp;</span></td>',
              '<td><span>', htmlspecialchars($recProduct["sProducer"]),'</span></td></tr>';
          
          echo '<tr><td>&nbsp;</td></tr><tr><td><span>מחיר קואופרטיב‏:‏&nbsp;</span></td>',
                  '<td><span>', $recProduct["mCoopPrice"], '</span></td></tr>';
          
          if (SHOW_PRODUCER_PRICES_IN_PRODUCT_OVERVIEW)
          { 
            echo '<tr><td colspan="2"><span>', sprintf('[ מחיר היצרן: %1$s. עלויות הקואופ: %2$s ]', $recProduct["mProducerPrice"], $fCoopCosts),
                 '</span></td></tr>';   
          }
        
          ?>
        
        </table>
      </td>
      <td width="49%">
        <table cellspacing="0" cellpadding="0">
        <tr>
        <td>
        <?php 
        if ($recProduct["sImage1FileName"] != NULL)
        {
          echo '<div class="link" onclick="JavaScript:OpenPicViewer(\'', $recProduct["sImage1FileName"], 
             '\');"><img border="0" width="100%" src="',
                $g_sRootRelativePath, URL_UPLOAD_DIR, $recProduct["sImage1FileName"], '" /></div>';
        }
        ?>
        </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
        <td>
        <?php 
        if ($recProduct["sImage2FileName"] != NULL)
        {
          echo '<div class="link" onclick="JavaScript:OpenPicViewer(\'', $recProduct["sImage2FileName"], 
             '\');"><img border="0" width="100%" src="',
                $g_sRootRelativePath, URL_UPLOAD_DIR, $recProduct["sImage2FileName"], '" /></div>';
        }
        ?>
        </td>
        </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>
        <?php 
        include_once 'control/footer.php';
        ?>
      </td>
    </tr>
</table>
</form>
 </body>
</html>