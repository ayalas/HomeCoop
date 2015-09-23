<?php

//validate numeric-only date formats
class DateTimeValidate {
  
  const IND_STR = 0;
  const IND_IS_NUMERIC = 1;
  const IND_STR_LEN = 2;
  
  protected $m_aShortInfo = NULL;
  protected $m_aLongInfo = NULL;
  protected $m_aDateInfo = NULL;
  
  protected function SetDateObjects($sDatePhpFormat)
  {
    global $g_dNow, $g_oTimeZone;
    $dThisYear = $g_dNow;
    
    $sYear = $dThisYear->format("Y");
        
    $dShort = DateTime::createFromFormat("Y/n/j", $sYear . "/1/1", $g_oTimeZone);
    $dLong = DateTime::createFromFormat("Y/n/j", $sYear . "/12/31", $g_oTimeZone);
    
    $sShortFormated = $dShort->format($sDatePhpFormat);
    $sLongFormated = $dLong->format($sDatePhpFormat);
    
    $this->m_aShortInfo = $this->GatherDateInfo($sShortFormated);
    $this->m_aLongInfo = $this->GatherDateInfo($sLongFormated);
  }
  
  protected function SetTimeObjects($sDatePhpFormat)
  {        
    global $g_oTimeZone;
    $dShort = DateTime::createFromFormat("G:i", "1:00", $g_oTimeZone);
    $dLong = DateTime::createFromFormat("G:i", "23:59", $g_oTimeZone);
    
    $sShortFormated = $dShort->format($sDatePhpFormat);
    $sLongFormated = $dLong->format($sDatePhpFormat);
    
    $this->m_aShortInfo = $this->GatherDateInfo($sShortFormated);
    $this->m_aLongInfo = $this->GatherDateInfo($sLongFormated);
  }
  
  public function ValidateDateString($sDatePhpFormat, $sStrDate)
  {
    if ($sStrDate == NULL || trim($sStrDate) == '')
      return FALSE;
    
    $this->SetDateObjects($sDatePhpFormat);
    
    $this->m_aDateInfo = $this->GatherDateInfo($sStrDate);
    
    if (!$this->ValidateStructure())
      return FALSE;
    
    return TRUE;
  }
  
  public function ValidateTimeString($sDatePhpFormat, $sStrDate)
  {
    if ($sStrDate == NULL || trim($sStrDate) == '')
      return FALSE;
    
    $this->SetTimeObjects($sDatePhpFormat);
    
    $this->m_aDateInfo = $this->GatherDateInfo($sStrDate);
    
    if (!$this->ValidateStructure())
      return FALSE;
    
    if (!$this->ValidateTime())
      return FALSE;
    
    return TRUE;
  }
  
  protected function ValidateStructure()
  {
    //compare the date structures
    $nCountShort = count($this->m_aShortInfo);
    $nCountDate = count($this->m_aDateInfo);
    //if not same count of numeric/non-numeric parts, validation fails
    if ($nCountDate != $nCountShort)
      return FALSE;
    
    for ($index = 0; $index < $nCountShort; $index++) {
      //if a part is numeric at one string and the equivalent part at the other string is not, validation fails
      if ($this->m_aDateInfo[$index][self::IND_IS_NUMERIC] !=  $this->m_aShortInfo[$index][self::IND_IS_NUMERIC])
        return FALSE;
      
      //if a part's length is not between that of the shortest date/time and the longest date/time, validation fails
      if ($this->m_aDateInfo[$index][self::IND_STR_LEN] <  $this->m_aShortInfo[$index][self::IND_STR_LEN] ||
          $this->m_aDateInfo[$index][self::IND_STR_LEN] >  $this->m_aLongInfo[$index][self::IND_STR_LEN]
              )
        return FALSE;
      
      //if a part is not numeric it must be identical (case-insensitive) to the equivalent part in the shortest or longest dates
      if (!$this->m_aDateInfo[$index][self::IND_IS_NUMERIC])
      {
        if (strcasecmp($this->m_aDateInfo[$index][self::IND_STR], $this->m_aShortInfo[$index][self::IND_STR]) !== 0 &&
            strcasecmp($this->m_aDateInfo[$index][self::IND_STR], $this->m_aLongInfo[$index][self::IND_STR]) !== 0   )
         return FALSE;
      }
    }
    
    return TRUE;
  }

  //check if there's a part in the date that includes AM or PM, case insensitive
  //if there is, check if the first numeric part is between 0 to 12 or absent
  //if there isn't, check if the first numeric part is between 0 to 24 or absent
  //check if the second and third numeric parts are between 0 to 59 or absent
  protected function ValidateTime()
  {
    $sFirstNumericPart = NULL;
    $sSecondNumericPart = NULL;
    $sThirdNumericPart = NULL;
    $bAMPM = FALSE;
    
    $nCountDate = count($this->m_aDateInfo);

    for ($index = 0; $index < $nCountDate; $index++) {
      if ($this->m_aDateInfo[$index][self::IND_IS_NUMERIC])
      {
         if ($sFirstNumericPart == NULL)
           $sFirstNumericPart = $this->m_aDateInfo[$index][self::IND_STR];
         else if ($sSecondNumericPart == NULL)
           $sSecondNumericPart = $this->m_aDateInfo[$index][self::IND_STR];
         else if ($sThirdNumericPart == NULL)
           $sThirdNumericPart = $this->m_aDateInfo[$index][self::IND_STR];
         else if ($bAMPM) //if all parts are filled and AM/PM string already found, break from the loop
           break;
      }
      else
      {
        if ( stripos($this->m_aDateInfo[$index][self::IND_STR], "AM") !== FALSE ||
             stripos($this->m_aDateInfo[$index][self::IND_STR], "PM") !== FALSE) 
          $bAMPM = TRUE;
      }
    }

    //validate parts
    if ( $bAMPM )
      if (!$this->ValidateNumericRange($sFirstNumericPart, 0, 12)) return FALSE;
    else
      if (!$this->ValidateNumericRange($sFirstNumericPart, 0, 24)) return FALSE;

    if (!$this->ValidateNumericRange($sSecondNumericPart, 0, 59)) return FALSE;

    if (!$this->ValidateNumericRange($sThirdNumericPart, 0, 59)) return FALSE;

    return TRUE;
  }
  
  protected function ValidateNumericRange($strNum, $nStart, $nEnd)
  {
    //skip nulls
    if ($strNum === NULL) return TRUE;
    
    $nVal = 0 + $strNum;
    
    return ($nVal >= $nStart && $nVal <= $nEnd);
  }
  
  protected function GatherDateInfo($sDate)
  {
    $aReturn = array();
    $nLen = strlen($sDate);
    $nProcessed = 0;
    $aNewPart = NULL;
    
    do
    {
      $aNewPart = $this->GetStringPart(mb_substr($sDate, $nProcessed), $nLen  - $nProcessed);
      
      $nProcessed += $aNewPart[self::IND_STR_LEN];
      
      $aReturn[] = $aNewPart;
      
    } while($nProcessed < $nLen);
    
    return $aReturn;
  }
  
  protected function GetStringPart($sDate, $nLen)
  {
    $bPartIsNumeric = TRUE;
    $bCharIsNumeric = TRUE;
    $sReturn = '';
    $sChar = '';
    for ($index = 0; $index < $nLen; $index++) {
      $sChar = mb_substr($sDate, $index, 1);
      $bCharIsNumeric = (1 === preg_match("/^([0-9])$/", $sChar));
      if ($index == 0)
      {
        $sReturn .= $sChar;
        $bPartIsNumeric = $bCharIsNumeric;
      }
      else if ($bPartIsNumeric == $bCharIsNumeric)
      {
        $sReturn .= $sChar;
      }
      else
       break;
    }
    
    return array($sReturn, $bPartIsNumeric, $index);
  }
}

?>
