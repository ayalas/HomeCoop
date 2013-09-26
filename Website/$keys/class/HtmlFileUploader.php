<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//outputs to html document the input=file control
class HtmlFileUploader {
  
 const PROPERTY_ID = "ID";
 const PROPERTY_REQUIRED = "Required";
 const PROPERTY_READ_ONLY = "ReadOnly";
 const PROPERTY_LABEL = "Label";
 const PROPERTY_FILE_SIZE = "MaxFileSize";
 const PROPERTY_LABEL_SLOT_IS_HTML = "UseLabelSlotAsHtml";
   
 protected $m_aData = NULL;
  
 public function __construct($sId, $sLabel, $nMaxFileSize)
 {
   $this->m_aData = array(
         self::PROPERTY_ID => $sId,
         self::PROPERTY_REQUIRED => FALSE,
         self::PROPERTY_READ_ONLY => FALSE,
         self::PROPERTY_LABEL => $sLabel,
         self::PROPERTY_FILE_SIZE => $nMaxFileSize,
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
          '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label>';
    }
 
    echo '</td><td>';
        
    if ($this->m_aData[self::PROPERTY_FILE_SIZE] > 0)
      echo '<input type="hidden" name="MAX_FILE_SIZE" value="' , $this->m_aData[self::PROPERTY_FILE_SIZE] , '" />';
   
    echo '<input type="file" id="' , $this->m_aData[self::PROPERTY_ID] , '" name="' ,
            $this->m_aData[self::PROPERTY_ID] , '" ';
    
    if ($this->m_aData[self::PROPERTY_READ_ONLY])
      echo ' disabled="1" ';

    if ( $this->m_aData[self::PROPERTY_REQUIRED] )
      echo ' required="required" ';
    
    echo ' size="<!$FILE_UPLOADER_SIZE$!>" ', 
    
       ' /></td>';
  }
 
}

?>
