<?php

//utility functions for collecting post data
class ComplexPostData {
  
  //get strings from multiple langauges textboxes (HtmlTextEditMultiLang)
  public static function GetNames($sID)
  {
    global $g_aSupportedLanguages;
    global $_POST;
    global $g_sLangDir;
    
    $nCount = 0;
    
    if (is_array($g_aSupportedLanguages))
      $nCount = count($g_aSupportedLanguages);
    
    if ($nCount > 0)
    {
      $aValues = array();

      foreach( $g_aSupportedLanguages as $sLKey => $aLang )
      {       
          //add a new key-value pair to the array
          $aValues[ $sLKey ] = ComplexPostData::GetName($sID, $sLKey);
      }
      
      return $aValues;
    }
    else
    {
      if ( isset( $_POST[$sID] ) )
        return array( trim($_POST[$sID]) );
    }
    
    return NULL;
  }
  
  //helper function: get a string, in a specific language, from multiple langauges textboxes (HtmlTextEditMultiLang)
  protected static function GetName($sID, $sLKey)
  {
    global $_POST;
    $sCtlName = $sID . HtmlTextEditMultiLang::ID_LINK . $sLKey;
        
    $sString = NULL;
    if ( isset( $_POST[$sCtlName] ) )
     $sString = trim($_POST[$sCtlName]);

    return $sString;
  }
  
  //get and validate date entry from the date picker (HtmlDatePicker)
  //uses DateTime::createFromFormat which requires php 5.3
  public static function GetDate($sIDSuffix)
  {
    global $_POST;
    global $g_oTimeZone;
    
    $sID = HtmlDatePicker::PREFIX . $sIDSuffix;
        
    $sString = NULL;
    if ( isset( $_POST[$sID] ) && !empty($_POST[$sID]) )
     $sString = trim($_POST[$sID]);
    else
      return NULL;
    
    $nLen = strlen($sString);
    
    if ($nLen == 0)
      return NULL;
    
    $oValidate = new DateTimeValidate;
    if (!$oValidate->ValidateDateString('j.n.Y', $sString))
      return NULL;
    
    return DateTime::createFromFormat('j.n.Y', $sString, $g_oTimeZone);
  }
  
  //get and validate date + time entry from the date picker (HtmlDatePicker)
  //$aDefaultTime: can be NULL or array of 3 elements: hours, minutes, seconds
  //uses DateTime::createFromFormat which requires php 5.3
  public static function GetDateTime($sIDSuffix, $aDefaultTime)
  {
    global $_POST;
    global $g_oTimeZone;
    
    $dDate = self::GetDate($sIDSuffix);
    if ($dDate == NULL)
      return NULL;
    
    $sTimeID = HtmlDatePicker::TIME_PREFIX . $sIDSuffix;
        
    if ( isset( $_POST[$sTimeID] ) && !empty($_POST[$sTimeID]) )
    {
     $sString = trim($_POST[$sTimeID]);
     
     $oValidate = new DateTimeValidate;
     if (!$oValidate->ValidateTimeString('G:i', $sString))
       return NULL;

     $dTime = DateTime::createFromFormat('G:i', $sString, $g_oTimeZone);
     $dDate->setTime($dTime->format('G')+0, $dTime->format('i')+0, $dTime->format('s')+0);
    }
    else if ($aDefaultTime != NULL)
      $dDate->setTime($aDefaultTime[0], $aDefaultTime[1], $aDefaultTime[2]);
    
    return $dDate;
  }
  
}

?>
