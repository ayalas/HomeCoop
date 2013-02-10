<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;


//outputs to htmldocument a set of HtmlTextEdit outputs, for all supported languages
class HtmlTextEditMultiLang {
  
  const PROPERTY_ID = "ID";
  const PROPERTY_LABEL = "Label";
  const PROPERTY_VALUES = "Values";
  const PROPERTY_REQUIRED = "Required";
  const PROPERTY_READ_ONLY = "ReadOnly";
  const PROPERTY_MAX_LENGTH = "MaxLength";

  const ID_LINK = "-";
  
  protected $m_aData = NULL;
  
  public function __construct($sLabel, $sIdPrefix, $nType, $arrValues) {
    $this->m_aData = array(
         self::PROPERTY_ID => $sIdPrefix,
         HtmlTextEdit::PROPERTY_TYPE => $nType,
         self::PROPERTY_LABEL => $sLabel,
         self::PROPERTY_VALUES => $arrValues,
         self::PROPERTY_REQUIRED => FALSE,
         self::PROPERTY_READ_ONLY => FALSE,
         self::PROPERTY_MAX_LENGTH => NULL
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
  
  public function EchoHtml()
  {
    global $g_aSupportedLanguages;
    global $g_sLangDir;
    
    $oTextEdit = NULL;
        
    if ( is_array($g_aSupportedLanguages) && count($g_aSupportedLanguages) > 0 )
    {
      $sID = $this->m_aData[self::PROPERTY_ID] . self::ID_LINK . $g_sLangDir;
      echo '<td nowrap><label ';
      if ($this->m_aData[self::PROPERTY_REQUIRED])
        echo ' class="required" ';
      echo 'for="' , $sID , '">' , $this->m_aData[self::PROPERTY_LABEL] , '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label></td>';
      //first get current language
      $oTextEdit = new HtmlTextEdit( $sID, $g_aSupportedLanguages[$g_sLangDir][Consts::IND_LANGUAGE_DIRECTION], 
              $this->m_aData[HtmlTextEdit::PROPERTY_TYPE], $this->GetLangPropertyVal(self::PROPERTY_VALUES,$g_sLangDir) );
      $oTextEdit->MaxLength = $this->m_aData[self::PROPERTY_MAX_LENGTH];
      $oTextEdit->ReadOnly = $this->m_aData[self::PROPERTY_READ_ONLY];
      $oTextEdit->Required = $this->m_aData[self::PROPERTY_REQUIRED];    
      $oTextEdit->EchoEditPartHtml();
      
      foreach( $g_aSupportedLanguages as $lkey => $larr)
      {
        if ($lkey != $g_sLangDir)
        {
          $sID = $this->m_aData[self::PROPERTY_ID] . self::ID_LINK . $lkey;
          
          $oTextEdit = new HtmlTextEdit($sID, $larr[Consts::IND_LANGUAGE_DIRECTION], 
              $this->m_aData[HtmlTextEdit::PROPERTY_TYPE], $this->m_aData[self::PROPERTY_VALUES][ $lkey ] );
          $oTextEdit->MaxLength = $this->m_aData[self::PROPERTY_MAX_LENGTH];
          $oTextEdit->ReadOnly = $this->m_aData[self::PROPERTY_READ_ONLY];
          $oTextEdit->Required = $this->m_aData[self::PROPERTY_REQUIRED] && $larr[Consts::IND_LANGUAGE_REQUIRED];     
          $oTextEdit->EchoEditPartHtml();
        }
      }
    }
    else
    {
      echo '<td nowrap><label ';
      if ($this->m_aData[self::PROPERTY_REQUIRED])
        echo ' class="required" ';
      echo 'for="' , $this->m_aData[self::PROPERTY_ID] , '">' , $sLabel , '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label></td>';
      
      $oTextEdit = new HtmlTextEdit($this->m_aData[self::PROPERTY_ID], NULL, 
          $this->m_aData[HtmlTextEdit::PROPERTY_TYPE], $this->m_aData[self::PROPERTY_VALUES][ 0 ] );
      $oTextEdit->MaxLength = $this->m_aData[self::PROPERTY_MAX_LENGTH];
      $oTextEdit->ReadOnly = $this->m_aData[self::PROPERTY_READ_ONLY];
      $oTextEdit->Required = $this->m_aData[self::PROPERTY_REQUIRED];
      $oTextEdit->EchoEditPartHtml();
    }
    
    unset($oTextEdit);
  }
  
  //echo directly to html document to save some string concats/retrieval
  public static function EchoColumnHeaders()
  {
    global $g_aSupportedLanguages;
    global $g_sLangDir;
    
    if ( is_array($g_aSupportedLanguages) && count($g_aSupportedLanguages) > 0 )
    {
        //first get current language
        echo "<td class='coordlangtitle' ><span>" , $g_aSupportedLanguages[$g_sLangDir][Consts::IND_LANGUAGE_NAME] , "</span></td>";
      
        foreach($g_aSupportedLanguages as $key => $aLang)
        {
          if ($key != $g_sLangDir)
            echo "<td class='coordlangtitle' ><span>" , $aLang[Consts::IND_LANGUAGE_NAME]  , "</span></td>";
        }
    }
    else
      echo "<td></td>";
  }
  
  public static function OtherLangsEmptyCells()
  {
    global $g_aSupportedLanguages;
    
    $nCount = 0;   
    
    if ( is_array($g_aSupportedLanguages) )
      $nCount = count($g_aSupportedLanguages);

    if ( $nCount > 0 )
      echo '<td colspan="' . ($nCount) . '" ></td>';
  }
  
  public static function EchoHelpText($sHelpText)
  {
    global $g_aSupportedLanguages;
        
    $nCount = 0;   
    
    if ( is_array($g_aSupportedLanguages) )
      $nCount = count($g_aSupportedLanguages);

    echo '<td';
    
    if ( $nCount > 0 )
      echo ' colspan="' , $nCount , '" ';
    
    echo '><a class="tooltiphelp" href="#" ><!$HELP_SIGN$!><span>' , $sHelpText,
      '</span></a></td>';
   
  }
  
  protected function GetLangPropertyVal($PropertyID, $sLang)
    {
      if ( isset( $this->m_aData[$PropertyID][$sLang] ))
        return $this->m_aData[$PropertyID][$sLang];
      
      return '';
    }
}

?>
