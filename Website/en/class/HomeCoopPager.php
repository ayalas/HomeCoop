<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class HomeCoopPager { 
  public static function Process($sQuery, $sOrderByClause) {
    $GLOBALS['g_sPageNumber'] = 1;
    global $g_sPageNumber;
        
    if (isset($_GET['pg'])) {
      $g_sPageNumber = $_GET['pg'];
    }
   
    $nPageNum = intval($g_sPageNumber);
    
    $nOffset = 0;
    if (ABS($nPageNum) > 1) {
      $nOffset = (ABS($nPageNum) -1) * HOMECOOP_RECORDS_PER_PAGE;
    }
    
    //process last page (or back from last page) request
    if ($nPageNum <= 0) {
      return 'SELECT * FROM (' . $sQuery . self::FlipOrderBy($sOrderByClause) . ' LIMIT ' . $nOffset . ', ' . (HOMECOOP_RECORDS_PER_PAGE+1) . ') AS PagerT1 ' .
          $sOrderByClause;
    }
    
    //other requests
    
    return $sQuery . $sOrderByClause . ' LIMIT ' . $nOffset . ', ' . (HOMECOOP_RECORDS_PER_PAGE+1);    
  }
  
  private static function FlipOrderBy($sOrderByClause) {
    $sTemp = str_ireplace(' ASC', ' A-S-C', $sOrderByClause);
    $sTemp = str_ireplace(' DESC', ' ASC', $sTemp);
    return str_ireplace(' A-S-C', ' DESC', $sTemp);
  }
  
  
}

?>
