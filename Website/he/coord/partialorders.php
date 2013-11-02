<?php

include_once '../settings.php';
include_once '../authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

$oData = new PartialOrders;
$recTable = NULL;
$sPageTitle = 'הזמנות חלקיות';
$fDeficit = 0;
$fItemQuantityToCompleteDeficit = 0;

try
{
  if (isset($_GET['coid']))
    $oData->CoopOrderID = intval($_GET['coid']);
  if (isset($_GET['prd']))
    $oData->ProductID = intval($_GET['prd']);
  $recTable = $oData->LoadData();
  
  switch($oData->LastOperationStatus)
  {
    case SQLBase::OPERATION_STATUS_NO_SUFFICIENT_DATA_PROVIDED:
    case SQLBase::OPERATION_STATUS_NO_PERMISSION:
    case SQLBase::OPERATION_STATUS_LOAD_RECORD_FAILED:
    case SQLBase::OPERATION_STATUS_COORDINATION_GROUP_VERIFY_FAILED:
      RedirectPage::To( $g_sRootRelativePath . Consts::URL_ACCESS_DENIED );
      exit;
  }

  if ($oData->IsPartial && $oData->TotalOrder > 0)
  {
    //first get wether there is a defecit. Value is NOT the real defecit
    $fDeficit = fmod($oData->TotalOrder, $oData->PackageSize);

    if ($fDeficit == 0)
      $g_oError->AddError('ההזמנות החלקיות מצטברות לכדי הזמנת קואופרטיב שלמה', 'ok');
    else
      $fDeficit = $oData->PackageSize - $fDeficit; //real defecit
  }

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
<title><?php echo $oData->ProductName , ':' , $sPageTitle;  ?></title>
<script type="text/javascript" src="../script/public.js" ></script>
<script type="text/javascript" src="../script/authenticated.js" ></script>
<script type="text/javascript" src="../control/error/scError.js" ></script>
<script type="text/javascript">
  function OpenOrder(nID)
  {
    sURL = "../orderitems.php?id=" + nID;
    if (window.opener != null)
      window.opener.document.location = sURL;
    else
      window.open(sURL, "_blank");
  }
</script>
</head>
<body>
<form id="frmMain" name="frmMain" method="post">
<input type="hidden" id="hidOriginalData" name="hidOriginalData" value="<?php echo $oData->GetSerializedOriginalData(); ?>" />
<input type="hidden" id="hidPostAction" name="hidPostAction" value="" />
<input type="hidden" id="hidPostValue" name="hidPostValue" value="<?php echo $oData->CoopOrderID; ?>" />
<table cellspacing="0" cellpadding="0">
    <tr>
        <td width="948"><span class="coopname"><?php echo htmlspecialchars($oData->ProductName);?>:&nbsp;</span><span class="pagename"><?php echo $sPageTitle;  ?></span></td>
    </tr>   
    <tr>
        <td >
            <table cellspacing="0" cellpadding="2" width="100%">
            <tr>
              <td colspan="6"><?php include_once '../control/error/ctlError.php'; ?></td>
            </tr>
            <tr>
              <td class="columntitle">חבר/ה</td>
              <td class="columntitletiny">כמות מקורית</td>
              <td class="columntitletiny">כמות</td>
              <td class="columntitletiny">הוספה</td>
              <td class="columntitleshort">כמות מוצעת</td>
              <td class="columntitlenowidth">ת. הזמנה</td>
            </tr>
            <?php
                if (!$recTable)
                {
                  echo "<tr><td colspan='6'>&nbsp;</td></tr><tr><td align='center' colspan='6'>לא נמצאו הזמנות חלקיות.</td></tr>";
                }
                else
                {
                  while ( $recTable )
                  {
                      $fItemQuantityToCompleteDeficit = 0;
                      
                      if ($oData->IsPartial)
                      {
                        if ($fDeficit > 0 && $recTable["fMaxFixQuantityAddition"] != NULL && $recTable["fMaxFixQuantityAddition"] > 0)
                        {
                          if ($recTable["fMaxFixQuantityAddition"] >= $fDeficit)
                          {
                            $fItemQuantityToCompleteDeficit = $fDeficit;
                            $fDeficit = 0;
                          }
                          else
                          {
                            $fItemQuantityToCompleteDeficit = $recTable["fMaxFixQuantityAddition"];
                            $fDeficit -= $fItemQuantityToCompleteDeficit;
                          }
                        }
                      }
                      
                      echo "<tr>",
                      
                       '<td><span class="link" onclick="JavaScript:OpenOrder(' , $recTable["OrderID"] , ');" >' ,  
                              htmlspecialchars( $recTable["MemberName"] ) ,  '</span></td>',
                      
                       "<td>" , $recTable["fOriginalQuantity"] , "</td>",
                      
                       "<td>" , $recTable["fQuantity"] , "</td>",
                      
                       "<td>" , $recTable["fMaxFixQuantityAddition"] , "</td>",
                      
                       "<td";
                      
                      if ($fItemQuantityToCompleteDeficit > 0)
                        echo ' class="alarmingdata" ';
                      
                      echo '>' , ($recTable["fQuantity"] + $fItemQuantityToCompleteDeficit) , '</td>';
                      
                      $dDate = new DateTime($recTable["dCreated"], $g_oTimeZone);
                      
                      echo "<td>" , $dDate->format('j.n.Y') , "</td>";

                      echo '</tr>';

                      $recTable = $oData->fetch();
                  }
                  
                  //deficit is not completed even after fixes: notify about it
                  if ($fDeficit > 0)
                  {
                   ?>
              <script type="text/javascript">
                SetError('שימו לב: גם אחרי קביעת הכמות המוצעת כאן, הכמויות של המוצר לא ישלימו לכפולות הנדרשות ע&quot;י היצרן', 'warning');
              </script>
                   <?php
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