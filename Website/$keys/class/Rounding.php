<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
    return;

//round numbers in different methods according to system settings
//used throughout the system
class Rounding {
  const ROUND_TYPE_DEFAULT = 0;
  const ROUND_TYPE_FLOOR = 1;
  const ROUND_TYPE_CEILING = 2;
  const ROUND_TYPE_ROUND_PRECISION_1 = 3;
  const ROUND_TYPE_ROUND_PRECISION_2 = 4;
  const ROUND_TYPE_NO_ROUND = 100;
  
  public static function Round($fValue, $nRoundSetting)
  {
    switch($nRoundSetting)
    {
      case self::ROUND_TYPE_DEFAULT:
        return round($fValue,0);
      case self::ROUND_TYPE_FLOOR:
        return floor($fValue);
      case self::ROUND_TYPE_CEILING:
        return ceil($fValue);
      case self::ROUND_TYPE_ROUND_PRECISION_1:
        return round($fValue,1);
      case self::ROUND_TYPE_ROUND_PRECISION_2:
        return round($fValue,2);
      case self::ROUND_TYPE_NO_ROUND:
        return $fValue;
      default:
        throw new Exception('Invalid round setting in Rounding::Round. The setting is ' . $nRoundSetting);
        break;
    }
  }
}

?>
