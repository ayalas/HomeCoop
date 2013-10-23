<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

$HelloMessage = '';
$sBalance = '';

if ( isset($g_oMemberSession) ) //not set on public pages, such as catalog.php
{

  //display balance if different than zero or payment method is not at pickup
  if ($g_oMemberSession->Balance != 0 || $g_oMemberSession->PaymentMethod != Consts::PAYMENT_METHOD_AT_PICKUP)
  {
    $sBalance = $g_oMemberSession->Balance;
    $HelloMessage .= 'Your balance: ' . $g_oMemberSession->Balance;
    
    if ($g_oMemberSession->Balance != $g_oMemberSession->BalanceHeld)
      $HelloMessage .= ' ' . sprintf('‎(in cachier: %s)‎', $g_oMemberSession->BalanceHeld);

    $mMaxOrder = $g_oMemberSession->GetMaxOrder();
    if ($mMaxOrder != NULL && $mMaxOrder != $g_oMemberSession->Balance) //if not payment at pickup
      $HelloMessage .= '<br/>' . 'Max. Order: ' . $mMaxOrder;
      
  }
}

?>
<header>
<input type="hidden" id="hidLogout" name="hidLogout" />
<table cellspacing="0" cellpadding="0" width="100%">
    
    <?php if ( isset($g_oMemberSession) && !$g_oMemberSession->IsOnlyMember )
    {
      echo '<tr><td>';
      include_once $g_sRootRelativePath . 'control/coordpanel.php'; 
      echo '</td></tr>';
    }
    ?>
    <tr class="usermenu"><td class="usermenucell">
      <?php
      //members menu
      if (isset($g_oMemberSession))
      {
        echo '<span class="usermenulabel" title="My Profile"><a href="', $g_sRootRelativePath, 
            'profile.php">Hello, ', $g_oMemberSession->Name,
            '</a></span>';
        
        if ($sBalance != '')
        {
            echo '<a class="tooltiphelp" href="#"><img border="0" src="', $g_sRootRelativePath ,
            'img/emblem-money.png" />‎', $sBalance;
        }
        
        echo '<span>', $HelloMessage, '</span></a>';
        
        if ( $g_oMemberSession->CanOrder) 
        {
          define('prmidMyOrder', 10);

          $oPermissionBridgeSet = new PermissionBridgeSet();

          if ($oPermissionBridgeSet->DefinePermissionBridge(prmidMyOrder, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
                   Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
            echo '<span class="usermenulabel"><a href="',$g_sRootRelativePath,
                  'orders.php">My Orders</a></span>';
        }
        
        echo '<span class="usermenulabel"><a href="', $g_sRootRelativePath, 'catalog.php">Products Catalog</a></span>';
      }
      //in one language deployment exit, remove this line for better performances
      include_once APP_DIR . '/control/language.php';

      if ( isset($g_oMemberSession) )
      {

            echo '<span class="usermenulink usermenulabel" onclick="JavaScript:Logout()" >Logout</span>';
      }  
      ?>
      </td>
    </tr>
    <tr>
      <td class="logo"><a href="<?php echo $g_sRootRelativePath ?>home.php" ><img border="0" src="<?php echo $g_sRootRelativePath ?>logo.gif"/></a></td>
    </tr>
</table>
</header>
