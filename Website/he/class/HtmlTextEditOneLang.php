<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to htmldocument an HtmlTextEdit output in current language only, with label
class HtmlTextEditOneLang extends HtmlTextEdit {
  
  const PROPERTY_LABEL = "Label";
  
  public function __construct($sLabel, $sId, $sValue) {
    $this->m_aData = array(
         self::PROPERTY_ID => $sId,
         self::PROPERTY_TYPE => self::TEXTBOX,
         self::PROPERTY_DIR => NULL,
         self::PROPERTY_LABEL => $sLabel,
         self::PROPERTY_VALUE => $sValue,
         self::PROPERTY_REQUIRED => FALSE,
         self::PROPERTY_READ_ONLY => FALSE,
         self::PROPERTY_MAX_LENGTH => NULL,
         self::PROPERTY_CSS_CLASS => NULL,
         self::PROPERTY_TEXTAREA_ROWS => NULL,
         self::PROPERTY_ENCLOSE_IN_HTML_CELL => TRUE,
         self::PROPERTY_ON_CHANGE => NULL,
         self::PROPERTY_PROPERTIES => array(),
         self::PROPERTY_LABEL_SLOT_IS_HTML => FALSE,
        );
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {   
    echo '<td nowrap >';
    
    if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
      echo $this->m_aData[self::PROPERTY_LABEL];
    else
    {
     echo '<label ';
      if ($this->m_aData[self::PROPERTY_REQUIRED])
        echo ' class="required" ';

      echo ' for="' , $this->m_aData[self::PROPERTY_ID] , '">' , $this->m_aData[self::PROPERTY_LABEL] ,
         '‏:‏</label>'; 
    }
    
    echo '</td>';
    $this->EchoEditPartHtml();
  }
}

?>
