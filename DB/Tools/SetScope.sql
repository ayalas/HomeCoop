SET @prmTypeView = (SELECT PT.PermissionTypeKeyID FROM T_PermissionType PT INNER JOIN T_Key K ON K.KeyID = PT.PermissionTypeKeyID WHERE K.sStringKey = 'PERMISSION_TYPE_VIEW');

SET @roleCoopOrderCoord = (SELECT R.RoleKeyID FROM T_Role R INNER JOIN T_Key K ON K.KeyID = R.RoleKeyID 
WHERE K.sStringKey = 'ROLE_COOP_ORDER_COORDINATOR');

SET @prmAreaCoopOrders = (SELECT PA.PermissionAreaKeyID FROM T_PermissionArea PA INNER JOIN T_Key K ON K.KeyID = PA.PermissionAreaKeyID WHERE K.sStringKey =
'PERMISSION_AREA_COOP_ORDERS');

SET @prmScope = (SELECT PD.PermissionScopeKeyID FROM T_PermissionScope PD INNER JOIN T_Key K ON K.KeyID = PD.PermissionScopeKeyID 
WHERE K.sStringKey = 'PERMISSION_SCOPE_COOP');

/* or alternatively, use group level permission:
SET @prmScope = (SELECT PD.PermissionScopeKeyID FROM T_PermissionScope PD INNER JOIN T_Key K ON K.KeyID = PD.PermissionScopeKeyID 
WHERE K.sStringKey = 'PERMISSION_SCOPE_GROUP');

*/

UPDATE T_RolePermission
SET PermissionScopeKeyID = @prmScope
WHERE RoleKeyID = @roleCoopOrderCoord
AND PermissionAreaKeyID = @prmAreaCoopOrders
AND PermissionTypeKeyID = @prmTypeView;