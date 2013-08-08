<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//facilitates joins to strings table, using permission area values
//this is why some permission areas are still created in the database
//although they only serve as constants, to be produced from it (see Consts class for the production script)
class PermissionArea {
  
  const PROPERTY_AREA = "Area";
  const PROPERTY_TABLE_NAME = "TableName";
  const PROPERTY_TABLE_PRIMARY_KEY = "TablePrimaryKey";
  const PROPERTY_TABLE_ALIAS = "TableAlias";
  
  protected $m_aData = NULL;

  public function __construct($nArea)
  {
    $this->m_aData = array(
      self::PROPERTY_AREA => $nArea,
      self::PROPERTY_TABLE_NAME => NULL,
      self::PROPERTY_TABLE_PRIMARY_KEY => NULL,
      self::PROPERTY_TABLE_ALIAS => NULL
      );

    switch($nArea)
    {
      case Consts::PERMISSION_AREA_COOP_ORDERS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_CoopOrder';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'CoopOrderKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'CO';
        break;
      case Consts::PERMISSION_AREA_PRODUCERS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Producer';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'ProducerKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'P';
      break;
      case Consts::PERMISSION_AREA_PRODUCTS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Product';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'ProductKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'PRD';
        break;
      case Consts::PERMISSION_AREA_SPECIFICATION:
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'SpecStringKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'SPC';
        break;
      case Consts::PERMISSION_AREA_MEASURES:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Measure';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'MeasureKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'M';
        break;
      case Consts::PERMISSION_AREA_UNITS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Unit';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'UnitKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'UT';
        break;
      case Consts::PERMISSION_AREA_PICKUP_LOCATIONS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_PickupLocation';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'PickupLocationKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'PL';
        break;
      case Consts::PERMISSION_AREA_PICKUP_LOCATION_ADDRESS:
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'AddressStringKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'ADRS';
        break;
       case Consts::PERMISSION_AREA_PICKUP_LOCATION_PUBLISHED_COMMENTS:
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'PublishedCommentsStringKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'PLPC';
        break;
      case Consts::PERMISSION_AREA_ITEM_UNITS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Unit';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'UnitKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'IUT';
        break;
      case Consts::PERMISSION_AREA_UNIT_ABBREVIATION:
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'UnitAbbreviationStringKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'UTAB';
        break;
      case Consts::PERMISSION_AREA_JOINED_PRODUCTS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Product';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'ProductKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'JPRD';
        break;
      case Consts::PERMISSION_AREA_ITEM_UNIT_ABBREVIATION:
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'UnitAbbreviationStringKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'IUTAB';
        break;
      case Consts::PERMISSION_AREA_PAYMENT_METHODS:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_PaymentMethod';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'PaymentMethodKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'PM';
        break;
       case Consts::PERMISSION_AREA_ROLES:
        $this->m_aData[self::PROPERTY_TABLE_NAME] = 'T_Role';
        $this->m_aData[self::PROPERTY_TABLE_PRIMARY_KEY] = 'RoleKeyID';
        $this->m_aData[self::PROPERTY_TABLE_ALIAS] = 'R';
        break;
    }
  }
  
  public function __get( $name ) {
      if ( array_key_exists( $name, $this->m_aData) )
        return $this->m_aData[$name];
      $trace = debug_backtrace();
      throw new Exception(
          'Undefined property via __get(): ' . $name .
          ' in class '. get_class() .', file ' . $trace[0]['file'] .
          ' on line ' . $trace[0]['line']);
      break;
  }
  
  
}

?>
