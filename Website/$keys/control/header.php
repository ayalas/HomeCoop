<?

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

$HelloMessage = '';

if ( isset($g_oMemberSession) ) //not set on public pages, such as catalog.php
{

  $HelloMessage = '<!$HEADER_HELLO$!>, '. $g_oMemberSession->Name;

  //display balance if different than zero or payment method is not at pickup
  if ($g_oMemberSession->Balance != 0 || $g_oMemberSession->PaymentMethod != Consts::PAYMENT_METHOD_AT_PICKUP)
  {
    $HelloMessage .= '. ' . '<!$HEADER_YOUR_BALNACE$!>: ' . $g_oMemberSession->Balance;

    $mMaxOrder = $g_oMemberSession->GetMaxOrder();
    if ($mMaxOrder != NULL && $mMaxOrder != $g_oMemberSession->Balance) //if not payment at pickup
      $HelloMessage .= '. ' . '<!$HEADER_YOUR_MAX_ORDER$!>: ' . $mMaxOrder;
  }
}

?>
<header>
<input type="hidden" id="hidLogout" name="hidLogout" />
<br/><br/>
<table cellspacing="0" cellpadding="0" width="<!$HOME_CONTENT_WIDTH$!>">
  <tr>
    <td colspan="2"><a href="<?php echo $g_sRootRelativePath ?>home.php" ><img border="0" src="<?php echo $g_sRootRelativePath ?>logo.gif"/></a></td>
    </tr>
   <tr>
    <td width="100%">
      <?php
      if ( isset($g_oMemberSession) )
      {
        echo '<a href="', $g_sRootRelativePath ,
            'home.php"><img border="0" title="<!$HOME_PAGE_TITLE$!>" src="', $g_sRootRelativePath ,
            'img/go-home-8.png" /></a>&nbsp;<span>', $HelloMessage  ,'</span>';
      }
      ?>
    </td>
    <td nowrap><?php
    
      //in one language deployment exit, remove this line for better performances
      include_once APP_DIR . '/control/language.php';
      
      if ( isset($g_oMemberSession) )
      {
            
            echo '&nbsp;<span class="link" onclick="JavaScript:Logout()" ><!$HEADER_LOGOUT$!></span>';
      }  
      ?>
    </td>
    </tr>
</table>
<br/><br/>
</header>
