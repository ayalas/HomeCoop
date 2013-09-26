<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document a select element, with TRUE and FALSE values
class HtmlSelectBoolean {
  
  const PROPERTY_ID = "ID";
  const PROPERTY_LABEL = "Label";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_TEXT_FOR_TRUE = "TextForTrue";
  const PROPERTY_TEXT_FOR_FALSE = "TextForFalse";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_ON_CHANGE = "OnChange";
  const PROPERTY_LABEL_SLOT_IS_HTML = "UseLabelSlotAsHtml";
  const PROPERTY_OMIT_LABEL = "OmitLabel";
  
  protected $m_aData = array( self::PROPERTY_LABEL => NULL,
                              self::PROPERTY_VALUE => FALSE,
                              self::PROPERTY_TEXT_FOR_TRUE => NULL,
                              self::PROPERTY_TEXT_FOR_FALSE => NULL,
                              self::PROPERTY_READ_ONLY => FALSE,
                              self::PROPERTY_ID => NULL,
                              self::PROPERTY_ON_CHANGE => NULL,
                              self::PROPERTY_LABEL_SLOT_IS_HTML => FALSE,
                              self::PROPERTY_OMIT_LABEL => FALSE,
                      );
  
  public function __construct($sID, $sLabel, $bValue, $sTextForTrue, $sTextForFalse) {
    $this->m_aData[self::PROPERTY_ID] = $sID;
    $this->m_aData[self::PROPERTY_LABEL] = $sLabel;
    $this->m_aData[self::PROPERTY_VALUE] = $bValue;
    $this->m_aData[self::PROPERTY_TEXT_FOR_TRUE] = $sTextForTrue;
    $this->m_aData[self::PROPERTY_TEXT_FOR_FALSE] = $sTextForFalse;
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
    if (!$this->m_aData[self::PROPERTY_OMIT_LABEL])
    {
      echo '<td nowrap>';

      if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
        echo $this->m_aData[self::PROPERTY_LABEL];
      else
        echo '<label class="required" for="' , $this->m_aData[self::PROPERTY_ID] , '" >' , 
              $this->m_aData[self::PROPERTY_LABEL] , '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label>';
    
      echo '</td>';
    }
    echo '<td><select class="requiredselect" id="' 
            , $this->m_aData[self::PROPERTY_ID] , '" name="' , $this->m_aData[self::PROPERTY_ID] , '"';
    
    if ( $this->m_aData[self::PROPERTY_READ_ONLY] )
      echo ' disabled="1" ';
    
    if ( $this->m_aData[self::PROPERTY_ON_CHANGE] != NULL)
      echo ' onchange="' ,  $this->m_aData[self::PROPERTY_ON_CHANGE] , '" ';
        
    echo ' >',
    
      '<option value="0" ';
    
    if ( !$this->m_aData[self::PROPERTY_VALUE] )
      echo ' selected ';
    
    
    echo ' >' , $this->m_aData[self::PROPERTY_TEXT_FOR_FALSE] , '</option>',
      '<option value="1" ';
    
    if ($this->m_aData[self::PROPERTY_VALUE])
      echo ' selected ';
    
    echo ' >' , $this->m_aData[self::PROPERTY_TEXT_FOR_TRUE] , '</option>',
      '</select></td>';
  }
}

?>
