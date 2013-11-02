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
define('prmidMembersView', 80);


$oPermissionBridgeSet = new PermissionBridgeSet();

?>
<?php
if ($oPermissionBridgeSet->DefinePermissionBridge(prmidCoopOrdersView, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) ||
    $oPermissionBridgeSet->DefinePermissionBridge(prmidCoopOrdersModify, Consts::PERMISSION_AREA_COOP_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/cooporders.php">Cooperative Orders</a></span>
<?php
}        

if ($oPermissionBridgeSet->HasPermission(prmidCoopOrdersModify))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>orders.php">Member Orders</a></span>
<?php
}
        
if ($oPermissionBridgeSet->DefinePermissionBridge(prmidProducersView, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) ||
   $oPermissionBridgeSet->DefinePermissionBridge(prmidProducersModify, Consts::PERMISSION_AREA_PRODUCERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/producers.php">Producers</a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidProductsView, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE) || 
    $oPermissionBridgeSet->DefinePermissionBridge(prmidProductsModify, Consts::PERMISSION_AREA_PRODUCTS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/products.php">Products</a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidPickupLocationsModify, Consts::PERMISSION_AREA_PICKUP_LOCATIONS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/pickuplocs.php">Pickup Locations</a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidCachierTotals, Consts::PERMISSION_AREA_CACHIER_TOTALS, 
           Consts::PERMISSION_TYPE_VIEW, Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/cachier.php">Cashier Totals</a></span>
<?php
}

if ($oPermissionBridgeSet->DefinePermissionBridge(prmidMembersView, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_VIEW, 
     Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE) ||    
    $oPermissionBridgeSet->DefinePermissionBridge(prmidMembersModify, Consts::PERMISSION_AREA_MEMBERS, Consts::PERMISSION_TYPE_MODIFY, 
     Consts::PERMISSION_SCOPE_COOP_CODE, 0, TRUE))
{
?>
<span class="coordmenulabel"><a href="<?php echo $g_sRootRelativePath; ?>coord/members.php">Members</a></span>
<?php
}

echo '</div>';

?>
