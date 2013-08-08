SET @Lang = (SELECT LangID FROM Tlng_Language Where sPhpFolder = 'en');

SELECT P.PermissionAreaKeyID, PA_S.sString as sPermissionArea, 
P.PermissionTypeKeyID, PT_S.sString as sPermissionType, 
P.nAllowedScopeCodes
FROM T_Permission P
INNER JOIN Tlng_String PA_S
ON PA_S.KeyID = P.PermissionAreaKeyID
AND PA_S.LangID = @Lang
INNER JOIN Tlng_String PT_S
ON PT_S.KeyID = P.PermissionTypeKeyID
AND PT_S.LangID = @Lang
ORDER BY P.PermissionAreaKeyID, P.PermissionTypeKeyID;
