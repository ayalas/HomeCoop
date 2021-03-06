<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//formats outputs of product package size, quantity and interval 
class ProductPackage {
  
  const PROPERTY_QUANTITY = "Quantity";
  const PROPERTY_PACKAGE_SIZE = "PackageSize";
  const PROPERTY_HTML = "Html";
  const PROPERTY_SUPPRESS_TOOLTIP = "SuppressTooltip";
  const PROPERTY_TOOLTIP = "Tooltip";
  const PROPERTY_MODE = "Mode";
  const PROPERTY_ID = "ID";
  const PROPERTY_AVAILABLE_ITEMS = "AvailableItems";
  const PROPERTY_SOLD_OUT = "SoldOut";
  const PROPERTY_HAS_TOOLTIP = "HasTooltip";
  const PROPERTY_TOOLTIP_CSS_CLASS = "TooltipCssClass";
  
  const MODE_NONE = 0;
  const MODE_QUANTITY = 1;
  const MODE_ITEMS_QUANTITY = 2;
  const MODE_PACKAGE_SIZE = 3;
  
  protected $m_sResult = NULL;
  protected $m_aData = NULL;
  protected $m_nMode = self::MODE_NONE;
  
  protected $m_sToolTipPackageSizeLine = '';
  protected $m_sToolTipQuantityIntervalLine = '';
  protected $m_sToolTipAvailableItems = '';
  
  public function __construct($nItems, $fItemQuantity, $sItemUnitAbbrev, $fUnitInterval, $sUnitAbbrev, $fPackageSize, $fQuantity, $fMaxCoopTotal, $fCoopTotal,
      $sTooltipCssClass = 'tooltiphelp', $sID = "productpkg") {
    
    $this->m_aData = array(
       self::PROPERTY_QUANTITY => NULL,  
       self::PROPERTY_PACKAGE_SIZE => '',
       self::PROPERTY_SUPPRESS_TOOLTIP => FALSE,
       self::PROPERTY_AVAILABLE_ITEMS => NULL,
       self::PROPERTY_SOLD_OUT => FALSE,
       self::PROPERTY_HAS_TOOLTIP => FALSE,
       self::PROPERTY_TOOLTIP_CSS_CLASS => $sTooltipCssClass,
       self::PROPERTY_ID => $sID,
    );
        
    //"Left: X items" tooltip
    if ($fMaxCoopTotal > 0)
    {
      $this->m_aData[self::PROPERTY_HAS_TOOLTIP] = TRUE;
      $this->m_aData[self::PROPERTY_AVAILABLE_ITEMS] = $fMaxCoopTotal - $fCoopTotal;
      if ($this->m_aData[self::PROPERTY_AVAILABLE_ITEMS] > 0)
        $this->m_sToolTipAvailableItems = sprintf('<div>%s: %s items</div>', 'Left', $this->m_aData[self::PROPERTY_AVAILABLE_ITEMS]);
      else
      {
        $this->m_aData[self::PROPERTY_SOLD_OUT] = TRUE;
        $this->m_sToolTipAvailableItems = '<div>SOLD OUT</div>';
      }
    }

    $this->m_aData[self::PROPERTY_QUANTITY] = $fQuantity . ' ' . $sUnitAbbrev;
    
    if ( $nItems > 1 && $fItemQuantity != NULL && $sItemUnitAbbrev != NULL )
    {
      $this->m_nMode = self::MODE_ITEMS_QUANTITY;
      $this->m_aData[self::PROPERTY_PACKAGE_SIZE] = $nItems . 'X' . $fItemQuantity . $sItemUnitAbbrev;
    }
    else
    {
      if ($fUnitInterval != NULL && $fUnitInterval != 1)
      {
        $this->m_sToolTipQuantityIntervalLine = '<div>Unit Interval‏:‏&nbsp;‎' . 
                $fUnitInterval . '&nbsp;' . $sUnitAbbrev . '</div>';

        $this->m_nMode = self::MODE_PACKAGE_SIZE;
        $this->m_aData[self::PROPERTY_HAS_TOOLTIP] = TRUE;
      }
      if ($fPackageSize != NULL && $fPackageSize != $fQuantity)
      {                          
        $this->m_aData[self::PROPERTY_PACKAGE_SIZE] = $fPackageSize . ' ' . $sUnitAbbrev;
        $this->m_sToolTipPackageSizeLine = '<div>Package Size‏:‏&nbsp;‎' . 
                $this->m_aData[self::PROPERTY_PACKAGE_SIZE] . '</div>';

        $this->m_nMode = self::MODE_PACKAGE_SIZE;
        $this->m_aData[self::PROPERTY_HAS_TOOLTIP] = TRUE;
      }

      if ($this->m_nMode != self::MODE_PACKAGE_SIZE)
        $this->m_nMode = self::MODE_QUANTITY;
    }
    
    switch($this->m_nMode)
    {
      case self::MODE_QUANTITY:
        $this->m_sResult = $this->m_aData[self::PROPERTY_QUANTITY];
        break;
      case self::MODE_ITEMS_QUANTITY:
        $this->m_sResult = $this->m_aData[self::PROPERTY_PACKAGE_SIZE];
        break;
      case self::MODE_PACKAGE_SIZE:
        $this->m_sResult = $this->m_aData[self::PROPERTY_QUANTITY];
        break;
    }
  }
  
  public function __get( $name ) {
    if ($name == self::PROPERTY_HTML)
    {
      if (($this->m_aData[self::PROPERTY_SUPPRESS_TOOLTIP]) || !($this->m_aData[self::PROPERTY_HAS_TOOLTIP]))
        return $this->m_sResult;
      
      $sHelpID = "hlp" . $this->m_aData[self::PROPERTY_ID];
      
      return '<a id="'. $sHelpID . '" name="'. $sHelpID . '" class="' . $this->m_aData[self::PROPERTY_TOOLTIP_CSS_CLASS] . '" href="#'. $sHelpID . '" >' . htmlspecialchars($this->m_sResult) . '<span>' .
            $this->m_sToolTipPackageSizeLine . $this->m_sToolTipQuantityIntervalLine . $this->m_sToolTipAvailableItems  . '</span></a>';
    }
    else if ($name == self::PROPERTY_TOOLTIP)
      return $this->m_sToolTipPackageSizeLine . $this->m_sToolTipQuantityIntervalLine . $this->m_sToolTipAvailableItems;
    else if ($name == self::PROPERTY_MODE)
      return $this->m_nMode;
    else if ( array_key_exists( $name, $this->m_aData) )
        return $this->m_aData[$name];
    $trace = debug_backtrace();
    throw new Exception(
        'Undefined property via __get(): ' . $name .
        ' in class '. get_class() .', file ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line']);
  }
  
  public function __set( $name, $value ) {        
    if ($name == self::PROPERTY_SUPPRESS_TOOLTIP)
    {
        $this->m_aData[self::PROPERTY_SUPPRESS_TOOLTIP] = $value;
        return;
    }
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property via __set(): ' . $name .
        ' in class '. get_class() .', file ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    if (($this->m_aData[self::PROPERTY_SUPPRESS_TOOLTIP]) || !($this->m_aData[self::PROPERTY_HAS_TOOLTIP]))
      echo htmlspecialchars( $this->m_sResult );
    else
    {
      $sHelpID = "hlp" . $this->m_aData[self::PROPERTY_ID];
      
      echo '<a id="', $sHelpID , '" name="', $sHelpID , '" class="', $this->m_aData[self::PROPERTY_TOOLTIP_CSS_CLASS]  , '" href="#', $sHelpID , '" >' , htmlspecialchars( $this->m_sResult ) , '<span>' ,
              $this->m_sToolTipPackageSizeLine , $this->m_sToolTipQuantityIntervalLine, $this->m_sToolTipAvailableItems  , '</span></a>';
    }
  }
  
  public function EchoTooltip()
  {
    echo $this->m_sToolTipPackageSizeLine , $this->m_sToolTipQuantityIntervalLine, $this->m_sToolTipAvailableItems;
  }
  
  
}

?>