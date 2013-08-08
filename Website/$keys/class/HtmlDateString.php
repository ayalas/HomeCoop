<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//used to display dates as labels, without date picker
class HtmlDateString {
  
  const PROPERTY_DATE = "Date";
  const PROPERTY_TYPE = "Type";
  
  const TYPE_NO_CURRENT_YEAR = 1;
  
  const PROPERTY_STRING_TYPE = 0;
  
  protected $m_aData = NULL;
  
  
  public function __construct($dtValue, $nType) {
    global $g_oTimeZone;
    $this->m_aData = array(
         self::PROPERTY_DATE => new DateTime($dtValue, $g_oTimeZone),
         self::PROPERTY_TYPE => $nType
        );
  }
  
  public static function GetThisYear()
  {
    global $g_dNow;
    $dtNow = $g_dNow;
    $sYear = $dtNow->format('Y');
    return $sYear + 0;
  }
  
  protected function GetYear()
  {
    $sYear = $this->m_aData[self::PROPERTY_DATE]->format('Y');
    return $sYear + 0;
  }
  
  //echo directly to html document to save some string concats/retrieval
  public function EchoHtml()
  {
    switch ($this->m_aData[self::PROPERTY_TYPE])
    {
      case self::TYPE_NO_CURRENT_YEAR: //return short day, month date string for current year 
      ////and full day, month, year date string otherwise
        if (self::GetThisYear() == $this->GetYear())
          echo $this->m_aData[self::PROPERTY_DATE]->format('<!$SHORT_DATE_FORMAT$!>');
        else
          echo $this->m_aData[self::PROPERTY_DATE]->format('<!$DATE_PICKER_DATE_FORMAT$!>');
        break;
      default:
        echo $this->m_aData[self::PROPERTY_DATE]->format('<!$DATE_PICKER_DATE_FORMAT$!>');
        break;
    }
  }
}

?>
