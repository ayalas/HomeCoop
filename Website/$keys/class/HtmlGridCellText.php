<?php
if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//used to output the html abbr element
//according to system defined widthes
class HtmlGridCellText {
  const PROPERTY_TEXT = "Text";
  const PROPERTY_WIDTH = "Width";
  
  const CELL_TYPE_NONE = 0;
  const CELL_TYPE_TINY = 1;
  const CELL_TYPE_SHORT = 2;
  const CELL_TYPE_NORMAL = 3;
  const CELL_TYPE_LONG = 4;
  const CELL_TYPE_EXTRA_LONG = 5;
  
  protected $m_aData = NULL;
  
  public function __construct($sText, $nType) {
    $this->m_aData = array(
          self::PROPERTY_TEXT => $sText,
          self::PROPERTY_WIDTH => $this->GetWidthByType($nType)
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
  
  protected function GetWidthByType($nType)
  {
    switch($nType)
    {
      case self::CELL_TYPE_TINY:
       return Consts::TINY_COLUMN_WIDTH;
      case self::CELL_TYPE_SHORT:
       return Consts::SHORT_COLUMN_WIDTH;
      case self::CELL_TYPE_NORMAL:
       return Consts::NORMAL_COLUMN_WIDTH;
      case self::CELL_TYPE_LONG:
       return Consts::LONG_COLUMN_WIDTH;
      case self::CELL_TYPE_EXTRA_LONG:
       return Consts::EXTRA_LONG_COLUMN_WIDTH;
      default:
       return 0;
    }
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    if ($this->m_aData[self::PROPERTY_WIDTH] <= 0 || $this->m_aData[self::PROPERTY_TEXT] == NULL)
      return $this->m_aData[self::PROPERTY_TEXT]; //return text untruncated, if no valid width was specified
    
    $nSpaceInLetters = intval($this->m_aData[self::PROPERTY_WIDTH] / Consts::GRID_COLUMN_PIXELS_PER_LETTER);
    
    $nLen = strlen( $this->m_aData[self::PROPERTY_TEXT] );

    if ($nLen > $nSpaceInLetters)
    {
      echo '<abbr title="' , htmlspecialchars( $this->m_aData[self::PROPERTY_TEXT] ) , '" >' , 
            htmlspecialchars(trim(mb_substr( $this->m_aData[self::PROPERTY_TEXT], 0, $nSpaceInLetters ))) , '</abbr>';
    }
    else
      echo htmlspecialchars($this->m_aData[self::PROPERTY_TEXT]); //width is long enough
    
  }

}

?>
