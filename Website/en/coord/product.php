<?php

include_once '../settings.php';
include_once '../authenticate.php';

$sPageTitle = 'New Product';
$oRecord = new Product;
$oProducers = new Producers;
$oProducts = new Products;
$recProducers = NULL;
$arrUnits = NULL;
$arrJoinToProductList = NULL;
$bReadOnly = TRUE;
try
{
  if (!$oRecord->CheckAccess())
  {
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }
  
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {
    if ( isset( $_POST['hidPostValue'] ) && !empty($_POST['hidPostValue']) )
      $oRecord->ID = intval($_POST['hidPostValue']);
    
    if ( isset( $_POST['hidOriginalData'] ) )
      $oRecord->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case SQLBase::POST_ACTION_SAVE:
          
          $oRecord->ProductNames = ComplexPostData::GetNames('txtProduct');
          
          $sCtl = HtmlSelectPDO::PREFIX . 'ProducerKeyID';
          if ( isset( $_POST[$sCtl] ))
            $oRecord->ProducerID = intval($_POST[$sCtl]);
          
          $oRecord->SpecStrings = ComplexPostData::GetNames('txtSpec');
          
          $sCtl = HtmlSelectArray::PREFIX . 'UnitKeyID';
          if ( isset( $_POST[$sCtl] ))
            $oRecord->UnitID = intval($_POST[$sCtl]);
          
          $oRecord->UnitInterval = NULL;
          if ( isset( $_POST['txtUnitInterval'] ) && !empty($_POST['txtUnitInterval']))
            $oRecord->UnitInterval = 0 + trim($_POST['txtUnitInterval']);
          
          $oRecord->MaxUserOrder = NULL;
          if ( isset( $_POST['txtMaxUserOrder'] ) && !empty($_POST['txtMaxUserOrder']))
            $oRecord->MaxUserOrder = 0 + trim($_POST['txtMaxUserOrder']);
          
          $oRecord->ItemUnitID = NULL;
          $sCtl = HtmlSelectArray::PREFIX . 'ItemUnitKeyID';
          if ( isset( $_POST[$sCtl] ) && intval($_POST[$sCtl]) > 0 )
            $oRecord->ItemUnitID = intval($_POST[$sCtl]);
          
          $oRecord->JoinToProductID = NULL;
          $sCtl = HtmlSelectArray::PREFIX . 'JoinToProductID';
          if ( isset( $_POST[$sCtl] ) && intval($_POST[$sCtl]) > 0 )
            $oRecord->JoinToProductID = intval($_POST[$sCtl]);
          
          $oRecord->ItemQuantity = NULL;
          if ( isset( $_POST['txtItemQuantity'] ) && !empty($_POST['txtItemQuantity']))
            $oRecord->ItemQuantity = 0 + trim($_POST['txtItemQuantity']);
          
          $oRecord->SortOrder = NULL;
          if ( isset( $_POST['txtSortOrder'] ) && !empty($_POST['txtSortOrder']))
            $oRecord->SortOrder = 0 + trim($_POST['txtSortOrder']);
                    
          $oRecord->PackageSize = NULL;
          if ( isset( $_POST['txtPackageSize'] ) && !empty($_POST['txtPackageSize']))
            $oRecord->PackageSize = 0 + trim($_POST['txtPackageSize']);
          
          if ( isset( $_POST['txtProducerPrice'] ))
            $oRecord->ProducerPrice = 0 + trim($_POST['txtProducerPrice']);
          
          if ( isset( $_POST['txtQuantity'] ))
            $oRecord->Quantity = 0 + trim($_POST['txtQuantity']);
          
          if ( isset( $_POST['txtItems'] ))
            $oRecord->Items = intval($_POST['txtItems']);
          
          if ( isset( $_POST['txtProducerPrice'] ))
            $oRecord->ProducerPrice = 0 + trim($_POST['txtProducerPrice']);
          
          if ( isset( $_POST['txtCoopPrice'] ))
            $oRecord->CoopPrice = 0 + trim($_POST['txtCoopPrice']);
          
          if ( isset( $_POST['txtBurden'] ))
            $oRecord->Burden = 0 + trim($_POST['txtBurden']);         

          if ( isset( $_POST['ctlIsDisabled'] ))
            $oRecord->IsDisabled = (intval($_POST['ctlIsDisabled']) == 1);
          
          if ( isset($_FILES['ctlPicUpload1']))
            $oRecord->Image1File = 'ctlPicUpload1';
          else if (isset($_POST['ctlPicUpload1']))
          {
            $sErrPicUpload1 = sprintf('Upload file %s failed.', $_POST['ctlPicUpload1'] );
            $g_oError->AddError($sErrPicUpload1, 'warning');
          }

          if ( isset($_FILES['ctlPicUpload2']))
            $oRecord->Image2File = 'ctlPicUpload2';
          else if (isset($_POST['ctlPicUpload2']))
          {
            $sErrPicUpload2 = sprintf('Upload file %s failed.', $_POST['ctlPicUpload2'] );
            $g_oError->AddError($sErrPicUpload2, 'warning');
          }
          
          if ( isset($_POST['txtPic1FileName']))
            $oRecord->Image1FileName = $_POST['txtPic1FileName'];
           
          if ( isset($_POST['txtPic2FileName']))
            $oRecord->Image2FileName = $_POST['txtPic2FileName'];
          
          if (isset($_POST['chkRemoveImage1']))
            $oRecord->Image1Remove = ($_POST['chkRemoveImage1'] == 1); //checked
          
          if (isset($_POST['chkRemoveImage2']))
            $oRecord->Image2Remove = ($_POST['chkRemoveImage2'] == 1); //checked

          $bSuccess = false;
          $bAdd = FALSE;
          if ($oRecord->ID > 0)
            $bSuccess = $oRecord->Edit();
          else
          {
            $bSuccess = $oRecord->Add();
            $bAdd = TRUE;
          }

          if ( $bSuccess )
          {
              $g_oError->AddError('Record saved successfully.', 'ok');
              $sPageTitle = $oRecord->ProductName;
              //reload record after add, to load coordinating group from selected producer
              if ($bAdd)
                $oRecord->LoadRecord($oRecord->ID);
          }
          else if ($oRecord->LastOperationStatus != SQLBase::OPERATION_STATUS_VALIDATION_FAILED)
              $g_oError->AddError('Record was not saved. You may not have sufficent permissions or an error has occured.');
          break;
        case SQLBase::POST_ACTION_DELETE:
          $bSuccess = $oRecord->Delete();
          if ( $bSuccess )
          {
              //redirect to grid
              RedirectPage::To('products.php');
              exit;
          }
          else
              $g_oError->AddError('The record was not deleted.');
          
          break;
      }
    }
  }
  else if (isset($_GET['id']))
  {
    if(!$oRecord->LoadRecord(intval($_GET['id'])))
    {
        RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
        exit;
    }

    $sPageTitle = $oRecord->ProductName;
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
  
  $recProducers = $oProducers->GetTable();
  $arrUnits = $oRecord->GetUnits();
  $arrJoinToProductList = $oProducts->GetJoinToProductList($oRecord->ID);
  
  $bReadOnly = !$oRecord->HasPermission(Product::PERMISSION_EDIT);
  
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
<title>Enter Your Cooperative Name: <?php echo $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function Delete()
{
  if (confirm(decodeXml('Please confirm or cancel the delete operation')))
  {
    document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_DELETE; ?>;
    document.frmMain.submit();
  }
}
function Save()
{
  document.getElementById("hidPostAction").value = <?php echo SQLBase::POST_ACTION_SAVE; ?>;
}
function OnChangeUnit()
{
  var ctlUnit = document.getElementById("selUnitKeyID");
  var ctlQuantity = document.getElementById("txtQuantity");
  var nItemsValue = <?php echo Consts::UNIT_ITEMS; ?>;
  if (ctlUnit.options[ctlUnit.selectedIndex].value == nItemsValue )
  {
    //set quantity to 1 and lock it
    ctlQuantity.value = 1;
    ctlQuantity.disabled = true;
  }
  else //unlock quantity
  {
    ctlQuantity.disabled = false;
  }
}
</script>
</head>
<body class="centered">
<form id="frmMain" name="frmMain" method="post" enctype="multipart/form-data">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oRecord->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oRecord->ID; ?>" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>
    <tr>
        <td >
                <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                <td><?php 
                  include_once '../control/error/ctlError.php';
                ?></td>
                </tr>
                <tr>
                  <td>
                    <?php
                  if (!$bReadOnly && !$g_oError->HadError)
                  {
                    echo '<a href="product.php" ><img border="0" title="Add" src="../img/edit-add-2.png" /></a>&nbsp;';
                    echo '<button type="submit" onclick="JavaScript:Save();" id="btn_save" name="btn_save">Save</button>&nbsp;';

                    if ($oRecord->ID > 0)
                    {
                     echo '<button type="button" onclick="JavaScript:Delete();" id="btnDelete" name="btnDelete">Delete</button>'; 
                    } 
                  }
                  ?>
                  </td>
                </tr>
                <tr><td>
                <table cellspacing="0" cellpadding="2" width="100%">
                <tr>
                <td></td>
                <?php
                  HtmlTextEditMultiLang::EchoColumnHeaders();
                ?>
                <td width="100%">&nbsp;</td>
                </tr>
                <tr>
                <?php
                                                
                $txtProduct = new HtmlTextEditMultiLang('Product Name', 'txtProduct', HtmlTextEdit::TEXTBOX, 
                        $oRecord->ProductNames);
                $txtProduct->Required = TRUE;
                $txtProduct->ReadOnly =  $bReadOnly;
                $txtProduct->EchoHtml();
                unset($txtProduct);
                
                ?>
                <td></td>
                </tr>
                <tr>
                  <?php                    
                    $selProducer = new HtmlSelectPDO('Producer', $recProducers, $oProducers,
                          $oRecord->ProducerID, 'sProducer', 'ProducerKeyID');
                    $selProducer->Required = TRUE;
                    $selProducer->ReadOnly =  $bReadOnly;
                    $selProducer->EchoHtml();
                    unset($selProducer);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                <?php
                                
                $txtSpec = new HtmlTextEditMultiLang('Details', 'txtSpec', HtmlTextEdit::TEXTAREA, 
                        $oRecord->SpecStrings);
                $txtSpec->ReadOnly =  $bReadOnly;
                $txtSpec->EchoHtml();
                unset($txtSpec);
                
                
                ?>
                <td></td>
                </tr>
                <tr>
                  <?php                    
                    $selUnit = new HtmlSelectArray('UnitKeyID', 'Unit', $arrUnits, $oRecord->UnitID);
                    $selUnit->Required = TRUE;
                    $selUnit->ReadOnly =  $bReadOnly;
                    $selUnit->OnChange = "JavaScript:OnChangeUnit();";
                    $selUnit->EchoHtml();
                    unset($selUnit);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                     
                    $txtQuantity = new HtmlTextEditNumeric('Quantity', 'txtQuantity', $oRecord->Quantity);
                    $txtQuantity->Required = TRUE;
                    if ($bReadOnly || $oRecord->UnitID == Consts::UNIT_ITEMS)
                      $txtQuantity->ReadOnly = TRUE;
                    $txtQuantity->EchoHtml();
                    unset($txtQuantity);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php                     
                    $txtUnitInterval = new HtmlTextEditNumeric('Unit Interval', 'txtUnitInterval', $oRecord->UnitInterval);
                    $txtUnitInterval->ReadOnly =  $bReadOnly;
                    $txtUnitInterval->EchoHtml();
                    unset($txtUnitInterval);
                    
                    HtmlTextEditMultiLang::EchoHelpText('Interval between orderable weights/capacities. Irrelevant for products provided by item quantity', 'UnitInterval');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtProducerPrice = new HtmlTextEditNumeric('Producer Price', 'txtProducerPrice', $oRecord->ProducerPrice);
                    $txtProducerPrice->Required = TRUE;
                    $txtProducerPrice->ReadOnly =  $bReadOnly;
                    $txtProducerPrice->EchoHtml();
                    unset($txtProducerPrice);
                    
                    HtmlTextEditMultiLang::EchoHelpText('This is the default price for the product. The actual product price can be set to a different value in each cooperative order', 'ProducerPrice');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtCoopPrice = new HtmlTextEditNumeric('Coop. Price', 'txtCoopPrice', $oRecord->CoopPrice);
                    $txtCoopPrice->Required = TRUE;
                    $txtCoopPrice->ReadOnly =  $bReadOnly;
                    $txtCoopPrice->EchoHtml();
                    unset($txtCoopPrice);
                    
                    HtmlTextEditMultiLang::EchoHelpText('This is the default price for the product. The actual product price can be set to a different value in each cooperative order', 'CoopPrice');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtSortOrder = new HtmlTextEditNumeric('Sorting Order', 'txtSortOrder', $oRecord->SortOrder);
                    $txtSortOrder->ReadOnly =  $bReadOnly;
                    $txtSortOrder->EchoHtml();
                    unset($txtSortOrder);
                    
                    HtmlTextEditMultiLang::EchoHelpText('A number representing the sorting order of the product wherever a products list is displayed. Smaller numbers display higher on the list.', 'SortOrder');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtPackageSize = new HtmlTextEditNumeric('Package Size', 'txtPackageSize', $oRecord->PackageSize);
                    $txtPackageSize->ReadOnly =  $bReadOnly;
                    $txtPackageSize->EchoHtml();
                    unset($txtPackageSize);
                    
                    HtmlTextEditMultiLang::EchoHelpText('Package size for a large package that the cooperative allows ordering portions/slices of.', 'PackageSize');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php 
                    $txtBurden = new HtmlTextEditNumeric('Burden', 'txtBurden', $oRecord->Burden);
                    $txtBurden->Required = TRUE;
                    $txtBurden->ReadOnly =  $bReadOnly;
                    $txtBurden->EchoHtml();
                    unset($txtBurden);
                    
                    HtmlTextEditMultiLang::EchoHelpText('An index that indicates how much this product &quot;burdens&quot; a delivery. Helps to uphold a cooperative order&#x27;s Delivery Capacity, if set', 'Burden');
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtItems = new HtmlTextEditNumeric('Items in Package', 'txtItems', $oRecord->Items);
                    $txtItems->Required = TRUE;
                    $txtItems->ReadOnly =  $bReadOnly;
                    $txtItems->EchoHtml();
                    unset($txtItems);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                 <tr>
                  <?php
                    $selItemUnit = new HtmlSelectArray('ItemUnitKeyID', 'Item Unit', $arrUnits, $oRecord->ItemUnitID);
                    $selItemUnit->ReadOnly =  $bReadOnly;
                    $selItemUnit->EchoHtml();
                    unset($selItemUnit);
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                   <td></td>
                </tr>
                <tr>
                  <?php                     
                    $txtItemQuantity = new HtmlTextEditNumeric('Item Quantity', 'txtItemQuantity', $oRecord->ItemQuantity);
                    $txtItemQuantity->ReadOnly =  $bReadOnly;
                    $txtItemQuantity->EchoHtml();
                    unset($txtItemQuantity);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                <tr>
                  <?php
                    $selItemUnit = new HtmlSelectArray('JoinToProductID', 'Join to Product', $arrJoinToProductList, $oRecord->JoinToProductID);
                    $selItemUnit->ReadOnly =  $bReadOnly;
                    $selItemUnit->EchoHtml();
                    unset($selItemUnit);
                    HtmlTextEditMultiLang::EchoHelpText('Join Items to Cost-Saving Larger Product', 'JoinToProductID'); 
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                     
                    $txtMaxUserOrder = new HtmlTextEditNumeric('Max. Order', 'txtMaxUserOrder', $oRecord->MaxUserOrder);
                    $txtMaxUserOrder->ReadOnly =  $bReadOnly;
                    $txtMaxUserOrder->EchoHtml();
                    unset($txtMaxUserOrder);
                    
                    HtmlTextEditMultiLang::EchoHelpText('Max. product quantity per member', 'MaxUserOrder'); 
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                </tr>
                <tr>
                  <?php                    
                    $oIsDisabled = new HtmlSelectBoolean('ctlIsDisabled', 'Status', $oRecord->IsDisabled, 'Inactive', 
                            'Active');
                    $oIsDisabled->ReadOnly =  $bReadOnly;
                    $oIsDisabled->EchoHtml();
                    unset($oIsDisabled);
                    
                    HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                  ?>
                  <td></td>
                </tr>
                  <?php   
                    if ($oRecord->CheckImageUploadsPermission())
                    {
                      echo '<tr>';
                      $oPic1Upload = new HtmlFileUploader('ctlPicUpload1', 'Picture 1', PRODUCT_IMAGE_MAX_FILE_SIZE);
                      $oPic1Upload->ReadOnly =  $bReadOnly;
                      $oPic1Upload->EchoHtml();
                      unset($oPic1Upload);

                      HtmlTextEditMultiLang::EchoHelpText(sprintf('Supported formats: jpg, gif, png. Max. size: %sKB', (PRODUCT_IMAGE_MAX_FILE_SIZE/1024))
                          , 'PicUpload1'); 
                      HtmlTextEditMultiLang::OtherLangsEmptyCells(); 

                      echo '</tr>';
                      
                      echo '<tr>';
                      $oPic1FileName = new HtmlTextEditOneLang('File Name', 'txtPic1FileName', $oRecord->Image1FileName);
                      $oPic1FileName->MaxLength = Producer::MAX_LENGTH_EXPORT_FILE_NAME;
                      $oPic1FileName->ReadOnly = $bReadOnly;
                      $oPic1FileName->EchoHtml();
                      unset($oPic1FileName);

                      HtmlTextEditMultiLang::EchoHelpText(sprintf('When uploading this field is not mandatory and the system can set it automatically. It is included in cases when you do not have permissions to upload files and want to correlate products with already uploaded image files. Image files must then be uploaded to the upload directory: %s', 
                          realpath($g_sRootRelativePath . URL_UPLOAD_DIR)  ), 'Pic1FileName'); 
                      HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                      echo '</tr>';
                      
                      if ($oRecord->Image1FileName != NULL)
                      {
                        echo '<tr><td colspan="2"><input type="checkbox" name="chkRemoveImage1" id="chkRemoveImage1" value="1" >Remove Picture',
                             '</input></td>';
                        HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                        echo '<td></td></tr>';
                        
                        echo '<tr><td colspan="2"><img border="0" height="', PRODUCT_IMAGE_HEIGHT_SMALL,
                                '" src="../', URL_UPLOAD_DIR, $oRecord->Image1FileName, '" /></td>';
                        HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                        echo '<td></td></tr>';
                      }
                      
                      echo '<tr>';
                      
                      $oPic2Upload = new HtmlFileUploader('ctlPicUpload2', 'Picture 2', 0); //0 - don't create two max file size elements
                      $oPic2Upload->ReadOnly =  $bReadOnly;
                      $oPic2Upload->EchoHtml();
                      unset($oPic2Upload);

                      HtmlTextEditMultiLang::EchoHelpText(sprintf('Supported formats: jpg, gif, png. Max. size: %sKB', (PRODUCT_IMAGE_MAX_FILE_SIZE/1024)), 
                          'PicUpload2'); 
                      HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                      echo '</tr>';
                      
                      echo '<tr>';
                      $oPic2FileName = new HtmlTextEditOneLang('File Name', 'txtPic2FileName', $oRecord->Image2FileName);
                      $oPic2FileName->MaxLength = Producer::MAX_LENGTH_EXPORT_FILE_NAME;
                      $oPic2FileName->ReadOnly = $bReadOnly;
                      $oPic2FileName->EchoHtml();
                      unset($oPic2FileName);
                      
                      HtmlTextEditMultiLang::EchoHelpText(sprintf('When uploading this field is not mandatory and the system can set it automatically. It is included in cases when you do not have permissions to upload files and want to correlate products with already uploaded image files. Image files must then be uploaded to the upload directory: %s', 
                          realpath($g_sRootRelativePath . URL_UPLOAD_DIR)  ), 
                          'Pic2FileName'); 
                      HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                      echo '</tr>';
                      
                      if ($oRecord->Image2FileName != NULL)
                      {
                        echo '<tr><td colspan="2"><input type="checkbox" name="chkRemoveImage2" id="chkRemoveImage2" value="1" >Remove Picture',
                             '</input></td>';
                        HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                        echo '<td></td></tr>';
                        
                        echo '<tr><td colspan="2"><img border="0" height="', PRODUCT_IMAGE_HEIGHT_SMALL,
                                '" src="../', URL_UPLOAD_DIR, $oRecord->Image2FileName, '" /></td>';
                        HtmlTextEditMultiLang::OtherLangsEmptyCells(); 
                        echo '<td></td></tr>';
                      }
                    }
                  ?>
                </table>
                </td></tr></table>
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
