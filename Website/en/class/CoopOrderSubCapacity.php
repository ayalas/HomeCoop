<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//helper class for CoopOrderCapacity to return the capacity of a specific measure - burden or sum totals
class CoopOrderSubCapacity {
  
  const PROPERTY_PERCENT = "Percent";
  const PROPERTY_PERCENT_ROUNDED = "PercentRounded";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_MAX_VALUE = "MaxValue";
  const PROPERTY_CAN_COMPUTE = "CanCompute";

  protected $m_aData = NULL;

 public function __construct( $fMaxValue, $fValue )
 {
   $fPercent = 0;
   
   $this->m_aData = array(
       self::PROPERTY_PERCENT => 0, 
       self::PROPERTY_PERCENT_ROUNDED => 0, 
       self::PROPERTY_VALUE => $fValue,
       self::PROPERTY_MAX_VALUE => $fMaxValue,
       self::PROPERTY_CAN_COMPUTE => FALSE
       );
   
  if ($fMaxValue != NULL && $fMaxValue > 0)
  {
   if ($fValue == NULL)
     $fValue = 0;

   $this->m_aData[self::PROPERTY_PERCENT] = ($fValue/$fMaxValue) * 100;
   $this->m_aData[self::PROPERTY_PERCENT_ROUNDED] = Rounding::Round($this->m_aData[self::PROPERTY_PERCENT], ROUND_SETTING_CAPACITY);
   $this->m_aData[self::PROPERTY_CAN_COMPUTE] = TRUE;
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
  }
}

?>
