SET NAMES 'utf8';

SET @PaymentMethodKeyID = (SELECT PaymentMethodKeyID
FROM T_PaymentMethod PM
INNER JOIN T_Key K
ON PM.PaymentMethodKeyID = K.KeyID
WHERE K.sStringKey = 'PAYMENT_METHOD_PLUS_EXTRA');

SET @AtPickup = (SELECT PaymentMethodKeyID
FROM T_PaymentMethod PM
INNER JOIN T_Key K
ON PM.PaymentMethodKeyID = K.KeyID
WHERE K.sStringKey = 'PAYMENT_METHOD_AT_PICKUP');

SET @roleMember = (SELECT R.RoleKeyID FROM T_Role R INNER JOIN T_Key K ON K.KeyID = R.RoleKeyID WHERE K.sStringKey = 'ROLE_MEMBER');

SET @fPOB = 30;

[Insert here first member script]

SET @FirstMemberID = LAST_INSERT_ID();
SELECT @FirstMemberID;

[Insert here all other members script]

INSERT INTO T_MemberRole(MemberID, RoleKeyID)
SELECT M.MemberID, @roleMember
FROM T_Member M
Where MemberID >= @FirstMemberID;

INSERT INTO T_CoordinatingGroup(sCoordinatingGroup)
SELECT Cast(MemberID AS CHAR(10))
FROM T_Member
Where MemberID >= @FirstMemberID;

INSERT INTO T_CoordinatingGroupMember(CoordinatingGroupId, MemberId)
SELECT G.CoordinatingGroupId, M.MemberID
FROM T_CoordinatingGroup G INNER JOIN T_Member M
ON Cast(M.MemberID AS CHAR(10)) = G.sCoordinatingGroup
Where MemberID >= @FirstMemberID;

UPDATE T_CoordinatingGroup
SET sCoordinatingGroup = NULL
WHERE Cast(sCoordinatingGroup as UNSIGNED) >= @FirstMemberID;
