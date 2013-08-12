<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

$HelloMessage = '';

if ( isset($g_oMemberSession) ) //not set on public pages, such as catalog.php
{

  $HelloMessage = 'שלום, '. $g_oMemberSession->Name;

  //display balance if different than zero or payment method is not at pickup
  if ($g_oMemberSession->Balance != 0 || $g_oMemberSession->PaymentMethod != Consts::PAYMENT_METHOD_AT_PICKUP)
  {
    $HelloMessage .= '. ' . 'היתרה שלך: ' . $g_oMemberSession->Balance;

    $mMaxOrder = $g_oMemberSession->GetMaxOrder();
    if ($mMaxOrder != NULL && $mMaxOrder != $g_oMemberSession->Balance) //if not payment at pickup
      $HelloMessage .= '. ' . 'מקס. הזמנה: ' . $mMaxOrder;
  }
}

?>
<header>
<input type="hidden" id="hidLogout" name="hidLogout" />
<br/><br/>
<table cellspacing="0" cellpadding="0" width="672">
  <tr>
    <td colspan="2"><a href="<?php echo $g_sRootRelativePath ?>home.php" ><img border="0" src="<?php echo $g_sRootRelativePath ?>logo.gif"/></a></td>
    </tr>
   <tr>
    <td width="100%">
      <?php
      if ( isset($g_oMemberSession) )
      {
        echo '<a href="', $g_sRootRelativePath ,
            'home.php"><img border="0" title="דף הבית" src="', $g_sRootRelativePath ,
            'img/go-home-8.png" /></a>&nbsp;<span>', $HelloMessage  ,'</span>';
      }
      ?>
    </td>
    <td nowrap><?php
    
      //in one language deployment exit, remove this line for better performances
      include_once APP_DIR . '/control/language.php';
      
      if ( isset($g_oMemberSession) )
      {
            
            echo '&nbsp;<span class="link" onclick="JavaScript:Logout()" >יציאה</span>';
      }  
      ?>
    </td>
    </tr>
</table>
<br/><br/>
</header>
