SET @Lang = (SELECT LangID FROM Tlng_Language Where sPhpFolder = 'en');

SELECT RP.RoleKeyID, KR.sSTringKey, R_S.sString as sRole, 
RP.PermissionAreaKeyID, PA_S.sString as sPermissionArea, 
RP.PermissionTypeKeyID, PT_S.sString as sPermissionType, 
RP.PermissionScopeKeyID, PS_S.sString as sPermissionScope
FROM T_RolePermission RP
INNER JOIN T_Key KR
ON KR.KeyID = RP.RoleKeyID
INNER JOIN Tlng_String R_S
ON R_S.KeyID = RP.RoleKeyID
AND R_S.LangID = @Lang
INNER JOIN Tlng_String PA_S
ON PA_S.KeyID = RP.PermissionAreaKeyID
AND PA_S.LangID = @Lang
INNER JOIN Tlng_String PT_S
ON PT_S.KeyID = RP.PermissionTypeKeyID
AND PT_S.LangID = @Lang
INNER JOIN Tlng_String PS_S
ON PS_S.KeyID = RP.PermissionScopeKeyID
AND PS_S.LangID = @Lang
ORDER BY RP.RoleKeyID, RP.PermissionAreaKeyID, RP.PermissionTypeKeyID;
