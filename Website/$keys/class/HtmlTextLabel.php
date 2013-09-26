<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document a span element, possibly sorrounded by an anchor ("a" element) to become a link
class HtmlTextLabel {
  
  const WINDOW_PREFIX = "win";
  
  const PROPERTY_LABEL = "Label";
  const PROPERTY_ID = "ID";
  const PROPERTY_VALUE = "Value";
  const PROPERTY_LINK_URL = "LinkUrl";
  const PROPERTY_WINDOW_OPEN_PARAMS = "WindowParams";
  const PROPERTY_PROPERTIES = "Properties";
  const PROPERTY_USE_HTML_ESCAPE = "UseHtmlEscape";
  const PROPERTY_LABEL_SLOT_IS_HTML = "UseLabelSlotAsHtml";
  
  protected $m_aData = NULL;
  
  public function __construct($sLabel, $sId, $sValue) {
    $this->m_aData = array(
         self::PROPERTY_ID => $sId,
         self::PROPERTY_LABEL => $sLabel,
         self::PROPERTY_VALUE => $sValue,
         self::PROPERTY_LINK_URL => NULL,
         self::PROPERTY_WINDOW_OPEN_PARAMS => NULL,
         self::PROPERTY_PROPERTIES => array(),
         self::PROPERTY_USE_HTML_ESCAPE => TRUE,
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
  public function EchoHtml()
  {    
    
    echo '<td nowrap >';
    
    if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
      echo $this->m_aData[self::PROPERTY_LABEL];
    else
      echo '<label  for="' , $this->m_aData[self::PROPERTY_ID] , '" >' , $this->m_aData[self::PROPERTY_LABEL] ,
        '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label>';
      
    echo '</td><td>';
    
    if ($this->m_aData[self::PROPERTY_LINK_URL] != NULL)
    {
      echo  '<a ';
      if ($this->m_aData[self::PROPERTY_WINDOW_OPEN_PARAMS] == NULL) //means open in same window
        echo  'href="' , $this->m_aData[self::PROPERTY_LINK_URL] , '" ';
      else
      {
        echo  'href="#" onclick="JavaScript:window.open(\'' , $this->m_aData[self::PROPERTY_LINK_URL] , '\',\'' , 
                self::WINDOW_PREFIX , $this->m_aData[self::PROPERTY_ID] , '\',\'' , 
                $this->m_aData[self::PROPERTY_WINDOW_OPEN_PARAMS] , '\');" ';
      }
      
      echo  '>';
    }
    else
    {
      echo '<span ';
      
      if ( is_array($this->m_aData[self::PROPERTY_PROPERTIES]) && count($this->m_aData[self::PROPERTY_PROPERTIES]) > 0)
      {
        foreach($this->m_aData[self::PROPERTY_PROPERTIES] as $propkey => $propval)
          echo ' ' , $propkey , '="' , $propval , '" ';      
      }
      
      echo ' >';
    }
    
    if ($this->m_aData[self::PROPERTY_USE_HTML_ESCAPE])
      echo  htmlspecialchars($this->m_aData[self::PROPERTY_VALUE]);
    else
      echo $this->m_aData[self::PROPERTY_VALUE];
    
    if ($this->m_aData[self::PROPERTY_LINK_URL] != NULL)
      echo  '</a>'; 
    else
      echo  '</span>';
    
    echo '</td>';
  }
}

?>
