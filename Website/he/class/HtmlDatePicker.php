<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//write a date picker to html document
//uses php_calendar/calendar.php as the date picker and script/ajax.js (viewcalendar) to show it
class HtmlDatePicker {
  
  const PREFIX = "dp";
  const TIME_PREFIX = "dptm";
  
  const TIME_NOT_DISPLAYED = 0;
  const TIME_DISPLAYED = 1;
  const TIME_REQUIRED = 2;
  
  const PROPERTY_ID = "ID";
  const PROPERTY_LABEL = "Label";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_TIME_SETTING = "TimeSetting";
  const PROPERTY_LABEL_SLOT_IS_HTML = "UseLabelSlotAsHtml";
  
  protected $m_aData = array( self::PROPERTY_LABEL => NULL,
                              self::PROPERTY_VALUE => NULL,
                              self::PROPERTY_REQUIRED => FALSE,
                              self::PROPERTY_READ_ONLY => FALSE,
                              self::PROPERTY_ID => NULL,
                              self::PROPERTY_TIME_SETTING => self::TIME_DISPLAYED,
                              self::PROPERTY_LABEL_SLOT_IS_HTML => FALSE,
                      );
  
  public function __construct($sLabel, $sIDSufix, $dValue) {
    $this->m_aData[self::PROPERTY_ID] = $sIDSufix;
    $this->m_aData[self::PROPERTY_LABEL] = $sLabel;
    $this->m_aData[self::PROPERTY_VALUE] = $dValue;
  }
  
  public function __get( $name ) {
      switch ($name)
      {
        default:
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
    
    public function __set( $name, $value ) {
      switch ($name)
      {
        case self::PROPERTY_ID;
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __set(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
          break;
        case self::PROPERTY_REQUIRED:
          $this->m_aData[$name] = $value;
          if ($value && $this->m_aData[self::PROPERTY_TIME_SETTING] == self::TIME_DISPLAYED)
            $this->m_aData[self::PROPERTY_TIME_SETTING] = self::TIME_REQUIRED;
          break;
        default:
        if (array_key_exists( $name, $this->m_aData))
        {
            $this->m_aData[$name] = $value;
            return;
        }
        $trace = debug_backtrace();
        throw new Exception(
            'Undefined property via __set(): ' . $name .
            ' in class '. get_class() .', file ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line']);
        break;
      }
    }
    
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    global $g_sRootRelativePath;
    global $_SERVER;
    global $_POST;
    
    $sValue = NULL;
    
    $sDateId = self::PREFIX . $this->m_aData[self::PROPERTY_ID];
        
    echo '<td nowrap>';
    
    if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
      echo $this->m_aData[self::PROPERTY_LABEL];
    else
    {
      echo '<label ';
    
    if ($this->m_aData[self::PROPERTY_REQUIRED])
      echo ' class="required" ';
    
      echo ' for="' , $sDateId , '">' , $this->m_aData[self::PROPERTY_LABEL] , '‏:‏</label>';
    }

    echo '</td>';

    echo '<td nowrap><input type="text" maxlength="15" id ="' , $sDateId , '" name = "' , $sDateId , 
            '" value="';
    if ($this->m_aData[self::PROPERTY_VALUE] != NULL)
    {
      echo $this->m_aData[self::PROPERTY_VALUE]->format('j.n.Y');
    }
    else if ($_SERVER[ 'REQUEST_METHOD'] == 'POST')
    {
      if (isset($_POST[$sDateId]))
        echo $_POST[$sDateId];
    }
    echo '" ';

    if ($this->m_aData[self::PROPERTY_REQUIRED])
      echo ' required="required" class="requireddate" ';
    else
      echo ' class="dateentry" ';
    
    if ($this->m_aData[self::PROPERTY_READ_ONLY])
      echo ' disabled="disabled" ';
    
    echo ' />'; 
    
    if ($this->m_aData[self::PROPERTY_TIME_SETTING] != self::TIME_NOT_DISPLAYED)
    {
      $sTimeId = self::TIME_PREFIX . $this->m_aData[self::PROPERTY_ID];
      echo '<input type="text" maxlength="8" id="' , $sTimeId , '" name="' , $sTimeId , '" value="';
      if ($this->m_aData[self::PROPERTY_VALUE] != NULL)
         echo $this->m_aData[self::PROPERTY_VALUE]->format('G:i');
      else if ($_SERVER[ 'REQUEST_METHOD'] == 'POST')
      {
        if (isset($_POST[$sTimeId]))
          echo $_POST[$sTimeId];
      }

      echo '" ';
      
      if ($this->m_aData[self::PROPERTY_TIME_SETTING] == self::TIME_REQUIRED)
        echo ' required="required" class="requiredtime" ';
      else
        echo ' class="timeentry" ';
      
      echo ' />';
    }
    
    //viewcalendar() is defined in script/ajax.js (uses ajax to get server date format)
    if (!$this->m_aData[self::PROPERTY_READ_ONLY])
      echo '<button type="button" onclick="JavaScript:viewcalendar(\'' , 
        $g_sRootRelativePath , 'php_calendar/\',\'' , $sDateId , '\');" >..</button>';
    
    echo '</td>';
  }
}

?>
