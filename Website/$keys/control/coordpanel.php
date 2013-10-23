<?php

//coordinators menu

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

if ($g_oMemberSession->IsOnlyMember)
  return;

echo '<div class="coordmenu">';

define('prmidCoopOrdersModify', 10);
define('prmidCoopOrdersView', 11);
define('prmidProducersModify', 30);
define('prmidProducersView', 31);
define('prmidProductsModify', 40);
define('prmidProductsView', 41);
define('prmidPickupLocationsModify', 50);
define('prmidCachierTotals', 60);
define('prmidMembersModify', 70);

$oPermissionBridgeSet = new PermissionBridgeSet();

?>
<?php
if ($oPermissionBridgeSet->DefinePermissionBridge(prmidCoopOrdersModify, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) ||
    $oPermissionBridgeSet->DefinePermissionBridge(prmidCoopOrdersView, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE)    
        )
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/cooporders.php"><!$PAGE_TITLE_COOP_ORDERS$!></a></span>
<?php
}        

if ($oPermissionBridgeSet->HasPermission(prmidCoopOrdersModify))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>orders.php"><!$LINK_MEMBER_ORDERS$!></a></span>
<?php
}
        
if ($oPermissionBridgeSet->DefinePermissionBridge(prmidProducersModify, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) ||
    $oPermissionBridgeSet->DefinePermissionBridge(prmidProducersView, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/producers.php"><!$PAGE_TITLE_PRODUCERS$!></a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidProductsModify, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) ||
    $oPermissionBridgeSet->DefinePermissionBridge(prmidProductsView, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) )
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/products.php"><!$PAGE_TITLE_PRODUCTS$!></a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidPickupLocationsModify, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/pickuplocs.php"><!$PAGE_TITLE_PICKUP_LOCATIONS$!></a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidCachierTotals, Consts::PERMISSION_AREA_CACHIER_TOTALS, 
           Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/cachier.php"><!$PAGE_TITLE_CACHIER$!></a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidMembersModify, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/members.php"><!$PAGE_TITLE_MEMBERS$!></a></span>
<?php
}

echo '</div>';

?>
