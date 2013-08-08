
SET @prmTypeModify = (SELECT PT.PermissionTypeKeyID FROM T_PermissionType PT INNER JOIN T_Key K ON K.KeyID = PT.PermissionTypeKeyID WHERE K.sStringKey = 'PERMISSION_TYPE_MODIFY');

SET @roleCoopOrderCoord = (SELECT R.RoleKeyID FROM T_Role R INNER JOIN T_Key K ON K.KeyID = R.RoleKeyID 
WHERE K.sStringKey = 'ROLE_COOP_ORDER_COORDINATOR');

SET @prmAreaOrderSetMax = (SELECT PA.PermissionAreaKeyID FROM T_PermissionArea PA INNER JOIN T_Key K ON K.KeyID = PA.PermissionAreaKeyID 
WHERE K.sStringKey = 'PERMISSION_AREA_ORDER_SET_MAX');

DELETE FROM T_RolePermission
WHERE RoleKeyID = @roleCoopOrderCoord
AND PermissionTypeKeyID =  @prmTypeModify
AND PermissionAreaKeyID = @prmAreaOrderSetMax;
