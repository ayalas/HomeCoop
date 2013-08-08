<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//computes the diffrence between record sets, sorted by their primary key, retrieved by PDO::fetchAll() as two-dimentional arrays, 
////given that their primary key is consisted of numeric fields only
class TableDiff {
  
  const KEY_CHANGED = "Changed";
  const KEY_REMOVED = "Removed";
  const KEY_ADDED = "Added";
  const KEY_NUMERIC_KEYS = "NumericKeys"; //primary key
  const KEY_ATTRIBUTES = "Attributes"; //other fields to compare in the recordset. can be null
  
  const REMOVED_RECORD = -1;
  const MATCHED_RECORD = 0;
  const ADDED_RECORD = 1;
    
  protected $m_aChanged = array();
  protected $m_aRemoved = array();
  protected $m_aAdded = array();
  
  protected $m_aNumericKeys = NULL;
  protected $m_aAttributes = NULL;
  
  public function __get( $name ) {
      switch ( $name ) 
      {           
         case self::KEY_ADDED;
            return $this->m_aAdded;
         case self::KEY_REMOVED;
            return $this->m_aRemoved;
         case self::KEY_CHANGED;
            return $this->m_aChanged;
         default:
              $trace = debug_backtrace();
              trigger_error(
                  'Undefined property via __get(): ' . $name .
                  ' in class '. get_class() .', file ' . $trace[0]['file'] .
                  ' on line ' . $trace[0]['line'],
                  E_USER_NOTICE);
          break;
      }
  }

  public function __set( $name, $value ) {
    switch( $name )
    {
      case self::KEY_NUMERIC_KEYS;
        $this->m_aNumericKeys = $value;
        break;
      case self::KEY_ATTRIBUTES:
        $this->m_aAttributes = $value;
        break;
      default:
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __set(): ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        break;
    }
  }
  
  public function ComputeTableDiff($aOriginalData, $aData)
  {
    if (!is_array($aOriginalData))
    {
      $this->m_aAdded = $aData;
      return;
    }
    
    if (!is_array($aData))
    {
      $this->m_aRemoved = $aOriginalData;
      return;
    }
    
    if (!is_array($this->m_aNumericKeys) || count($this->m_aNumericKeys)==0)
      return;
    
    $nDataPos = 0;
    $nOrigPos = 0;
    $nMatch = 0;
    $bNoAttributes = (!is_array($this->m_aAttributes) || count($this->m_aAttributes)==0);
    $nTotalOrig = count($aOriginalData);
    $nTotalData = count($aData);
    
    //last indexes processed in the two arrays
    $nLastDataProcessed = -1;
    $nLastOrigProcessed = -1;
    
    if (max($nTotalData,$nTotalOrig) == 0)
      return;
    
    $bAlwaysRemove = ($nTotalData == 0);
    $bAlwaysAdd = ($nTotalOrig == 0);
    
    do
    {    
      if ( $bAlwaysRemove )
        $nMatch = self::REMOVED_RECORD;
      else if ( $bAlwaysAdd )
        $nMatch = self::ADDED_RECORD;
      else
        $nMatch = $this->CompareRecordKeys($aOriginalData[$nOrigPos] ,$aData[$nDataPos]);
      
      switch($nMatch)
      {
        case self::MATCHED_RECORD:
          $nLastDataProcessed = $nDataPos;
          $nLastOrigProcessed = $nOrigPos;
          if (!$bNoAttributes)
          {
            //check for changes
            if (!$this->CompareRecordAttributes($aOriginalData[$nOrigPos] ,$aData[$nDataPos]))
              $this->m_aChanged[] = $aData[$nDataPos];
          }          
          if ($nDataPos >= $nTotalData - 1 && $nOrigPos < $nTotalOrig - 1)
          {
            $bAlwaysRemove = TRUE;
            $nOrigPos++;
          }
          else if ($nDataPos < $nTotalData - 1 && $nOrigPos >= $nTotalOrig - 1)
          {
            $bAlwaysAdd = TRUE;
            $nDataPos++;
          }
          else //let the while condition trigger exit
          {
            $nDataPos++;
            $nOrigPos++;
          }
          break;
        case self::REMOVED_RECORD:
          //add as removed record
          $this->m_aRemoved[] = $aOriginalData[$nOrigPos];
          $nLastOrigProcessed = $nOrigPos;
                    
          if ($nOrigPos >= $nTotalOrig - 1) //check for exit
          {    
            if ($nDataPos >= $nTotalData - 1)
            {
              if (($nDataPos == $nTotalData -1) && $nDataPos > $nLastDataProcessed) //if last position wasn't processed
                $this->m_aAdded[] = $aData[$nDataPos];
              return; //arrays have ended, exit the loop
            }
            
            $bAlwaysAdd = TRUE;
            if ($nDataPos == $nLastDataProcessed)
              $nDataPos++;
          }
          else
            $nOrigPos++;
          break;
        case self::ADDED_RECORD:
          //add as added record
          $this->m_aAdded[] = $aData[$nDataPos];
          $nLastDataProcessed = $nDataPos;
          
          if ( $nDataPos >= $nTotalData - 1 ) //check for exit
          {             
            if ($nOrigPos >= $nTotalOrig - 1) 
            {
              if (($nOrigPos ==  $nTotalOrig - 1) && $nOrigPos > $nLastOrigProcessed) //if last position wasn't processed
                $this->m_aRemoved[] = $aOriginalData[$nOrigPos];
              return; //arrays have ended, exit the loop
            }
            
            $bAlwaysRemove = TRUE;
            if ($nOrigPos == $nLastOrigProcessed)
              $nOrigPos++;
          }
          else
            $nDataPos++;
          
          break;
      }
    } while($nOrigPos < $nTotalOrig || $nDataPos < $nTotalData);
  }
    
  //returns self::REMOVED_RECORD if Data's key is larger than Orig's
  //self::MATCHED_RECORD if matching
  //self::ADDED_RECORD if Orig's key is larger than Data's
  protected function CompareRecordKeys(&$aOriginalData ,&$aData)
  {
    foreach($this->m_aNumericKeys as $key)
    {
      if ($aOriginalData[$key] > $aData[$key])
        return self::ADDED_RECORD;
      if ($aOriginalData[$key] < $aData[$key])
        return self::REMOVED_RECORD;
    }
    return self::MATCHED_RECORD;
  }
    
  //returns TRUE if records match, FALSE if not
  protected function CompareRecordAttributes(&$aOriginalData ,&$aData)
  {
    foreach($this->m_aAttributes as $key)
    {
      if ($aOriginalData[$key] != $aData[$key])
        return FALSE;
    }
    return TRUE;
  }
}
?>
