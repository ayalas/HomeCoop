SET NAMES 'utf8';

SHOW ERRORS;
SHOW WARNINGS;

/* Language */
SET @FallingLangID = (SELECT LangID FROM Tlng_Language WHERE sPhpFolder = 'en');

SET @LangID = 1 + IfNull((SELECT Max(LangID) FROM Tlng_Language),0);

SET @sLanguage = 'עברית';

INSERT INTO Tlng_Language(LangID, sLanguage, bRequired, sPhpFolder, FallingLangID)
VALUES( @LangID,@sLanguage, 1, 'he', @FallingLangID);

/* Measures */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_QUANTITY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יחידות' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_WEIGHT');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'משקל' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'MEASURE_VOLUME');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'נפח' );

/* Payment Methods */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_UP_TO_BALANCE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'עד לגובה היתרה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_PLUS_EXTRA');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'עד לגובה היתרה + % חריגה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PAYMENT_METHOD_AT_PICKUP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'בעת האיסוף' );

/* Permission Areas */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PRODUCERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יצרנים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COORDINATING_GROUPS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'קבוצות תיאום' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PRODUCTS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מוצרים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEASURES');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מידות' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_UNITS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יחידות מדידה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_SPECIFICATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מפרט' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATIONS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מקומות איסוף' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATION_ADDRESS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'כתובת מקום איסוף' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הערות לפרסום של מקום איסוף' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הזמנות קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ITEM_UNITS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יחידות מדידה לפריט בודד' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_UNIT_ABBREVIATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'קצור יחידת מדידה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ITEM_UNIT_ABBREVIATION');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'קצור יחידת מדידה לפריט בודד' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_PAYMENT_METHODS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'שיטות תשלום' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PRODUCERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יצרני הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ORDERS');

INSERT INTO Tlng_String ( KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הזמנות' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEMBERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'חברות/ים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_JOINED_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מוצרים מצורפים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מקומות איסוף של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מוצרים של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_ORDERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הזמנת חברות/ים של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מוצרי מקום איסוף של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יצרני מקום איסוף של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הזמנות חברות/ים לפי מקום איסוף של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_SUMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'סכומי הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'סכומי מקום איסוף של הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ORDER_ITEMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'פריטי הזמנת חבר/ה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_MEMBER_ROLES');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'תפקידי חברות/ים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_ROLES');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'תפקידים');

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_AREA_CACHIER_TOTALS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מצב הקופה');

/* Permission Scopes */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_SCOPE_COOP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'כל הקואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_SCOPE_GROUP');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'קבוצה מתאמת' );

/* Permission Types */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_MODIFY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'עדכון' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COORD');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'תיאום' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COORD_SET');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'עריכת תיאום' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_VIEW');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'צפיה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_DELETE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מחיקה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_COPY');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'העתקה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_EXPORT');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יצוא נתונים' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_ADD');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'הוספה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'PERMISSION_TYPE_UPLOAD_FILE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'העלאת קובץ' );

/* Roles */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_SYSTEM_ADMIN');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מנהל/ת מערכת' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_COOP_CHIEF_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מתאמ/ת קואפרטיב ראשית' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PRODUCER_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מתאמ/ת יצרן' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_COOP_ORDER_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מתאמ/ת הזמנת קואופרטיב' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PICKUP_LOCATION_COORDINATOR');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'מתאמ/ת מקום איסוף' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_MEMBER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'חבר/ה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'ROLE_PRODUCER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יצרן' );

/* Units */
SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_ITEMS');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יחידות' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_ITEMS_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'יח''' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_KG');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ק"ג' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_KG_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ק"ג' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_LITER');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ליטר' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_LITER_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ל''' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GRAM');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'גרם' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GRAM_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ג''' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_POUND');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'פאונד' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_POUND_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'ליב''' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_OUNCE');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'אונקיה' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_OUNCE_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'אונק''' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GALLON');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'גלון' );

SET @LastID = (SELECT KeyID FROM T_Key WHERE sStringKey = 'UNIT_GALLON_ABBRV');

INSERT INTO Tlng_String (KeyID, LangID, sString )
VALUES( @LastID, @LangID, 'גל''' );
