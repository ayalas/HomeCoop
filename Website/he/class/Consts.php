<?php 

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//system constants that are not cooperative-specific settings
//such cooperative-specific settings will appear in settings.php instead
class Consts
{
    //pages and folders used repeatedly (path from root)
    const URL_LOGIN = 'index.php';
    const URL_ACCESS_DENIED = 'AccessDenied.php';
    const URL_HOME = 'home.php';
    const URL_CACHE_DIR = 'cache';
       
    //indexes of the languages array, $g_aSupportedLanguages, defined in settings.php
    const IND_LANGUAGE_NAME = 0;
    const IND_LANGUAGE_REQUIRED = 1;
    const IND_LANGUAGE_DIRECTION = 2;
    const IND_LANGUAGE_ID = 3;
    const IND_FALLING_LANGUAGE_ID = 4;
    
    const COPY_ORDER_JUMP_NONE = 0;
    const COPY_ORDER_JUMP_WEEK = 1;
    const COPY_ORDER_JUMP_MONTH = 2;
    
    //sort order (used in coord/orders)
    const SORT_ORDER_ASCENDING = 1;
    const SORT_ORDER_DESCENDING = 2;
    
    //file formats for export
    const EXPORT_FORMAT_MS_EXCEL_XML = 1;
    const EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS = 2;
    
    //truncating grid columns
    const TINY_COLUMN_WIDTH = 55;
    const SHORT_COLUMN_WIDTH = 75;
    const NORMAL_COLUMN_WIDTH = 120;
    const LONG_COLUMN_WIDTH = 180;
    const EXTRA_LONG_COLUMN_WIDTH = 250;
    const GRID_COLUMN_PIXELS_PER_LETTER = 4;
    const ORDER_EXPORT_PRODUCTS_LIST_GROUP_LENGTH = 10;
    const ORDER_EXPORT_PRODUCTS_LIST_COLUMNS = 3;   

    //a bitwise value of the permission scope code
    const PERMISSION_SCOPE_NONE = 0;
    const PERMISSION_SCOPE_COOP_CODE = 1;
    const PERMISSION_SCOPE_GROUP_CODE = 2;
    const PERMISSION_SCOPE_BOTH = 3;  
    
    //used to export members to an open office calc file - with the date they joined the cooperative
    const OPEN_OFFICE_DATE_VALUE_FORMAT = 'Y-m-d';
    const MS_EXCEL_DATE_VALUE_FORMAT = 'Y-m-d\TH:i:s.u';
    
    //email - using preg_match() to validate email with the following pattern:
    //
    //explaining the pattern parts:
    /*
    0) ^ asserts start of string
    1) starting and ending '/' are just delimiters signifying what's between them is a pattern
    2) the repeated string a-zA-Z0-9 denotes the accepted char ranges, i.e. from a to z, from capital A to capital Z and from 0 to 9, in this case. More ranges can be defined by appending them to this string in all its repeated appearances.
    3) the string \._- means that the characters '-', '_' and '.' are allowed in the first part of the email. The use of a +([a-zA-Z0-9]) after that is to ensure that the last character is not one of those.
    4) @ denotes the email 'at' charcter and right after it are the allowed first character(s) ([a-zA-Z0-9_-]) and their subsequent ones 
    */
    const ACCEPTED_EMAIL_REGULAR_EXPRESSION = '/^([a-zA-Z0-9])*([a-zA-Z0-9\._-])*([a-zA-Z0-9])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/' ;

    //DATABASE-MATCHING CONSTANTS, produced by the following script:
        /*
          SELECT CONCAT('const ',sStringKey ,' = ',KeyID,';') as sRow 
          FROM T_Key
          WHERE sStringKey IS NOT NULL
          ORDER By sStringKey;
         */
        //sStringKey would be null for any non-constant value, such as coop order keys, producer keys, etc.
    const MEASURE_QUANTITY = 1;
    const MEASURE_VOLUME = 3;
    const MEASURE_WEIGHT = 2;
    const PAYMENT_METHOD_AT_PICKUP = 6;
    const PAYMENT_METHOD_PLUS_EXTRA = 5;
    const PAYMENT_METHOD_UP_TO_BALANCE = 4;
    const PERMISSION_AREA_CACHIER_TOTALS = 36;
    const PERMISSION_AREA_COOP_ORDERS = 16;
    const PERMISSION_AREA_COOP_ORDER_ORDERS = 27;
    const PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATIONS = 25;
    const PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_ORDERS = 30;
    const PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCERS = 29;
    const PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_PRODUCTS = 28;
    const PERMISSION_AREA_COOP_ORDER_PICKUP_LOCATION_SUMS = 32;
    const PERMISSION_AREA_COOP_ORDER_PRODUCERS = 21;
    const PERMISSION_AREA_COOP_ORDER_PRODUCTS = 26;
    const PERMISSION_AREA_COOP_ORDER_SUMS = 31;
    const PERMISSION_AREA_COORDINATING_GROUPS = 8;
    const PERMISSION_AREA_ITEM_UNITS = 17;
    const PERMISSION_AREA_ITEM_UNIT_ABBREVIATION = 19;
    const PERMISSION_AREA_JOINED_PRODUCTS = 24;
    const PERMISSION_AREA_MEASURES = 10;
    const PERMISSION_AREA_MEMBERS = 23;
    const PERMISSION_AREA_MEMBER_ROLES = 34;
    const PERMISSION_AREA_ORDERS = 22;
    const PERMISSION_AREA_ORDER_ITEMS = 33;
    const PERMISSION_AREA_ORDER_SET_MAX = 37;
    const PERMISSION_AREA_PAYMENT_METHODS = 20;
    const PERMISSION_AREA_PICKUP_LOCATIONS = 13;
    const PERMISSION_AREA_PICKUP_LOCATION_ADDRESS = 14;
    const PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS = 15;
    const PERMISSION_AREA_PRODUCERS = 7;
    const PERMISSION_AREA_PRODUCTS = 9;
    const PERMISSION_AREA_ROLES = 35;
    const PERMISSION_AREA_SPECIFICATION = 12;
    const PERMISSION_AREA_UNITS = 11;
    const PERMISSION_AREA_UNIT_ABBREVIATION = 18;
    const PERMISSION_AREA_STORAGE_AREAS = 105;
    const PERMISSION_SCOPE_COOP = 38;
    const PERMISSION_SCOPE_GROUP = 39;
    const PERMISSION_TYPE_ADD = 47;
    const PERMISSION_TYPE_COORD = 41;
    const PERMISSION_TYPE_COORD_SET = 42;
    const PERMISSION_TYPE_COPY = 45;
    const PERMISSION_TYPE_DELETE = 44;
    const PERMISSION_TYPE_EXPORT = 46;
    const PERMISSION_TYPE_MODIFY = 40;
    const PERMISSION_TYPE_UPLOAD_FILE = 48;
    const PERMISSION_TYPE_VIEW = 43;
    const ROLE_COOP_CHIEF_COORDINATOR = 50;
    const ROLE_COOP_ORDER_COORDINATOR = 52;
    const ROLE_MEMBER = 54;
    const ROLE_MEMBERSHIP_COORDINATOR = 56;
    const ROLE_PICKUP_LOCATION_COORDINATOR = 53;
    const ROLE_PRODUCER = 55;
    const ROLE_PRODUCER_COORDINATOR = 51;
    const ROLE_SYSTEM_ADMIN = 49;
    const UNIT_GALLON = 69;
    const UNIT_GALLON_ABBRV = 70;
    const UNIT_GRAM = 63;
    const UNIT_GRAM_ABBRV = 64;
    const UNIT_ITEMS = 57;
    const UNIT_ITEMS_ABBRV = 58;
    const UNIT_KG = 59;
    const UNIT_KG_ABBRV = 60;
    const UNIT_LITER = 61;
    const UNIT_LITER_ABBRV = 62;
    const UNIT_OUNCE = 67;
    const UNIT_OUNCE_ABBRV = 68;
    const UNIT_POUND = 65;
    const UNIT_POUND_ABBRV = 66;
    //end of DATABASE-MATCHING CONSTANTS
}
