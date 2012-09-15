SET NAMES 'utf8';

SHOW ERRORS;
SHOW WARNINGS;

/* Language */
SET @LangID = 1 + IfNull((SELECT Max(LangID) FROM Tlng_Language),0);

INSERT INTO Tlng_Language(LangID, sLanguage, bRequired, sPhpFolder, FallingLangID)
VALUES( @LangID, 'English', 1, 'en', NULL);

/* Measures */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_QUANTITY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Quantity' ); /* string NOT IN USE (no translation required) */

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_WEIGHT');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Weight' ); /* string NOT IN USE (no translation required) */

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_VOLUME');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Volume' ); /* string NOT IN USE (no translation required) */

/* Payment Methods */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_UP_TO_BALANCE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Up to Balance' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_PLUS_EXTRA');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Up to Balance + % Extra' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_AT_PICKUP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'At Pickup' );

/* Permission Areas - strings NOT IN USE (no translation required) */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PRODUCERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Producers' ); 

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COORDINATING_GROUPS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Coordinating Groups' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PRODUCTS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Products' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEASURES');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Measures' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_UNITS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Units' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_SPECIFICATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Specification' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATIONS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Pickup Locations' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATION_ADDRESS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Pickup Location Address' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Pickup Location Published Comments' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Orders' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ITEM_UNITS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Units for each item' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_UNIT_ABBREVIATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Unit abbrev.' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ITEM_UNIT_ABBREVIATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Unit abbrev. for each item' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PAYMENT_METHODS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Payment Methods' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PRODUCERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Producers' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ORDERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Orders' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEMBERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Members' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_JOINED_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Joined to Products' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Pickup Locations' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Products' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_ORDERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Member Orders' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Pickup Location Products' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Pickup Location Producers' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Pickup Location Member Orders' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_SUMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Summaries' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Pickup Location Summaries' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ORDER_ITEMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Member Order Items' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEMBER_ROLES');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Member Roles' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ROLES');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Roles' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_CACHIER_TOTALS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cachier Totals' );

/* Permission Scopes - strings NOT IN USE (no translation required) */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_SCOPE_COOP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Entire Cooperative' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_SCOPE_GROUP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Coordinating Group' );

/* Permission Types - strings NOT IN USE (no translation required) */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_MODIFY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Update' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COORD');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Coordinate' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COORD_SET');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Coordination Setting' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_VIEW');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'View' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_DELETE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Delete' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COPY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Copy' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_EXPORT');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Export Data' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_ADD');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Add' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_UPLOAD_FILE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Upload File' );

/* Roles */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_SYSTEM_ADMIN');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'System Administrator' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_COOP_CHIEF_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Chief Coordinator' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PRODUCER_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Producer Coordinator' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_COOP_ORDER_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Cooperative Order Coordinator' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PICKUP_LOCATION_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Pickup Location Coordinator' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_MEMBER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Member' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PRODUCER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Producer' );

/* Units */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_ITEMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'items' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_ITEMS_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'it.' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_KG');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Kg' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_KG_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'kg' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_LITER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Liter' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_LITER_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'lt' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GRAM');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Gram' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GRAM_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'gr.' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_POUND');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Pound' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_POUND_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'lb.' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_OUNCE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Ounce' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_OUNCE_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'oz.' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GALLON');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'Gallon' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GALLON_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'gal.' );
