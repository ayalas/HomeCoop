<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

//output storage area row in pickup location admin page
class HtmlStorageArea {
  
  const PROPERTY_STORAGE_AREA = "StorageArea";
  const PROPERTY_LINE_NUMBER = "LineNumber";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_IS_NEW = "IsNew";
  const CTL_NAME_PREFIX = 'txtStorageAreaName_';
  const CTL_NEW_NAME_PREFIX = 'txtnewStorageAreaName_';
  const CTL_DISABLED_PREFIX = 'ctlSAIsDisabled_';
  const CTL_NEW_DISABLED_PREFIX = 'ctlnewSAIsDisabled_';
  const CTL_DELETE_PREFIX = 'chkDeleteStorageArea_';
  const CTL_MAX_BURDEN_PREFIX = 'txtMaxBurden_';
  const CTL_NEW_MAX_BURDEN_PREFIX = 'txtnewMaxBurden_';
  const CTL_DEFAULT_GROUP = 'radDefaultStorage';
  const CTL_DEFAULT_PREFIX = 'radDefaultStorage_';
  const CTL_NEW_DEFAULT_PREFIX = 'radnewDefaultStorage_';
  
  const MIN_NEW_CONTROLS_NUM = 2000000000;
  
  protected $m_aData = array( self::PROPERTY_STORAGE_AREA => NULL,
                              self::PROPERTY_LINE_NUMBER => 1,
                              self::PROPERTY_IS_NEW => FALSE,
                              self::PROPERTY_REQUIRED => TRUE,
                      );
  
  public function __construct($arrSAID = NULL, $nLineNumber = 1) {
    $this->m_aData[self::PROPERTY_STORAGE_AREA] = $arrSAID;
    $this->m_aData[self::PROPERTY_LINE_NUMBER] = $nLineNumber;
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
    
    public function EchoHtml()
    {
      echo '<tr>';
      
      $txtStorage = NULL;
      $said  = 0;
      $oNewValue = NULL;
      if ($this->m_aData[self::PROPERTY_IS_NEW])
      {
        $oNewValue = array();
        if (isset($this->m_aData[self::PROPERTY_STORAGE_AREA]['Names']))
          $oNewValue = $this->m_aData[self::PROPERTY_STORAGE_AREA]['Names'];
        $txtStorage = new HtmlTextEditMultiLang('מקום אחסון', 
          self::CTL_NEW_NAME_PREFIX . $this->m_aData[self::PROPERTY_LINE_NUMBER], HtmlTextEdit::TEXTBOX, 
            $oNewValue
            );
      }
      else
      {
        $said = $this->m_aData[self::PROPERTY_STORAGE_AREA]['StorageAreaKeyID'];
        $txtStorage = new HtmlTextEditMultiLang(sprintf('מקום אחסון #%s', $this->m_aData[self::PROPERTY_LINE_NUMBER]), 
          self::CTL_NAME_PREFIX . $said, HtmlTextEdit::TEXTBOX, $this->m_aData[self::PROPERTY_STORAGE_AREA]['Names']);
        
      }
      
      $txtStorage->Required = $this->m_aData[self::PROPERTY_REQUIRED];
      $txtStorage->EchoHtml();
      
      echo '<td></td></tr><tr>';
      
      if ($this->m_aData[self::PROPERTY_IS_NEW])
      {
        $oNewValue = NULL;
        if (isset($this->m_aData[self::PROPERTY_STORAGE_AREA]['fMaxBurden']))
          $oNewValue = $this->m_aData[self::PROPERTY_STORAGE_AREA]['fMaxBurden'];
        
        $txtMaxBurden = new HtmlTextEditNumeric('קיבולת אחסון', 
            self::CTL_NEW_MAX_BURDEN_PREFIX . $this->m_aData[self::PROPERTY_LINE_NUMBER], $oNewValue);
        $txtMaxBurden->EchoHtml();
      }
      else
      {
        $txtMaxBurden = new HtmlTextEditNumeric('קיבולת אחסון', 
            self::CTL_MAX_BURDEN_PREFIX . $said, $this->m_aData[self::PROPERTY_STORAGE_AREA]['fMaxBurden']);
        $txtMaxBurden->EchoHtml();
      }
      
      HtmlTextEditMultiLang::EchoHelpText('הקבולת המקסימאלית של מקום האחסון במונחים של שדה המוצר &quot;מעמסה&quot;. הסכום של המעמסות של כל המוצרים כפול הכמות שהוזמנה מכל מוצר יושווה לערך זה עבור כל ההזמנות של מוצרים המיועדים למקום אחסון זה. זהו רק ערך ברירת מחדל, וניתן להחליפו בהגדרות מקום האחסון של הזמנת הקואופרטיב.', $said);
      HtmlTextEditMultiLang::OtherLangsEmptyCells();

      echo '</tr><tr>';
      
      if ($this->m_aData[self::PROPERTY_IS_NEW])
      {
        $oNewValue = FALSE;
        if (isset($this->m_aData[self::PROPERTY_STORAGE_AREA]['bDisabled']))
          $oNewValue = $this->m_aData[self::PROPERTY_STORAGE_AREA]['bDisabled'];
        
        $selIsDisabled = new HtmlSelectBoolean(self::CTL_NEW_DISABLED_PREFIX . $this->m_aData[self::PROPERTY_LINE_NUMBER], '', $oNewValue, 'לא פעיל', 
            'פעיל');
      }
      else
      {
        $selIsDisabled = new HtmlSelectBoolean(self::CTL_DISABLED_PREFIX . $said, 

          '<input type="checkbox" value="1" id="' . self::CTL_DELETE_PREFIX . $said .
          '" name="' . self::CTL_DELETE_PREFIX .  $said . '">מחיקה</input>',

          $this->m_aData[self::PROPERTY_STORAGE_AREA]['bDisabled'], 'לא פעיל', 
              'פעיל');
      }
      
      $selIsDisabled->UseLabelSlotAsHtml = TRUE;

      $selIsDisabled->EchoHtml();
      
      if ($this->m_aData[self::PROPERTY_IS_NEW])
      {
        $oNewValue = ' checked="true" ';
        if (isset($this->m_aData[self::PROPERTY_STORAGE_AREA]['bDefault']) && !$this->m_aData[self::PROPERTY_STORAGE_AREA]['bDefault'])
          $oNewValue = '';
        
        echo '<td><input type="radio" value="', (self::MIN_NEW_CONTROLS_NUM + $this->m_aData[self::PROPERTY_LINE_NUMBER]), 
            '" id="', self::CTL_NEW_DEFAULT_PREFIX, $this->m_aData[self::PROPERTY_LINE_NUMBER],'" name="',
            self::CTL_DEFAULT_GROUP, '" ', $oNewValue ,  ' /><span>ברירת מחדל</span></td>';
      }
      else
      {
        echo '<td><input type="radio" value="', $said, 
            '" id="', self::CTL_DEFAULT_PREFIX, $said,'" name="',
            self::CTL_DEFAULT_GROUP, '" ';
        if ($this->m_aData[self::PROPERTY_STORAGE_AREA]['bDefault'])
          echo ' checked="true" ';
        echo '/><span>ברירת מחדל</span></td>';
        
      }
      
      HtmlTextEditMultiLang::OtherLangsEmptyCells();
      
      echo '</tr>';
    }
    
    
}

?>
