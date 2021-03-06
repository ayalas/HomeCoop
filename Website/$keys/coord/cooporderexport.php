<?php

include_once '../settings.php';
include_once '../authenticate.php';

$oData = new CoopOrderExport;
$arrList = NULL;
$oTabInfo = new CoopOrderTabInfo;
$oTabInfo->Page = CoopOrderTabInfo::PAGE_EXPORT_DATA;
$sOnLoadScript = NULL;
$sMailList = NULL;
$sMailListDir = 'ltr';
$bDisplayProducts = FALSE;
$sPageTitle = '<!$TAB_ORDER_EXPORT_DATA$!>';
$arrProducts = NULL;

function GetMailingList()
{
  global $sMailList;
  global $sMailListDir;
  global $oData;
  $sMailList = $oData->GetMailingList();
  if ($sMailList == NULL)
  {
    $sMailList = '<!$ORDER_EXPORT_DATA_MAIL_LIST_IS_EMPTY$!>';
    $sMailListDir = NULL; //because displayed in current langugae
  }
}

function ShowProducts()
{
  global $bDisplayProducts;
  global $oData;
  global $arrProducts;
  
  $bDisplayProducts = TRUE;

  $arrProducts= $oData->GetProductList();
}

try
{
  if ( $_SERVER[ 'REQUEST_METHOD'] == 'POST' )
  {   
    if ( isset( $_POST['hidOriginalData'] ) )
      $oData->SetSerializedOriginalData( $_POST["hidOriginalData"] );
    
    $oData->CopyData();
    
    $arrList = $oData->DataSetList;
    
    $sCtl = HtmlSelectArray::PREFIX . 'DataSet';
    if ( isset( $_POST[$sCtl] ))
      $oData->ID = intval($_POST[$sCtl]);  
    
    $sCtl = HtmlSelectArray::PREFIX . 'FileFormat';
    if ( isset( $_POST[$sCtl] ))
    {
      $oData->ExportFormat = intval($_POST[$sCtl]);
      $oData->SaveExportFormat();
    }
    
    if (!empty( $_POST['hidPostAction'] ))
    {
      switch($_POST['hidPostAction'])
      {
        case CoopOrderExport::POST_ACTION_LIST_SELECT: 
          if ( ($oData->ID & CoopOrderExport::LIST_ITEM_TYPE_MAILS) === CoopOrderExport::LIST_ITEM_TYPE_MAILS )
          {
            if ( ($oData->ID & CoopOrderExport::LIST_ITEM_PRODUCTS) == CoopOrderExport::LIST_ITEM_PRODUCTS)
              ShowProducts();
            else
              GetMailingList();
          }
          else
          {
            $sOnLoadScript = 'window.open("cooporderexportres.php?coid=' . $oData->CoopOrderID . '&id=' . $oData->ID . 
             '","_blank", "status=0,toolbar=0,menubar=0,top=150, left=100, width=400,height=400" );';
          }
          break;
        case CoopOrderExport::POST_ACTION_DISPLAY_PRODUCTS_MAILS:
          ShowProducts();
          if (!isset($_POST["products"]))
            $g_oError->AddError('<!$ORDER_EXPORT_NO_PRODUCTS_SELECTED$!>');
          else
          {
            $oData->ProductIDs = implode(",", $_POST["products"]);
            GetMailingList();
          }
         break;
      }
    }
  }
  else
  {
    if (isset($_GET['coid']))
      $oData->CoopOrderID = intval($_GET['coid']);
    $arrList = $oData->GetDataSetsList();
  }

  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  $oTabInfo->ID = $oData->CoopOrderID;
  $oTabInfo->Status = $oData->Status;
  $oTabInfo->CoopOrderTitle = $oData->Name;
  $oTabInfo->CoordinatingGroupID = $oData->CoordinatingGroupID;
  $oTabInfo->StatusObj = new ActiveCoopOrderStatus($oData->End, $oData->Delivery, $oData->Status);
  $oTabInfo->CoopTotal = $oData->CoopOrderCoopTotal; 
  $oPercent = new CoopOrderCapacity($oData->CoopOrderMaxBurden, $oData->CoopOrderBurden, $oData->CoopOrderMaxCoopTotal, $oData->CoopOrderCoopTotal,
      $oData->CoopOrderMaxStorageBurden, $oData->CoopOrderStorageBurden);
  if ($oPercent->SelectedType != CoopOrderCapacity::TypeNone)
    $oTabInfo->Capacity = $oPercent->PercentRounded . '%';
  unset($oPercent);
  $sPageTitle = $oData->Name . '<!$PAGE_TITLE_SEPARATOR$!><!$TAB_ORDER_EXPORT_DATA$!>';

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
<title><!$COOPERATIVE_NAME$!>: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" >
function ListSelect()
{
  if (document.getElementById("selDataSet").value == 0)
    return;
  
  document.getElementById("hidPostAction").value = <?php echo CoopOrderExport::POST_ACTION_LIST_SELECT; ?>;
  document.frmMain.submit();
}
function DisplayProducts()
{
  document.getElementById("hidPostAction").value = <?php echo CoopOrderExport::POST_ACTION_DISPLAY_PRODUCTS_MAILS; ?>;
  document.frmMain.submit();
}
function SelectAll(bCheck)
{
  var arrInputs = document.getElementsByTagName('input');
    // loop through all collected objects
    for (i = 0; i < arrInputs.length; i++) {
        if (arrInputs[i].type === 'checkbox' && arrInputs[i].name.indexOf('products') == 0) 
            arrInputs[i].checked = bCheck;
    }
}
</script>
</head>
<body class="centered">
<?php
  if ($sOnLoadScript != NULL)
  {
    echo '<script type="text/javascript" >',
     $sOnLoadScript,
     '</script>';
  }
?>
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="" />
<?php include_once '../control/header.php'; ?>
<table cellspacing="0" cellpadding="0">
    <tr>
        <td class="fullwidth"><span class="pagename"><?php echo $sPageTitle; ?></span></td>
    </tr>    
    <tr>
        <td>
              <table cellspacing="0" cellpadding="2" width="100%">
              <tr>
                <td colspan="4"><?php include_once '../control/coopordertab.php'; ?></td>
              </tr>
              <tr>
                <td colspan="4"><?php include_once '../control/error/ctlError.php'; ?></td>
              </tr>
              <tr>
                <?php
                  $arrFormats = array(Consts::EXPORT_FORMAT_MS_EXCEL_XML => '<!$EXPORT_FORMAT_MS_EXCEL_XML$!>',
                                      Consts::EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS => '<!$EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS$!>');
                  $formatList = new HtmlSelectArray('FileFormat', '<!$LBL_EXPORT_FORMAT$!>',$arrFormats, $oData->ExportFormat
                      );
                  $formatList->EncodeHtml = FALSE; //already encoded
                  $formatList->Required = TRUE;
                  $formatList->EchoHtml();
                ?>
              </tr>
              <tr>
                  <?php
                  $selList = new HtmlSelectArray('DataSet', '<!$FIELD_ORDER_EXPORT_DATASET$!>', $arrList, $oData->ID);
                  $selList->EncodeHtml = FALSE; //already encoded (from strings files)
                  $selList->Required = TRUE;
                  $selList->OnChange = "JavaScript:ListSelect();";
                  $selList->EchoHtml();
                  ?>
                  <td nowrap>
                    <?php 
                      if ($bDisplayProducts)
                      {
                        echo '<button type="button" onclick="JavaScript:DisplayProducts();" id="btnProducts" name="btnProducts">' ,
                              '<!$BTN_ORDER_EXPORT_DISPLAY_PRODUCTS_MAIL_LIST$!></button>';
                      }
                    ?>
                  </td>
                  <td width="100%"></td>
                </tr>
                <?php
                  if ($bDisplayProducts)
                  {
                    if (!is_array($arrProducts) || count($arrProducts) == 0)
                    {
                      echo '<tr><td colspan="4"><!$NO_RECORD_FOUND$!></td></tr>';
                    }
                    else
                    {
                      $nCount = 0;
                      echo '<tr><td colspan="4"><span class="link" onclick="JavaScript:SelectAll(true);"><!$SELECT_ALL$!></span>&nbsp;',
                        '<span class="link" onclick="JavaScript:SelectAll(false);"><!$DESELECT_ALL$!></span></td></tr>';
                      echo '<tr><td colspan="4"><table cellspacing="0" cellpadding="0" width="100%">';
                      foreach($arrProducts as $ProductID => $ProductName)
                      {
                        if (($nCount % (Consts::ORDER_EXPORT_PRODUCTS_LIST_COLUMNS * Consts::ORDER_EXPORT_PRODUCTS_LIST_GROUP_LENGTH)) == 0)
                          echo '<tr>';
                        if (($nCount % Consts::ORDER_EXPORT_PRODUCTS_LIST_GROUP_LENGTH) == 0)
                          echo '<td nowrap><ul>';

                        echo '<li><input type="checkbox" name="products[]" value="' , $ProductID , '"'; 

                        //restore is checked
                        if (isset($_POST["products"]))
                        {
                          if (in_array($ProductID, $_POST["products"]))
                            echo ' checked ';
                        }

                        echo '>' , $ProductName , '</input></li>';

                        $nCount++;

                        if (($nCount % Consts::ORDER_EXPORT_PRODUCTS_LIST_GROUP_LENGTH) == 0)
                          echo '</ul></td>';
                        if (($nCount % (Consts::ORDER_EXPORT_PRODUCTS_LIST_COLUMNS * Consts::ORDER_EXPORT_PRODUCTS_LIST_GROUP_LENGTH)) == 0)
                          echo '</tr>';
                      }
                      echo '</table></td></tr>';
                    }
                  }
                  if ($sMailList != NULL)
                  {
                    echo '<tr><td colspan="4">';
                      $txtMailList = new HtmlTextEdit('MailList', $sMailListDir, HtmlTextEdit::TEXTAREA, $sMailList);
                      $txtMailList->CssClass = "mailinglist";
                      $txtMailList->EncloseInHtmlCell = FALSE;
                      $txtMailList->EchoEditPartHtml();

                    echo '</td></tr>';
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
