<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oRecord = new CoopOrderProduct;

$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_PRODUCTS;
$oTabInfo->IsSubPage = TRUE;
$arrProducts = NULL;
$recProducers = NULL;
$oProducers = NULL;
$sPageTitle = 'מוצרים';
$bReadOnly = FALSE;

try
{
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {   
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );

    $oRecord->CopyCoopOrderData();
    
    $oRecord->ProducerPrice = NULL;
    if ( isset($_POST['txtProducerPrice']) && !empty($_POST['txtProducerPrice']))
       $oRecord->ProducerPrice = 0 + trim($_POST['txtProducerPrice']);

    $oRecord->CoopPrice = NULL;
    if ( isset($_POST['txtCoopPrice']) && !empty($_POST['txtCoopPrice']))
       $oRecord->CoopPrice = 0 + trim($_POST['txtCoopPrice']);

    $oRecord->MaxUserOrder = NULL;
    if ( isset($_POST['txtMaxUserOrder']) && !empty($_POST['txtMaxUserOrder']))
       $oRecord->MaxUserOrder = 0 + trim($_POST['txtMaxUserOrder']);

    $oRecord->MaxCoopOrder = NULL;
    if ( isset($_POST['txtMaxCoopOrder']) && !empty($_POST['txtMaxCoopOrder']))
       $oRecord->MaxCoopOrder = 0 + trim($_POST['txtMaxCoopOrder']);

    $oRecord->Burden = NULL;
    if ( isset($_POST['txtBurden']) && !empty($_POST['txtBurden']))
       $oRecord->Burden = 0 + trim($_POST['txtBurden']);
    
    if (!$oRecord->IsExistingRecord) //get producer only on new record. It cannot be changed
    {
      $sCtl = HtmlSelectPDO::PREFIX . 'ProducerKeyID';
      if ( isset( $_POST[$sCtl] ))
        $oRecord->ProducerID = intval($_POST[$sCtl]);
    }
    
    if (!$oRecord->IsExistingRecord) //get product only on new record. It cannot be changed for existing one
    {
      $sCtl = HtmlSelectPDO::PREFIX . 'ProductKeyID';
      if ( isset( $_POST[$sCtl] ))
        $oRecord->ProductID = intval($_POST[$sCtl]);
    }

    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case CoopOrderProduct::POST_ACTION_SELECT_PRODUCER:
          if ($oRecord->ProducerID > 0) //get producer only on new record. It cannot be changed
          {
             $oRecord->ProductID = 0;
             $oRecord->ProducerPrice = NULL;
             $oRecord->CoopPrice = NULL;
             $oRecord->Burden = NULL;
             $oRecord->MaxUserOrder = NULL;
          }
          break;
        case CoopOrderProduct::POST_ACTION_SELECT_PRODUCT:
          //get product defaults
          if ($oRecord->ProductID > 0)
          {
            $oProduct = new Product;
            if ($oProduct->LoadCOProductDefaults($oRecord->ProductID))
            {
               $oRecord->ProducerPrice = $oProduct->ProducerPrice;
               $oRecord->CoopPrice = $oProduct->CoopPrice;
               $oRecord->Burden = $oProduct->Burden;
               $oRecord->MaxUserOrder = $oProduct->MaxUserOrder;
            }
            unset($oProduct);
          }
          break;
        case SQLBase::POST_ACTION_SAVE:
          //collect data
          if ($oRecord->IsExistingRecord)
            $oRecord->ProductID = $oRecord->OriginalProductID;
          else
          {
            //get product only on new record. It cannot be changed
            $sCtl = HtmlSelectArray::PREFIX . 'ProductKeyID';
            if ( isset( $_POST[$sCtl] ))
              $oRecord->ProductID = intval($_POST[$sCtl]);
          }

          $bSuccess = false;
          if ($oRecord->IsExistingRecord)
            $bSuccess = $oRecord->Edit();
          else
          {
            $bSuccess = $oRecord->Add();
            if ($bSuccess) 
              $oRecord->LoadRecord(); //loads default values and other data
          }
          
          if ( $bSuccess )
            $g_oError->AddError('הרשומה נשמרה בהצלחה.');   
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
            $g_oError->AddError('הרשומה לא נשמרה. אין לך הרשאות מספיקות או שאירעה שגיאה.');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $nCOID = $oRecord->CoopOrderID;
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('coproducts.php?id=' . $nCOID);
              exit;
          }
          else
              $g_oError->AddError('הרשומה לא נמחקה.');
          break;
      }
    }
  }
  else
  {
    if (isset($_GET['coid']))
      $oRecord->CoopOrderID = intval($_GET['coid']);
    
    if (isset($_GET['prdid']))
      $oRecord->ProductID = intval($_GET['prdid']);
    
    if(!$oRecord->LoadRecord())
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }
  }
  
  switch($oRecord->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $bReadOnly = ($oRecord->Status != CoopOrder::STATUS_ACTIVE 
          && $oRecord->Status != CoopOrder::STATUS_DRAFT
          && $oRecord->Status != CoopOrder::STATUS_LOCKED );
  
  if ( !$bReadOnly && !$oRecord->IsExistingRecord )
  {
    $oProducers = new CoopOrderProducers;
    $oProducers->CoopOrderID = $oRecord->CoopOrderID;
    $recProducers = $oProducers->LoadData();
    
    if ( !$recProducers )
    {
      $g_oError->AddError('אין יצרנים בהזמנה הנוכחית. יש להוסיף תחילה יצרנים');
      $bReadOnly = TRUE;
    }
    else
    {
      $oProducts = new Products;
      $arrProducts = $oProducts->GetListForCoopOrder($oRecord->ProducerID, 
              0, $oRecord->CoopOrderID );
      if ( !$oProducts->HasPermission(SQLBase::PERMISSION_COORD) ) //completely denied access
      {
          RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
          exit;
      }

      //check if empty list
      if (!is_array( $arrProducts ) || count( $arrProducts ) == 0)
        $g_oError->AddError('לא מוגדרים במערכת מוצרים פעילים, שלא נבחרו כבר בהזמנה הנוכחית');
    }
  }
  
  //if does not have edit permissions, set to read only
  if (!$bReadOnly && !$oRecord->CheckPermission())
    $bReadOnly = TRUE;
  
  $sPageTitle = $oRecord->Name . ' - מוצרים';
  $oTabInfo->ID = $oRecord->CoopOrderID;
  $oTabInfo->CoopOrderTitle = $oRecord->Name;
  $oTabInfo->Status = $oRecord->Status;
  $oTabInfo->CoordinatingGroupID = $oRecord->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oRecord->End, $oRecord->Delivery, $oRecord->Status);
  $oTabInfo->CoopTotal = $oRecord->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oRecord->CoopOrderMaxBurden, $oRecord->CoopOrderBurden, $oRecord->CoopOrderMaxCoopTotal, $oRecord->CoopOrderCoopTotal);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
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
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function Delete()
{
  var sMessage = 'נא אשר/י או בטל/י את פעולת המחיקה';
  
  <?php
    if ($oRecord->IsExistingRecord && $oRecord->ProductCoopTotal > 0)
    {
      
    ?>
    sMessage = "<?php echo sprintf('פעולה זו תמחק גם את כל ההזמנות על סך %s שנעשו עבור מוצר זה. נא אשר/י', $oRecord->ProductCoopTotal); ?>";
    <?php
    
    }
    ?>
  
  if (confirm(decodeXml(sMessage)))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
//change product list
function SelectProducer()
{
  document.getElementById("hidPostAction").value = <?php echo CoopOrderProduct::POST_ACTION_SELECT_PRODUCER; ?>;
  document.frmMain.submit();
}
//when selecting a product, get its default values
function SelectProduct()
{
  document.getElementById("hidPostAction").value = <?php echo CoopOrderProduct::POST_ACTION_SELECT_PRODUCT; ?>;
  document.frmMain.submit();
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="908"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
            <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td width="780" >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                  <td><?php include_once '../control/coopordertab.php'; ?></td>
                </tr>
                <tr>
                  <td><?php include_once '../control/error/ctlError.php'; ?></td>
                </tr>
                <tr>
                  <td>
                    <button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save" 
                  <?php if ($g_oError->HadError || $bReadOnly ) echo ' disabled="disabled" '; ?>>שמירה</button>&nbsp;<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete" <?php 
                      if ($g_oError->HadError || !$oRecord->IsExistingRecord || $bReadOnly ) 
                        echo ' disabled="disabled" '; 
                      ?> >מחיקה</button>
                  </td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <td></td>
                <?php
                  HtmlTextEditMultiLang::OtherLangsEmptyCells();
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                <tr>
                  <?php
                    
                    if ($oRecord->IsExistingRecord || $bReadOnly)
                    {
                      $lblProducerName = new HtmlTextLabel('יצרן', 'txtProducerName', $oRecord->ProducerName);
                      $lblProducerName->EchoHtml();
                      unset($lblProducerName);
                    }
                    else //new record - allow select
                    {
                      $selProducer = new HtmlSelectPDO('יצרן', $recProducers, $oProducers,
                            $oRecord->ProducerID, 'sProducer', 'ProducerKeyID');
                      $selProducer->Required = TRUE;
                      $selProducer->OnChange = "JavaScript:SelectProducer();";
                      $selProducer->EchoHtml();
                      unset($selProducer);
                    }
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php
                    
                    if ($oRecord->IsExistingRecord || $bReadOnly)
                    {
                      $lblProductName = new HtmlTextLabel('מוצר', 'txtProductName', $oRecord->ProductName);

                      $sCommand = "JavaScript:OpenProductOverview('" . $g_sRootRelativePath . "', " .
                              $oRecord->CoopOrderID . "," . $oRecord->ProductID . ");";
                      
                      $lblProductName->SetAttribute('onclick', $sCommand);
                      $lblProductName->SetAttribute('class', 'link');
                      $lblProductName->EchoHtml();
                      unset($lblProductName);
                    }
                    else //new record - allow select
                    {
                      $selProduct = new HtmlSelectArray('ProductKeyID', 'מוצר', $arrProducts, $oRecord->ProductID);
                      $selProduct->Required = TRUE;
                      $selProduct->OnChange = "JavaScript:SelectProduct();";
                      $selProduct->EchoHtml();
                      unset($selProduct);
                    }
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr> 
                <tr>
                  <?php
                      $oProductPackage = new ProductPackage($oRecord->Items, $oRecord->ItemQuantity, 
                                $oRecord->ItemUnitAbbrev, $oRecord->UnitInterval, $oRecord->UnitAbbrev, $oRecord->PackageSize, 
                                $oRecord->Quantity, $oRecord->MaxCoopOrder, $oRecord->TotalCoopOrder);
                      
                      $lblQuantity = new HtmlTextLabel('כמות', 'lblQuantity', $oProductPackage->Html);
                      $lblQuantity->UseHtmlEscape = FALSE; //already escaped in ProductPackage
                      $lblQuantity->EchoHtml();
                      unset($lblQuantity);
                  ?>
                </tr>
                <tr>
                  <?php                      
                     $txtProducerPrice = new HtmlTextEditNumeric('מחיר יצרן', 'txtProducerPrice', $oRecord->ProducerPrice);
                     $txtProducerPrice->ReadOnly = $bReadOnly || $oRecord->TotalCoopOrder > 0;
                     $txtProducerPrice->EchoHtml();
                     unset($txtProducerPrice);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <tr>
                  <?php                      
                     $txtCoopPrice = new HtmlTextEditNumeric('מחיר קואופרטיב', 'txtCoopPrice', $oRecord->CoopPrice);
                     $txtCoopPrice->ReadOnly = $bReadOnly || $oRecord->TotalCoopOrder > 0;
                     $txtCoopPrice->EchoHtml();
                     unset($txtCoopPrice);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php                      
                     $txtBurden = new HtmlTextEditNumeric('מעמסה', 'txtBurden', $oRecord->Burden);
                     $txtBurden->ReadOnly = $bReadOnly;
                     $txtBurden->EchoHtml();
                     unset($txtBurden);
                     
                     HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                <tr>
                  <?php                      
                     $txtMaxUserOrder = new HtmlTextEditNumeric('מכסת הזמנה לחבר/ה', 'txtMaxUserOrder', $oRecord->MaxUserOrder);
                     $txtMaxUserOrder->ReadOnly = $bReadOnly;
                     $txtMaxUserOrder->EchoHtml();
                     unset($txtMaxUserOrder);
                     
                     HtmlTextEditMultiLang::EchoHelpText('מקסימום כמות מהמוצר לכל חבר/ה');
                  ?>
                </tr>
                <tr>
                  <?php                      
                     $txtMaxCoopOrder = new HtmlTextEditNumeric('מכסת הזמנה לקואופ', 'txtMaxCoopOrder', $oRecord->MaxCoopOrder);
                     $txtMaxCoopOrder->ReadOnly = $bReadOnly;
                     $txtMaxCoopOrder->EchoHtml();
                     unset($txtMaxCoopOrder);
                     
                     HtmlTextEditMultiLang::EchoHelpText('מקסימום כמות מהמוצר לכל הזמנת הקואופרטיב');
                  ?>
                </tr>
                <tr>
                  <?php
                      $lblTotalCoopOrder = new HtmlTextLabel('סה&quot;כ', 'lblTotalCoopOrder', 
                              $oRecord->TotalCoopOrder );
                      $lblTotalCoopOrder->EchoHtml();
                      unset($lblTotalCoopOrder);
                  ?>
                </tr>
                <tr>
                  <?php
                      $lblProducerTotal = new HtmlTextLabel('סה&quot;כ ליצרן', 'lblProducerTotal', 
                              $oRecord->ProducerTotal );
                      $lblProducerTotal->EchoHtml();
                      unset($lblProducerTotal);
                  ?>
                </tr>
                
                <tr>
                  <?php                                       
                    $txtCoopTotal = new HtmlTextLabel('סכום לקואופ', 'txtCoopTotal', 
                            $oRecord->ProductCoopTotal);
                    $txtCoopTotal->EchoHtml();
                    unset($txtCoopTotal);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                  ?>
                </tr>
                
                <?php
                  if ( $oRecord->JoinedStatus != CoopOrderProduct::JOIN_STATUS_NONE )
                  {
                    echo '<tr><td colspan="2">';
                    if ( $oRecord->JoinedStatus == CoopOrderProduct::JOIN_STATUS_JOINED )
                      echo 'לפחות חלק מהכמות של מוצר זה צורפה למוצר מקושר';
                    else
                      echo 'לכמות של המוצר הזה צורפה כמות ממוצר מקושר';
                    echo '</td>';
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells();
                    
                    echo '</tr>';
                  }
                ?>
                </table>
                </td></tr></table>
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
