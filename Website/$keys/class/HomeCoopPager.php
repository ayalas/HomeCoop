<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class HomeCoopPager { 
  const PAGING_IGNORE = 0;
  const PAGING_BREAK_LOOP = 1;
  const PAGING_SKIP_RECORD = 2;
  
  public static function Process($sQuery, $sOrderByClause) {
    $GLOBALS['g_sPageNumber'] = 1;
    global $g_sPageNumber;
        
    if (isset($_GET['pg'])) {
      $g_sPageNumber = $_GET['pg'];
    }
   
    $nPageNum = intval($g_sPageNumber);
    
    $nOffset = 0;
    if ($nPageNum > 1) {
      $nOffset = ($nPageNum -1) * HOMECOOP_RECORDS_PER_PAGE;
    }
    else if ($nPageNum < 0) {
      $nOffset = (ABS($nPageNum)) * HOMECOOP_RECORDS_PER_PAGE;
    }
    
    //process last page (or back from last page) request
    if ($nPageNum <= 0) {
      return 'SELECT * FROM (' . $sQuery . self::FlipOrderBy($sOrderByClause) . ' LIMIT ' . $nOffset . ', ' . (HOMECOOP_RECORDS_PER_PAGE+1) . ') AS PagerT1 ' .
          $sOrderByClause;
    }
    
    //other requests
    
    return $sQuery . $sOrderByClause . ' LIMIT ' . $nOffset . ', ' . (HOMECOOP_RECORDS_PER_PAGE+1);    
  }
  
  public static function IterateRecordForPaging() {
    global $g_nCountRecords, $g_sPageNumber;
    $nPageNum = intval($g_sPageNumber);
    
    $g_nCountRecords++;
    if ($g_nCountRecords > HOMECOOP_RECORDS_PER_PAGE && $nPageNum >= 1) {
      //do not display the row over the page reocrds - it's for checking if there is a next page
      //need to determine whether to skip the first row (reverse order - going from last page backward), 
      //or break before last record (regular order)
      return self::PAGING_BREAK_LOOP;
    }
    elseif ($g_nCountRecords == 1 && $nPageNum <= 0) {
      return self::PAGING_SKIP_RECORD;
    }
    
    return self::PAGING_IGNORE;
  }
  
  private static function FlipOrderBy($sOrderByClause) {
    $sTemp = str_ireplace(' ASC', ' A-S-C', $sOrderByClause);
    $sTemp = str_ireplace(' DESC', ' ASC', $sTemp);
    return str_ireplace(' A-S-C', ' DESC', $sTemp);
  }
  
  
  
  
}

?>
