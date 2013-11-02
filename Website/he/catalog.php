<?php

include_once 'settings.php';
if ( !PRODUCT_CATALOG_IS_PUBLIC || UserSession::IsAuthenticated() )
{
  include_once 'authenticate.php';
}

$oCache = new Cache;

//cache only when not authenticated
if (!isset($g_oMemberSession))
{  
  $oCache->CacheTimeInSeconds = PRODUCT_CATALOG_CACHING;
  $oCache->start(); // Start caching
}

if ((!$oCache->CanCache) || $oCache->IsCaching || isset($g_oMemberSession))
{

  $oData = new ProductCatalog;
  $recProduct = NULL;
  

  try
  {
    $recProduct = $oData->GetTable();
    
    if ( ($recProduct == NULL) || ($oData->LastOperationStatus == SQLBase::OPERATION_STATUS_NO_PERMISSION) )
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }
  }
  catch(Exception $e)
  {
    $g_oError->HandleException($e);
  }
  
  UserSessionBase::Close();

  ?>
  <!DOCTYPE HTML>
  <html dir='rtl' >
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="style/main.css" />
  <title>הזינו את שם הקואופרטיב שלכם: קטלוג המוצרים</title>
  <script type="text/javascript" src="script/public.js" ></script>
  <script type="text/javascript" src="script/authenticated.js" ></script>
  <script type="text/javascript">

  function OpenPicViewer(sPicName)
  {
   var nLeft = (screen.availWidth - 800)/2;
   if (nLeft < 0) nLeft = 0;

    var sParams = 'status=0,toolbar=0,menubar=0,top=100, left=' + nLeft + ', width=800,height=' + (screen.availHeight-200) ;
    window.open('<?php echo $g_sRootRelativePath,URL_UPLOAD_DIR; ?>' + sPicName, '_blank', sParams );
  }

  </script>
  </head>
  <body class="centered">
  <form id="frmMain" name="frmMain" method="post">
  <?php include_once 'control/header.php'; ?>
  <table cellspacing="0" cellpadding="0" >
      <tr>
          <td width="948"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename">קטלוג המוצרים</span></td>
      </tr>
      <tr>
          <td height="100%" >
              <table cellspacing="0" cellpadding="4" width="100%">
              <tr>
                <td colspan="3"><?php include_once 'control/error/ctlError.php'; ?></td>
              </tr>
              <tr>
                <td colspan="3">הערה: המחירים בקטלוג עשויים להיות שונים מאלו שבהזמנה ספציפית</td>
              </tr>
              <?php
                  if (!$recProduct)
                  {
                    echo "<tr><td colspan='3'>&nbsp;</td></tr><tr><td align='center' colspan='3'>לא נמצאו רשומות.</td></tr>";
                  }
                  else
                  {
                    while ( $recProduct )
                    {
                        $sPackageSize = NULL;
                        $sUnitInterval = NULL;
                        $fCoopCosts = NULL;
                        $sQuantity = NULL;
                        
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

                        echo "<tr>";

                        //info
                        echo '<td>',
                          '<table cellspacing="0" cellpadding="0">',
                          '<tr><td colspan="2"><span class="pagename">', $recProduct["sProduct"], '</span></td></tr>';

                          if ( $sQuantity != NULL )
                          {
                            echo '<tr><td colspan="2"><span>', $sQuantity,'</span></td></tr>';
                          }
                          
                          if ($recProduct["sSpec"] != NULL)
                            echo '<tr><td colspan="2"><span>', nl2br(htmlspecialchars ($recProduct["sSpec"])), '</span></td></tr>',
                                 '<tr><td colspan="2">&nbsp;</td></tr>';

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

                          if (SHOW_PRODUCER_PRICES_IN_PRODUCT_CATALOG)
                          { 
                            echo '<tr><td colspan="2"><span>', sprintf('[ מחיר היצרן: %1$s. עלויות הקואופ: %2$s ]', $recProduct["mProducerPrice"], $fCoopCosts),
                                 '</span></td></tr>';   
                          }

                          echo '</table>',
                        '</td>';

                        //pic1
                        echo '<td width="33%">';

                        if ($recProduct["sImage1FileName"] != NULL)
                        {
                          echo '<div class="link" onclick="JavaScript:OpenPicViewer(\'', $recProduct["sImage1FileName"], 
                             '\');"><img border="0" width="100%" src="',
                                $g_sRootRelativePath, URL_UPLOAD_DIR, $recProduct["sImage1FileName"], '" /></div>';
                        }
                        echo '</td>';

                        //pic2
                        echo '<td width="33%">';

                        if ($recProduct["sImage2FileName"] != NULL)
                        {
                          echo '<div class="link" onclick="JavaScript:OpenPicViewer(\'', $recProduct["sImage2FileName"], 
                             '\');"><img border="0" width="100%" src="',
                                $g_sRootRelativePath, URL_UPLOAD_DIR, $recProduct["sImage2FileName"], '" /></div>';
                        }
                        echo '</td>';

                        echo '</tr>';

                        $recProduct = $oData->fetch();
                    }
                  }
      ?>
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
<?php
}
else
  UserSessionBase::Close();

//cache only when not authenticated
if (!isset($g_oMemberSession))
  $oCache->end();

$oCache = NULL;

?>