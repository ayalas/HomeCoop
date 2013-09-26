<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document an input=textbox or textarea element
class HtmlTextEdit {
  
  const TEXTBOX = 1;
  const TEXTAREA = 2;
  const PASSWORD = 3;
  
  const PROPERTY_ID = "ID";
  const PROPERTY_TYPE = "ControlType";
  const PROPERTY_DIR = "Dir";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_MAX_LENGTH = "MaxLength";
  const PROPERTY_CSS_CLASS = "CssClass";
  const PROPERTY_TEXTAREA_ROWS = "Rows";
  const PROPERTY_ENCLOSE_IN_HTML_CELL = "EncloseInHtmlCell";
  const PROPERTY_ON_CHANGE = "OnChange";
  const PROPERTY_PROPERTIES = "Properties";
  const PROPERTY_LABEL_SLOT_IS_HTML = 'UseLabelSlotAsHtml';
  
  protected $m_aData = NULL;
  
  public function __construct($sId, $sDir, $nType, $sValue) {
    $this->m_aData = array(
         self::PROPERTY_ID => $sId,
         self::PROPERTY_DIR => $sDir,
         self::PROPERTY_TYPE => $nType,
         self::PROPERTY_REQUIRED => FALSE,
         self::PROPERTY_READ_ONLY => FALSE,
         self::PROPERTY_VALUE => $sValue,
         self::PROPERTY_MAX_LENGTH => NULL,
         self::PROPERTY_CSS_CLASS => NULL,
         self::PROPERTY_TEXTAREA_ROWS => NULL,
         self::PROPERTY_ENCLOSE_IN_HTML_CELL => TRUE,
         self::PROPERTY_ON_CHANGE => NULL,
         self::PROPERTY_PROPERTIES => array(),
         self::PROPERTY_LABEL_SLOT_IS_HTML => FALSE,
        );
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
  
  //allows adding any html attribute to the element
  public function SetAttribute($name, $value)
  {
    $this->m_aData[self::PROPERTY_PROPERTIES][$name] = $value;
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoEditPartHtml()
  {  
    $sControl = '';
    $sValuePrefix = '';
    $sValueSuffix = '';
    $sMaxLength = '';
    $sCssClass = '';
    $nEncodingFlag = ENT_COMPAT;
    //requireddata
    switch($this->m_aData[self::PROPERTY_TYPE])
    {
      case self::TEXTBOX:
        $sMaxLength = '<!$MAX_LENGTH_NAME$!>';
        $sControl = '<input type="text" ';
        $sValuePrefix = ' value="';
        $sValueSuffix = '" />';
        break;
      case self::PASSWORD:
        $sMaxLength = '<!$MAX_LENGTH_NAME$!>';
        $sControl = '<input type="password" ';
        $sValuePrefix = ' value="';
        $sValueSuffix = '" />';
        break;
      case self::TEXTAREA:
        $sMaxLength = '<!$MAX_LENGTH_LONGTEXT$!>';
        $sControl = '<textarea ';
        if ($this->m_aData[self::PROPERTY_TEXTAREA_ROWS] != NULL)
          $sControl .= ' rows="' . $this->m_aData[self::PROPERTY_TEXTAREA_ROWS] . '" ';
        $sValuePrefix = '>';
        $nEncodingFlag = ENT_NOQUOTES;
        $sValueSuffix = '</textarea>';
        break;
    }
    if ( $this->m_aData[self::PROPERTY_MAX_LENGTH] != NULL )
      $sMaxLength = $this->m_aData[self::PROPERTY_MAX_LENGTH];
    
    if ($this->m_aData[self::PROPERTY_ENCLOSE_IN_HTML_CELL])
      echo '<td>';
    
    echo $sControl , ' maxlength="' , $sMaxLength , '" ';

    if ($this->m_aData[self::PROPERTY_DIR]  != NULL)
      echo ' dir="' , $this->m_aData[self::PROPERTY_DIR] , '" ';
    
    if ($this->m_aData[self::PROPERTY_READ_ONLY])
      echo ' readonly="1" ';
    
    echo ' id="' , $this->m_aData[self::PROPERTY_ID] , '" name="' , $this->m_aData[self::PROPERTY_ID] , '" '; 
    
    if ( $this->m_aData[self::PROPERTY_ON_CHANGE] != NULL)
         echo ' onchange="' , $this->m_aData[self::PROPERTY_ON_CHANGE] , '" ';
    
    if ( is_array($this->m_aData[self::PROPERTY_PROPERTIES]) && count($this->m_aData[self::PROPERTY_PROPERTIES]) > 0)
    {
      foreach($this->m_aData[self::PROPERTY_PROPERTIES] as $propkey => $propval)
        echo ' ' , $propkey , '="' , $propval , '" ';      
    }
    
    if ( $this->m_aData[self::PROPERTY_REQUIRED] )
    {
      echo 'required="required" ';
      $sCssClass = ' class="requireddata" ';
    }
    else
    {
      $sCssClass = ' class="dataentry" ';
    }   
    
    if ($this->m_aData[self::PROPERTY_CSS_CLASS] !== NULL)
      $sCssClass = ' class="' . $this->m_aData[self::PROPERTY_CSS_CLASS] . '" ';
    
    echo $sCssClass;
      
    echo $sValuePrefix;
    if ($this->m_aData[self::PROPERTY_VALUE] !== NULL)
      echo htmlspecialchars( $this->m_aData[self::PROPERTY_VALUE] , $nEncodingFlag);
    
    echo $sValueSuffix;
    
    if ($this->m_aData[self::PROPERTY_ENCLOSE_IN_HTML_CELL])
      echo '</td>';
  }
  
}

?>
