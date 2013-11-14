<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

$HelloMessage = '';
$sBalance = '';
$sBalanceLink = '';

if ( isset($g_oMemberSession) ) //not set on public pages, such as catalog.php
{

  //display balance if different than zero or payment method is not at pickup
  if ($g_oMemberSession->Balance != 0 || $g_oMemberSession->PaymentMethod != Consts::PAYMENT_METHOD_AT_PICKUP)
  {
    $sBalance = $g_oMemberSession->Balance;
    $HelloMessage .= 'היתרה שלך: ' . $g_oMemberSession->Balance;
    
    if ($g_oMemberSession->Balance != $g_oMemberSession->BalanceHeld)
      $HelloMessage .= ' ' . sprintf('‏(בקופה: %s)‏', $g_oMemberSession->BalanceHeld);

    $mMaxOrder = $g_oMemberSession->GetMaxOrder();
    if ($mMaxOrder != NULL && $mMaxOrder != $g_oMemberSession->Balance) //if not payment at pickup
      $HelloMessage .= '<br/>' . 'מקס. הזמנה: ' . $mMaxOrder;
      
  }
  
    if ($sBalance != '')
    {
        $sBalanceLink = '<a class="tooltiphelp mobilemenu" href="#"><img border="0" src="' . $g_sRootRelativePath .
        'img/emblem-money.png" />‏' . $sBalance;
    }

    $sBalanceLink .= '<span>' . $HelloMessage . '</span>';
    if ($sBalance != '')
      $sBalanceLink .=  '</a>';
}

?>
<header>
<input type="hidden" id="hidLogout" name="hidLogout" />
<table cellspacing="0" cellpadding="0" class="fullwidth">
  <tr class="mobiledisplay"><td><a id="tglUser" class="nav-toggle"></a></td><?php 
        if ( isset($g_oMemberSession)) { 
          echo '<td>';
          if (!$g_oMemberSession->IsOnlyMember ) 
            echo '<a id="tglCoord" class="nav-toggle"></a>';
          echo '</td>';
         
          echo '<td>';
          if (isset($sHeaderAdditionToLogo)) {
            echo $sHeaderAdditionToLogo;
          }
          echo '</td>';
          
          echo '<td>', $sBalanceLink, '</td>';
       }
     ?></tr>  
    <?php if ( isset($g_oMemberSession) && !$g_oMemberSession->IsOnlyMember )
    {
      echo '<tr class="coordmenu"><td colspan="4" class="coordmenucell">';
      include_once $g_sRootRelativePath . 'control/coordpanel.php'; 
      echo '</td></tr>';
    }
    ?>
  <tr class="usermenu"><td <?php if (isset($g_oMemberSession)) { echo ' colspan="4" '; } ?> class="usermenucell"><nav id="navUser" class="nav-collapse"><ul>
      <?php
      //members menu
      if (isset($g_oMemberSession))
      {
        echo '<li><span class="usermenulabel" title="הפרופיל שלי"><a href="', $g_sRootRelativePath, 
            'profile.php">שלום, ', $g_oMemberSession->Name,
            '</a></span></li>';
        
        echo '<li class="mobilehide">', $sBalanceLink, '</li>';
                
        if ( $g_oMemberSession->CanOrder) 
        {
          define('prmidMyOrder', 10);

          $oPermissionBridgeSet = new PermissionBridgeSet();

          if ($oPermissionBridgeSet->DefinePermissionBridge(prmidMyOrder, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
                   Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
            echo '<li><span class="usermenulabel"><a href="',$g_sRootRelativePath,
                  'orders.php">ההזמנות שלי</a></span></li>';
        }
        
        echo '<li><span class="usermenulabel"><a href="', $g_sRootRelativePath, 'catalog.php">קטלוג המוצרים</a></span></li>';
      }
      //in one language deployment exit, remove this line for better performances
      include_once APP_DIR . '/control/language.php';

      if ( isset($g_oMemberSession) )
      {

            echo '<li><span class="usermenulabel"><a href="#" onclick="JavaScript:Logout()" >יציאה</a></span></li>';
      }  
      ?>
      </ul>
      </nav>
      </td>
    </tr>
    <tr>
      <td class="logo" <?php if (isset($g_oMemberSession)) { echo ' colspan="4" '; } ?> >
        <div ><a href="<?php echo $g_sRootRelativePath ?>home.php" ><img class="logoimg" src="<?php echo $g_sRootRelativePath ?>logo.gif"/></a></div>
      </td>
    </tr>
</table>
</header>
