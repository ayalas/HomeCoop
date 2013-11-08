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
  const PROPERTY_LABEL_SLOT_IS_HTML = "UseLabelSlotAsHtml";

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
         self::PROPERTY_MAX_LENGTH => NULL,
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
  
  public function EchoHtml()
  {
    global $g_aSupportedLanguages;
    global $g_nCountLanguages;
    global $g_sLangDir;
    
    $oTextEdit = NULL;
     
    //using language dirs?
    if ( $g_nCountLanguages > 0 )
    {
    
      $sID = $this->m_aData[self::PROPERTY_ID] . self::ID_LINK . $g_sLangDir;
      echo '<td nowrap>';
      
      if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
        echo $this->m_aData[self::PROPERTY_LABEL];
      else
      {
        echo '<label ';
        if ($this->m_aData[self::PROPERTY_REQUIRED])
          echo ' class="required" ';
        echo 'for="' , $sID , '">' , $this->m_aData[self::PROPERTY_LABEL] , '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label>';
      }
      
      echo '</td>';
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
              $this->m_aData[HtmlTextEdit::PROPERTY_TYPE], $this->GetLangPropertyVal(self::PROPERTY_VALUES, $lkey ) );
          $oTextEdit->MaxLength = $this->m_aData[self::PROPERTY_MAX_LENGTH];
          $oTextEdit->ReadOnly = $this->m_aData[self::PROPERTY_READ_ONLY];
          $oTextEdit->Required = $this->m_aData[self::PROPERTY_REQUIRED] && $larr[Consts::IND_LANGUAGE_REQUIRED];     
          $oTextEdit->EchoEditPartHtml();
        }
      }
    }
    else
    {
      echo '<td nowrap>';
      
      if ($this->m_aData[self::PROPERTY_LABEL_SLOT_IS_HTML])
        echo $this->m_aData[self::PROPERTY_LABEL];
      else
      {
        echo '<label ';
        if ($this->m_aData[self::PROPERTY_REQUIRED])
          echo ' class="required" ';
        echo 'for="' , $this->m_aData[self::PROPERTY_ID] , '">' , $this->m_aData[self::PROPERTY_LABEL] , 
            '<!$FIELD_DISPLAY_NAME_SUFFIX$!></label>';
      }
      
      echo '</td>';
      
      $oOneLangValue = NULL;
      if (isset($this->m_aData[self::PROPERTY_VALUES][ 0 ]))
          $oOneLangValue = $this->m_aData[self::PROPERTY_VALUES][ 0 ];
      
      $oTextEdit = new HtmlTextEdit($this->m_aData[self::PROPERTY_ID], NULL, 
          $this->m_aData[HtmlTextEdit::PROPERTY_TYPE], $oOneLangValue );
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
    global $g_nCountLanguages;
    global $g_sLangDir;
    
    //using language dirs?
    if ( $g_nCountLanguages > 0 )
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
    {
        echo '<td></td>';
    }
  }
  
  public static function OtherLangsEmptyCells()
  {
    global $g_nCountLanguages;
    
    if ( $g_nCountLanguages > 1 )
      echo '<td colspan="' , ($g_nCountLanguages -1) , '" ></td>';
  }
  
  public static function EchoHelpText($sHelpText)
  {        
    echo '<td><a class="tooltiphelp" href="#" ><!$HELP_SIGN$!><span class="helpspan">' , $sHelpText,
      '</span></a></td>';
  }
  
  public static function EchoSeparatorLine()
  {
    global $g_nCountLanguages;
    
    $nCount = 2; //help column + label column
    if ($g_nCountLanguages == 0)
      $nCount++; //one language deployment
    else
      $nCount+= $g_nCountLanguages;
      
    echo '<tr><td colspan="' , $nCount , '" ><div class="sepline"></div></td></tr>';
  }
  
  public static function EchoTitleLine($sTitle)
  {
    global $g_nCountLanguages;
    
    $nCount = 2; //help column + label column
    if ($g_nCountLanguages == 0)
      $nCount++; //one language deployment
    else
      $nCount+= $g_nCountLanguages;
      
    echo '<tr><td colspan="' , $nCount , '" ><div class="titleline">', 
        htmlspecialchars($sTitle), '</div></td></tr>';
  }
  
  protected function GetLangPropertyVal($PropertyID, $sLang)
    {
      if ( isset( $this->m_aData[$PropertyID][$sLang] ))
        return $this->m_aData[$PropertyID][$sLang];
      
      return '';
    }
}

?>
