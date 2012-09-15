<span><a href="<?php echo $g_sRootRelativePath; ?>profile.php">My Profile</a></span><br/>
<?php 

//members menu

if (isset($g_oMemberSession) && $g_oMemberSession->CanOrder) 
{
  define('prmidMyOrder', 10);

  $oPermissionBridgeSet = new PermissionBridgeSet();

  if ($oPermissionBridgeSet->DefinePermissionBridge(prmidMyOrder, Consts::PERMISSION_AREA_ORDERS, Consts::PERMISSION_TYPE_MODIFY, 
           Consts::PERMISSION_SCOPE_BOTH, 0, TRUE))
    echo '<span class="popularlink"><a href="',$g_sRootRelativePath,
          'orders.php">My Orders</a></span><br/>';
}
?>
<span><a href="<?php echo $g_sRootRelativePath; ?>catalog.php">Products Catalog</a></span><br/>

