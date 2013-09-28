SET NAMES 'utf8';

SHOW ERRORS;
SHOW WARNINGS;

/* Measures */
INSERT INTO T_Key( sStringKey )
VALUES( 'MEASURE_QUANTITY' );

SET @QuantityID = LAST_INSERT_ID();

INSERT INTO T_Measure (MeasureKeyID)
VALUES(@QuantityID);

INSERT INTO T_Key( sStringKey )
VALUES( 'MEASURE_WEIGHT' );

SET @WeightID = LAST_INSERT_ID();

INSERT INTO T_Measure (MeasureKeyID)
VALUES(@WeightID);

INSERT INTO T_Key( sStringKey )
VALUES( 'MEASURE_VOLUME' );

SET @VolumeID = LAST_INSERT_ID();

INSERT INTO T_Measure (MeasureKeyID)
VALUES(@VolumeID);

/* Payment Methods */
INSERT INTO T_Key( sStringKey )
VALUES( 'PAYMENT_METHOD_UP_TO_BALANCE' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PaymentMethod ( PaymentMethodKeyID )
VALUES ( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PAYMENT_METHOD_PLUS_EXTRA' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PaymentMethod ( PaymentMethodKeyID )
VALUES ( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PAYMENT_METHOD_AT_PICKUP' );

SET @PaymentMethodAtPickupID = LAST_INSERT_ID();

INSERT INTO T_PaymentMethod ( PaymentMethodKeyID )
VALUES ( @PaymentMethodAtPickupID );

/* Permission Areas */
INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PRODUCERS' );

SET @prmAreaProducers = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaProducers );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COORDINATING_GROUPS' );

SET @prmAreaCoordinatingGroups = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoordinatingGroups );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PRODUCTS' );

SET @prmAreaProducts = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @prmAreaProducts );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_MEASURES' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_UNITS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_SPECIFICATION' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PICKUP_LOCATIONS' );

SET @prmAreaPickupLocs = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @prmAreaPickupLocs );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PICKUP_LOCATION_ADDRESS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDERS' );

SET @prmAreaCoopOrders = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @prmAreaCoopOrders );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ITEM_UNITS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_UNIT_ABBREVIATION' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ITEM_UNIT_ABBREVIATION' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_PAYMENT_METHODS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PRODUCERS' );

SET @prmAreaCoopOrderProducers = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @prmAreaCoopOrderProducers );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ORDERS' );

SET @prmAreaOrders = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID )
VALUES( @prmAreaOrders );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_MEMBERS' );

SET @prmAreaMembers = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaMembers );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_JOINED_PRODUCTS' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS' );

SET @prmAreaCoopOrderPickupLocs = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderPickupLocs );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PRODUCTS' );

SET @prmAreaCoopOrderProducts = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderProducts );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_ORDERS' );

SET @prmAreaCoopOrderMemberOrders = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderMemberOrders );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS' );

SET @prmAreaCoopOrderPLProducts = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderPLProducts );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS' );

SET @prmAreaCoopOrderPLProducers = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderPLProducers );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS' );

SET @prmAreaCoopOrderPLMemberOrders = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderPLMemberOrders );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_SUMS' );

SET @prmAreaCoopOrderSums = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderSums );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS' );

SET @prmAreaCoopOrderPLSums = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCoopOrderPLSums );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ORDER_ITEMS' );

SET @prmAreaOrderItems = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaOrderItems );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_MEMBER_ROLES' );

SET @prmAreaMemberRoles = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaMemberRoles );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ROLES' );

SET @LastID = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @LastID );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_CACHIER_TOTALS' );

SET @prmAreaCachierTotals = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaCachierTotals );


INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_ORDER_SET_MAX' );

SET @prmAreaOrderSetMax = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaOrderSetMax );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_AREA_STORAGE_AREAS' );

SET @prmAreaStorageAreas = LAST_INSERT_ID();

INSERT INTO T_PermissionArea( PermissionAreaKeyID)
VALUES( @prmAreaStorageAreas );

/* Permission Scopes */
INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_SCOPE_COOP' );

SET @prmdepCoop = LAST_INSERT_ID();

INSERT INTO T_PermissionScope( PermissionScopeKeyID, nScopeCode)
VALUES( @prmdepCoop, 1 );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_SCOPE_GROUP' );

SET @prmdepGroup = LAST_INSERT_ID();

INSERT INTO T_PermissionScope( PermissionScopeKeyID, nScopeCode)
VALUES( @prmdepGroup, 2 );

/* Permission Types */

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_MODIFY' );

SET @prmTypeModify = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeModify );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_COORD' );

SET @prmTypeCoord = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeCoord );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_COORD_SET' );

SET @prmTypeCoordSet = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeCoordSet );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_VIEW' );

SET @prmTypeView = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeView );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_DELETE' );

SET @prmTypeDelete = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeDelete );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_COPY' );

SET @prmTypeCopy = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeCopy );


INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_EXPORT' );

SET @prmTypeExport = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeExport );


INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_ADD' );

SET @prmTypeAdd = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeAdd );

INSERT INTO T_Key( sStringKey )
VALUES( 'PERMISSION_TYPE_UPLOAD_FILE' );

SET @prmTypeUploadFile = LAST_INSERT_ID();

INSERT INTO T_PermissionType( PermissionTypeKeyID)
VALUES( @prmTypeUploadFile );

/* Roles */
INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_SYSTEM_ADMIN' );

SET @roleSystemAdmin = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleSystemAdmin );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_COOP_CHIEF_COORDINATOR' );

SET @roleCoopChiefCoord = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleCoopChiefCoord );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_PRODUCER_COORDINATOR' );

SET @roleProducerCoord = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleProducerCoord );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_COOP_ORDER_COORDINATOR' );

SET @roleCoopOrderCoord = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleCoopOrderCoord );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_PICKUP_LOCATION_COORDINATOR' );

SET @rolePickupLocCoord = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @rolePickupLocCoord );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_MEMBER' );

SET @roleMember = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleMember );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_PRODUCER' );

SET @roleProducer = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleProducer );

INSERT INTO T_Key( sStringKey )
VALUES( 'ROLE_MEMBERSHIP_COORDINATOR' );

SET @roleMembershipCoord = LAST_INSERT_ID();

INSERT INTO T_Role( RoleKeyID)
VALUES( @roleMembershipCoord );

/* Permissions */
INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeCoord, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeCoordSet, 1);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeView, 3);


INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoordinatingGroups, @prmTypeModify, 1);


INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducts, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducts, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeCoord, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeCoordSet, 1);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeCoord, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeCoordSet, 1);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeDelete, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeCopy, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaOrders, @prmTypeModify, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaMembers, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPickupLocs, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPickupLocs, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderProducers, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderProducts, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPLProducers, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPLProducts, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPLMemberOrders, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderMemberOrders, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderMemberOrders, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrders, @prmTypeExport, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPickupLocs, @prmTypeExport, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderProducers, @prmTypeExport, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeAdd, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducers, @prmTypeDelete, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeAdd, 3);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaPickupLocs, @prmTypeDelete, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderProducers, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderProducts, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderSums, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderSums, @prmTypeExport, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaOrderItems, @prmTypeView, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaOrderItems, @prmTypeModify, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaMembers, @prmTypeModify, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaMembers, @prmTypeDelete, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaMemberRoles, @prmTypeModify, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaMemberRoles, @prmTypeView, 1);

INSERT INTO T_Permission( PermissionAreaKeyID,  PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaOrders, @prmTypeDelete, 3);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCoopOrderPLSums, @prmTypeView, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaProducts, @prmTypeUploadFile, 1);
 
INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaCachierTotals, @prmTypeView, 1);

INSERT INTO T_Permission( PermissionAreaKeyID, PermissionTypeKeyID, nAllowedScopeCodes)
VALUES( @prmAreaOrderSetMax, @prmTypeModify, 1);

/* Units */
INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_ITEMS' );

SET @UnitItemsID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_ITEMS_ABBRV' );

SET @UnitItemsAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_KG' );

SET @UnitKGID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_KG_ABBRV' );

SET @UnitKGAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_LITER' );

SET @UnitLTID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_LITER_ABBRV' );

SET @UnitLTAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_GRAM' );

SET @UnitGramID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_GRAM_ABBRV' );

SET @UnitGramAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_POUND' );

SET @UnitPoundID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_POUND_ABBRV' );

SET @UnitPoundAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_OUNCE' );

SET @UnitOunceID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_OUNCE_ABBRV' );

SET @UnitOunceAbbrevID = LAST_INSERT_ID();


INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_GALLON' );

SET @UnitGallonID = LAST_INSERT_ID();

INSERT INTO T_Key( sStringKey )
VALUES( 'UNIT_GALLON_ABBRV' );

SET @UnitGallonAbbrevID = LAST_INSERT_ID();

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID, UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@QuantityID, @UnitItemsID, @UnitItemsAbbrevID, 0);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@WeightID, @UnitKGID,@UnitKGAbbrevID, 2);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@WeightID, @UnitPoundID,@UnitPoundAbbrevID, 2);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@VolumeID, @UnitLTID,@UnitLTAbbrevID, 2);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@VolumeID, @UnitGallonID,@UnitGallonAbbrevID, 2);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@WeightID, @UnitGramID,@UnitGramAbbrevID, 0);

INSERT INTO T_Unit (MeasureKeyID, UnitKeyID,UnitAbbreviationStringKeyID, nFloatingPoint)
VALUES(@WeightID, @UnitOunceID,@UnitOunceAbbrevID, 0);

/*SYSTEM_ADMIN - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducers, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducers, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoordinatingGroups, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducts, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaProducts, @prmTypeUploadFile, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaPickupLocs, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaPickupLocs, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaPickupLocs, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaPickupLocs, @prmTypeAdd, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaPickupLocs, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrders, @prmTypeCopy, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaMembers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaMembers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaMembers, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderProducers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderProducts, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderPLMemberOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleSystemAdmin, @prmAreaCoopOrderMemberOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrders, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderPickupLocs, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderProducers, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaProducers, @prmTypeAdd, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaProducers, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderSums, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaOrderItems, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaOrderItems, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaMemberRoles, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaOrders, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCoopOrderPLSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaCachierTotals, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleSystemAdmin, @prmAreaOrderSetMax, @prmTypeModify, @prmdepCoop);


/*CHIEF_COORDINATOR - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducers, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducers, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoordinatingGroups, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducts, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeCopy, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrders, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaMembers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaMembers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaMembers, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderProducers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderProducts, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderPLMemberOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaCoopOrderMemberOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderPickupLocs, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderProducers, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaProducers, @prmTypeAdd, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaProducers, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeAdd, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaPickupLocs, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderSums, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaOrderItems, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaOrderItems, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaMemberRoles, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaOrders, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCoopOrderPLSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaCachierTotals, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopChiefCoord, @prmAreaOrderSetMax, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopChiefCoord, @prmAreaProducts, @prmTypeUploadFile, @prmdepCoop);

/* MEMBERSHIP COORDINATOR - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaPickupLocs, @prmTypeCoord, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaPickupLocs, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaPickupLocs, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaMembers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaMembers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMembershipCoord, @prmAreaMembers, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleMembershipCoord, @prmAreaPickupLocs, @prmTypeAdd, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleMembershipCoord, @prmAreaPickupLocs, @prmTypeDelete, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleMembershipCoord, @prmAreaMemberRoles, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleMembershipCoord, @prmAreaCachierTotals, @prmTypeView, @prmdepCoop);

/*PRODUCER_COORDINATOR - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaProducers, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission( RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID )
VALUES( @roleProducerCoord, @prmAreaProducers, @prmTypeCoord, @prmdepGroup );

INSERT INTO T_RolePermission( RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID )
VALUES( @roleProducerCoord, @prmAreaProducers, @prmTypeCoordSet, @prmdepCoop );

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaProducts, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaProducts, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaProducts, @prmTypeUploadFile, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaProducers, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaPickupLocs, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaPickupLocs, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaPickupLocs, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrders, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrders, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrders, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrders, @prmTypeCopy, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaMembers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderProducers, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderProducts, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderPLMemberOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducerCoord, @prmAreaCoopOrderMemberOrders, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCoopOrders, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCoopOrderSums, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCoopOrderSums, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCoopOrderProducers, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaOrderItems, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaOrderItems, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCoopOrderPLSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducerCoord, @prmAreaCachierTotals, @prmTypeView, @prmdepCoop);

/*ORDER_COORDINATOR - role permissions */
INSERT INTO T_RolePermission( RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID )
VALUES( @roleCoopOrderCoord, @prmAreaProducers, @prmTypeCoord, @prmdepGroup );

INSERT INTO T_RolePermission( RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID )
VALUES( @roleCoopOrderCoord, @prmAreaProducers, @prmTypeView, @prmdepGroup );

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaProducts, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaPickupLocs, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaPickupLocs, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaPickupLocs, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeCoordSet, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeCopy, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaMembers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderPickupLocs, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderProducers, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderProducts, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderPLMemberOrders, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleCoopOrderCoord, @prmAreaCoopOrderMemberOrders, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrders, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrderPickupLocs, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrderProducers, @prmTypeExport, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaOrderItems, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaOrderItems, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrderSums, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrderSums, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaOrders, @prmTypeDelete, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCoopOrderPLSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleCoopOrderCoord, @prmAreaCachierTotals, @prmTypeView, @prmdepCoop);

/*PICKUP LOCATION COORDINATOR - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrders, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrders, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrderPickupLocs, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaPickupLocs, @prmTypeModify, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaPickupLocs, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaPickupLocs, @prmTypeCoordSet, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@rolePickupLocCoord, @prmAreaCoopOrderPLMemberOrders, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @rolePickupLocCoord, @prmAreaCoopOrders, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @rolePickupLocCoord, @prmAreaCoopOrderPickupLocs, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @rolePickupLocCoord, @prmAreaCoopOrderPLSums, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @rolePickupLocCoord, @prmAreaOrderItems, @prmTypeView, @prmdepCoop);

/*PRODUCER - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaProducers, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission( RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID )
VALUES( @roleProducer, @prmAreaProducers, @prmTypeCoord, @prmdepGroup );

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrders, @prmTypeCoord, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaProducts, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaPickupLocs, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrders, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrderPickupLocs, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrderProducers, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrderProducts, @prmTypeView, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrderPLProducers, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleProducer, @prmAreaCoopOrderPLProducts, @prmTypeView, @prmdepCoop);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducer, @prmAreaCoopOrders, @prmTypeExport, @prmdepGroup);

INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES( @roleProducer, @prmAreaCoopOrderProducers, @prmTypeExport, @prmdepGroup);

/*MEMBER - role permissions */
INSERT INTO T_RolePermission(RoleKeyID, PermissionAreaKeyID, PermissionTypeKeyID, PermissionScopeKeyID)
VALUES(@roleMember, @prmAreaOrders, @prmTypeModify, @prmdepCoop);

/* FIRST USER */
INSERT INTO T_Member(sLoginName, sName, sPassword, sEMail, PaymentMethodKeyID, dJoined) 
VALUES( 'admin', 'admin',md5('123456'), 'admin@demosite.org', @PaymentMethodAtPickupID, now());

SET @MemberID = LAST_INSERT_ID();

INSERT INTO T_MemberRole(MemberID, RoleKeyID)
VALUES(@MemberID, @roleSystemAdmin);

INSERT INTO T_CoordinatingGroup(sCoordinatingGroup)
VALUES( NULL );

SET @CoordinatingGroupID = LAST_INSERT_ID();

INSERT INTO T_CoordinatingGroupMember(CoordinatingGroupId, MemberId)
VALUES(@CoordinatingGroupID, @MemberID);
