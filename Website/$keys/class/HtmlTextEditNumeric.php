<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document an HtmlTextEdit, with label, refined for numbers input
//refining includes textbox (not textarea) with ltr reading order, and a max length suitable for number input
class HtmlTextEditNumeric extends HtmlTextEdit {
  
  const PROPERTY_LABEL = "Label";
  const NUMBER_DEFAULT_MAX_LENGTH = 20;
  
  public function __construct($sLabel, $sId, $nValue) {
    $this->m_aData = array(
         self::PROPERTY_ID => $sId,
         self::PROPERTY_TYPE => self::TEXTBOX,
         self::PROPERTY_DIR => 'ltr',
         self::PROPERTY_LABEL => $sLabel,
         self::PROPERTY_VALUE => $nValue,
         self::PROPERTY_REQUIRED => FALSE,
         self::PROPERTY_READ_ONLY => FALSE,
         self::PROPERTY_MAX_LENGTH => self::NUMBER_DEFAULT_MAX_LENGTH,
         self::PROPERTY_CSS_CLASS => NULL,
         self::PROPERTY_TEXTAREA_ROWS => NULL,
         self::PROPERTY_ENCLOSE_IN_HTML_CELL => TRUE,
         self::PROPERTY_ON_CHANGE => NULL,
         self::PROPERTY_PROPERTIES => array()
        );
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    echo '<td nowrap ><label ';
    if ($this->m_aData[self::PROPERTY_REQUIRED])
      echo ' class="required" ';
      
    echo ' for="' . $this->m_aData[self::PROPERTY_ID] , '">' , $this->m_aData[self::PROPERTY_LABEL] ,
            '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label></td>';
    $this->EchoEditPartHtml();
  }
}

?>
