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
$sPageTitle = 'יצוא נתונים';
$arrProducts = NULL;

function GetMailingList()
{
  global $sMailList;
  global $sMailListDir;
  global $oData;
  $sMailList = $oData->GetMailingList();
  if ($sMailList == NULL)
  {
    $sMailList = 'אין כתובות מייל.';
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
            $g_oError->AddError('לא נבחרו מוצרים.');
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
  $sPageTitle = $oData->Name . ' - יצוא נתונים';

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
<title>הזינו את שם הקואופרטיב שלכם: <?php echo $sPageTitle; ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
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
        <td width="908"><span class="coopname">הזינו את שם הקואופרטיב שלכם:&nbsp;</span><span class="pagename"><?php echo $sPageTitle; ?></span></td>
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
                  $arrFormats = array(Consts::EXPORT_FORMAT_MS_EXCEL_XML => 'MS Excel xml',
                                      Consts::EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS => 'Libre Office flat ods');
                  $formatList = new HtmlSelectArray('FileFormat', 'תבנית קובץ',$arrFormats, $oData->ExportFormat
                      );
                  $formatList->EncodeHtml = FALSE; //already encoded
                  $formatList->Required = TRUE;
                  $formatList->EchoHtml();
                ?>
              </tr>
              <tr>
                  <?php
                  $selList = new HtmlSelectArray('DataSet', 'נתונים ליצוא', $arrList, $oData->ID);
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
                              'הצגת הרשימה</button>';
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
                      echo '<tr><td colspan="4">לא נמצאו רשומות.</td></tr>';
                    }
                    else
                    {
                      $nCount = 0;
                      echo '<tr><td colspan="4"><span class="link" onclick="JavaScript:SelectAll(true);">כולם</span>&nbsp;',
                        '<span class="link" onclick="JavaScript:SelectAll(false);">הסרת בחירה</span></td></tr>';
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
