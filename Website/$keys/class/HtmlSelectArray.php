<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document a select element, based on an array list
class HtmlSelectArray {
  const PREFIX= 'sel';
  const EMPTY_VALUE = 0;
  
  const PROPERTY_ID = "ID";
  const PROPERTY_LABEL = "Label";
  const PROPERTY_ARRAY = "Array";
  const PROPERTY_EMPTY_TEXT = "EmptyText";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_ON_CHANGE = "OnChange";
  const PROPERTY_VALUE_FOUND = "ValueFound";
  const PROPERTY_ENCODE_HTML = "EncodeHtml";
  
  protected $m_aData = array( self::PROPERTY_LABEL => NULL,
                              self::PROPERTY_ARRAY => NULL,
                              self::PROPERTY_EMPTY_TEXT => '',
                              self::PROPERTY_VALUE => 0,
                              self::PROPERTY_REQUIRED => FALSE,
                              self::PROPERTY_READ_ONLY => FALSE,
                              self::PROPERTY_ID => NULL,
                              self::PROPERTY_ON_CHANGE => NULL,
                              self::PROPERTY_VALUE_FOUND => FALSE,
                              self::PROPERTY_ENCODE_HTML => TRUE
                      );
  
  public function __construct($sIDSufix, $sLabel, &$arr, $nValue) {
    $this->m_aData[self::PROPERTY_ID] = self::PREFIX . $sIDSufix;
    $this->m_aData[self::PROPERTY_LABEL] = $sLabel;
    $this->m_aData[self::PROPERTY_ARRAY] = $arr;
    $this->m_aData[self::PROPERTY_VALUE] = $nValue;
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
        case self::PROPERTY_ON_CHANGE:
          if ($value == NULL)
            $this->m_aData[$name] = $value;
          else
            $this->m_aData[$name] = str_replace('"', '\'', $value); //replace double quotes to avoid javascript error
          break;
        case self::PROPERTY_ID;
          $trace = debug_backtrace();
          throw new Exception(
              'Undefined property via __set(): ' . $name .
              ' in class '. get_class() .', file ' . $trace[0]['file'] .
              ' on line ' . $trace[0]['line']);
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

    echo '<td nowrap><label ';
    if ($this->m_aData[self::PROPERTY_REQUIRED])
        echo ' class="required" ';
    echo ' for="' , $this->m_aData[self::PROPERTY_ID] , '" >' , $this->m_aData[self::PROPERTY_LABEL] ,
            '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label></td>';
    echo '<td><select id="' , $this->m_aData[self::PROPERTY_ID] , '" name="' , $this->m_aData[self::PROPERTY_ID] , '"';
    
    if ($this->m_aData[self::PROPERTY_REQUIRED])
      echo ' class="requiredselect" ';
    else
      echo ' class="selectentry" ';
    
    if ($this->m_aData[self::PROPERTY_READ_ONLY])
      echo ' disabled="1" ';
    
     if ( $this->m_aData[self::PROPERTY_ON_CHANGE] != NULL)
      echo ' onchange="' ,  $this->m_aData[self::PROPERTY_ON_CHANGE] , '" ';
    
    echo ' >';
    
    if ($this->m_aData[self::PROPERTY_VALUE] == self::EMPTY_VALUE ||  !$this->m_aData[self::PROPERTY_REQUIRED])
    {
      if( $this->m_aData[self::PROPERTY_EMPTY_TEXT] !== NULL )
        echo '<option value="' , self::EMPTY_VALUE , '" >' , $this->m_aData[self::PROPERTY_EMPTY_TEXT] , '</option>';
    }
    
    if (is_array($this->m_aData[self::PROPERTY_ARRAY]))
    {
      foreach( $this->m_aData[self::PROPERTY_ARRAY] as $key => $str )
      {
        echo '<option value="' , $key , '" ';

        if ($this->m_aData[self::PROPERTY_VALUE] == $key)
        {
          echo ' selected="1" ';
          $this->m_aData[self::PROPERTY_VALUE_FOUND] = TRUE;
        }

        echo  ' >';
        if ($this->m_aData[self::PROPERTY_ENCODE_HTML])
          echo htmlspecialchars($str);
        else
          echo $str;
        echo '</option>';
      }
    }
    
    echo '</select></td>';
  }
}

?>
