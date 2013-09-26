<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs the percetage of a coop order capacity or a specific coop order pickup location/producer
//used in home page orders boxes
class CoopOrderCapacity {
  
 const TypeNone = 0;
 const TypeBurden = 1;
 const TypeTotal = 2;
 const TypeStorageBurden = 3;
  
 const PROPERTY_PERCENT = "Percent";
 const PROPERTY_PERCENT_ROUNDED = "PercentRounded";
 const PROPERTY_TYPE = "SelectedType";
 const PROPERTY_BURDEN = "Burden";
 const PROPERTY_STORAGE_BURDEN = "StorageBurden";
 const PROPERTY_TOTAL = "Total";
 
 protected $m_aData = NULL;

 public function __construct( $fMaxBurden, $fBurden, $mMaxTotal, $mTotal, $fStorageBurden = NULL, $fStorageMaxBurden = NULL )
 {
   $this->m_aData = array(self::PROPERTY_PERCENT => 0, 
       self::PROPERTY_PERCENT_ROUNDED => 0, 
       self::PROPERTY_TYPE => self::TypeNone,
       self::PROPERTY_BURDEN => new CoopOrderSubCapacity($fMaxBurden, $fBurden),
       self::PROPERTY_TOTAL => new CoopOrderSubCapacity($mMaxTotal, $mTotal),
       self::PROPERTY_STORAGE_BURDEN => new CoopOrderSubCapacity($fStorageMaxBurden, $fStorageBurden),
     );
   
  //determine which percentage is higher and hence should be considered as the capacity
  $arrPercentages = array(); //sorted array, so kept simple
  $aOtherData = array(); //other data should be stored here (with same key)
  
  if ($this->m_aData[self::PROPERTY_BURDEN]->CanCompute)
  {
    $arrPercentages[self::PROPERTY_BURDEN] = $this->m_aData[self::PROPERTY_BURDEN]->Percent;
    $aOtherData[self::PROPERTY_BURDEN] = array(
          self::PROPERTY_TYPE => self::TypeBurden,
        );
  }
  if ($this->m_aData[self::PROPERTY_TOTAL]->CanCompute)
  {
    $arrPercentages[self::PROPERTY_TOTAL] = $this->m_aData[self::PROPERTY_TOTAL]->Percent;
    $aOtherData[self::PROPERTY_TOTAL] = array(
          self::PROPERTY_TYPE => self::TypeTotal,
        );
  }
  if ($this->m_aData[self::PROPERTY_STORAGE_BURDEN]->CanCompute)
  {
    $arrPercentages[self::PROPERTY_STORAGE_BURDEN] = $this->m_aData[self::PROPERTY_STORAGE_BURDEN]->Percent;
    $aOtherData[self::PROPERTY_STORAGE_BURDEN] = array(
          self::PROPERTY_TYPE => self::TypeStorageBurden,
        );
  }
  
  //get the highest percentage
  if (count($arrPercentages) == 0)
    $this->m_aData[self::PROPERTY_TYPE] = self::TypeBurden;
  else
  {
    arsort($arrPercentages, SORT_NUMERIC);
    $sElement = key($arrPercentages); //get first key (highest)

    $this->m_aData[self::PROPERTY_TYPE] = $aOtherData[$sElement][self::PROPERTY_TYPE];
    $this->m_aData[self::PROPERTY_PERCENT] = $arrPercentages[$sElement];
    $this->m_aData[self::PROPERTY_PERCENT_ROUNDED] = $this->m_aData[$sElement]->PercentRounded;
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
