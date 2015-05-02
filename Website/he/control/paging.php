<?php

function WritePaging() {
  global $g_BasePageUrl, $g_sPageNumber, $g_sRootRelativePath, $g_sLangDir, $g_aSupportedLanguages, $g_sLangDir,
      $g_nCountRecords;
  
  if (!isset($g_BasePageUrl) || !isset($g_sPageNumber) || !isset($g_nCountRecords) || empty($g_sPageNumber)) {
    return;
  }
  
  $bHasNextPage = ($g_nCountRecords > HOMECOOP_RECORDS_PER_PAGE);
  
  $sDir = NULL;
  if (is_array($g_aSupportedLanguages)) {
    $sDir = $g_aSupportedLanguages[$g_sLangDir][Consts::IND_LANGUAGE_DIRECTION];
  }
  else {
    $sDir = 'ltr';
  }

  $sBackDir = '';
  $sForwardDir = '';
  if ($sDir == 'ltr') {
    $sBackDir = 'left';
    $sForwardDir = 'right';
  }
  else {
    $sBackDir = 'right';
    $sForwardDir = 'left';
  }
  
  $sLinkBase = $g_BasePageUrl;
  //url doesn't have querystring prefix?
  if (strpos($g_BasePageUrl, '?') === FALSE) {
    //add querystring prefix
    $sLinkBase .=  '?';
  }
  else {
    //add querystring argumnet prefix
    $sLinkBase .=  '&';
  }
  //add querystring argumnet start
  $sLinkBase .=  Consts::URLARG_PAGING . '=';
  
  echo '<br/><div class="pager">';
  
  $nPage = intval($g_sPageNumber);
  
  //regard the page that is the most back from the last as the first page 
  if ($nPage < 0 && !$bHasNextPage) {
    $nPage = 1;
    $bHasNextPage = true;
  }
  
  //last page or page greater than 1
  if ($nPage <= 0 || $nPage > 1) {
    //back to first page
    echo '<a href="', $sLinkBase, '1', '" title="העמוד הראשון" ><img src="',
        $g_sRootRelativePath,
        'img/arrow-', $sBackDir, '-double.png" border="0" /></a>';
    
    //back one page - cannot be done when backing from last page to a page with less than max rows
    if ($nPage > 1 || $bHasNextPage) {
      echo '<a href="', $sLinkBase, ($nPage -1), '" title="העמוד הקודם" ><img src="',
        $g_sRootRelativePath,
        'img/arrow-', $sBackDir , '.png" border="0" /></a>&nbsp;';
    }
  }
  
  if ($g_sPageNumber != 'last' && $bHasNextPage) {
    //forward one page
    echo '<a href="', $sLinkBase, ($nPage+1), '" title="העמוד הבא" ><img src="',
        $g_sRootRelativePath,
        'img/arrow-', $sForwardDir, '.png" border="0" /></a>';
    
    //forward to last page
    echo '<a href="', $sLinkBase, 'last', '" title="העמוד האחרון" ><img src="',
      $g_sRootRelativePath,
      'img/arrow-', $sForwardDir, '-double.png" border="0" /></a>';
  }
  
  echo '</div>';
}

WritePaging();


?>
