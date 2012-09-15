<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs the percetage of a coop order capacity or a specific coop order pickup location/producer
//used in home page orders boxes
class CoopOrderCapacity {
  
 const TypeNone = 0;
 const TypeBurden = 1;
 const TypeTotal = 2;
  
 const PROPERTY_PERCENT = "Percent";
 const PROPERTY_PERCENT_ROUNDED = "PercentRounded";
 const PROPERTY_TYPE = "SelectedType";
 const PROPERTY_BURDEN = "Burden";
 const PROPERTY_TOTAL = "Total";
 
 
 protected $m_aData = NULL;
  
 public function __construct( $fMaxBurden, $fBurden, $mMaxTotal, $mTotal )
 {
   $this->m_aData = array(self::PROPERTY_PERCENT => 0, 
       self::PROPERTY_PERCENT_ROUNDED => 0, 
       self::PROPERTY_TYPE => self::TypeNone,
       self::PROPERTY_BURDEN => new CoopOrderSubCapacity($fMaxBurden, $fBurden),
       self::PROPERTY_TOTAL => new CoopOrderSubCapacity($mMaxTotal, $mTotal));
   
  //determine which percentage is higher and hence should be considered as the capacity
  if ($this->m_aData[self::PROPERTY_BURDEN]->CanCompute && $this->m_aData[self::PROPERTY_BURDEN]->Percent >
          $this->m_aData[self::PROPERTY_TOTAL]->Percent)
  {
    $this->m_aData[self::PROPERTY_TYPE] = self::TypeBurden;
    $this->m_aData[self::PROPERTY_PERCENT] = $this->m_aData[self::PROPERTY_BURDEN]->Percent;
    $this->m_aData[self::PROPERTY_PERCENT_ROUNDED] = $this->m_aData[self::PROPERTY_BURDEN]->PercentRounded;
  }
  else if ($this->m_aData[self::PROPERTY_TOTAL]->CanCompute)
  {
    $this->m_aData[self::PROPERTY_TYPE] = self::TypeTotal;
    $this->m_aData[self::PROPERTY_PERCENT] = $this->m_aData[self::PROPERTY_TOTAL]->Percent;
    $this->m_aData[self::PROPERTY_PERCENT_ROUNDED] = $this->m_aData[self::PROPERTY_TOTAL]->PercentRounded;
  }
  else if ($this->m_aData[self::PROPERTY_BURDEN]->CanCompute)
  {
    $this->m_aData[self::PROPERTY_TYPE] = self::TypeBurden;
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
