<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

class TransactionTableHtml extends SQLBase {
  const PROPERTY_TABLE = 'TableObject';
  
  public function __construct($oTableObj)
  {
    $this->m_aData = array( 
        self::PROPERTY_TABLE => $oTableObj,
       );
  }
  
  public function EchoHtml($oRec)
  {
    global $g_oTimeZone, $g_nCountRecords;
    
    if (!$oRec)
    {
      echo '<div class="norecords"><!$NO_RECORD_FOUND$!></div>';
      return;
    }
    
    $sThisYear = HtmlDateString::GetThisYear();
    $bHasMemberCol = empty($this->m_aData[self::PROPERTY_TABLE]->{TransactionTable::PROPERTY_FILTER_MEMBER_ID});
    $bHasPLCol = empty($this->m_aData[self::PROPERTY_TABLE]->{TransactionTable::PROPERTY_FILTER_PICKUP_LOCATION_ID});        
    $bPrintedHeaders = false;
    
    while($oRec)
    {
      $retIterate = HomeCoopPager::IterateRecordForPaging();
      if ($retIterate == HomeCoopPager::PAGING_SKIP_RECORD) {
        $oRec = $this->m_aData[self::PROPERTY_TABLE]->fetch();
        continue;
      }
      else if ($retIterate == HomeCoopPager::PAGING_BREAK_LOOP) {
        break;
      }

      echo '<div class="resgridrow">';
      
      echo '<div class="resgridcell">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_TIME$!>');
            
      echo '<div class="resgriddatahlong">';
      $oDate = new DateTime($oRec["dDate"], $g_oTimeZone);
      if (($oDate->format('Y')+0) == $sThisYear)
        echo $oDate->format('<!$FULL_DATE_FORMAT_CURRENT_YEAR$!>');
      else
        echo $oDate->format('<!$FULL_DATE_FORMAT_ANY_YEAR$!>');
      echo '</div>'; //data
      echo '</div>'; //cell
      
      if ($bHasMemberCol)
      {
        echo '<div class="resgridcell">';
        HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_MEMBER_NAME$!>');
        echo '<div class="resgriddatahlong">', htmlspecialchars($oRec['MemberName']),  '</div>';
        echo '</div>';
      }
      if ($bHasPLCol)
      {        
        echo '<div class="resgridcell">';
        HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_PICKUP_LOCATION_NAME$!>');
        echo '<div class="resgriddatalong">', htmlspecialchars($oRec['sPickupLocation']),  '</div>';
        echo '</div>';
      }
            
      echo '<div class="resgridcell numericcolumn">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_AMOUNT$!>');
      echo '<div class="resgriddatashort" dir="ltr">', $oRec['mAmount'],  '</div>';
      echo '</div>';
      
      echo '<div class="resgridcell">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_TRANSACTION$!>');
      echo '<div class="resgriddatalong">', htmlspecialchars($oRec['sTransaction']),  '</div>';
      echo '</div>';
      
      echo '<div class="resgridcell">';

      HtmlDivTable::EchoTitle($bPrintedHeaders, '<!$FIELD_COORDINATOR$!>');
      echo '<div class="resgriddatahlong">', htmlspecialchars($oRec['ModifierName']),  '</div>';
      echo '</div>';
        
      echo '</div>';

      $bPrintedHeaders = true;
      $oRec = $this->m_aData[self::PROPERTY_TABLE]->fetch();
    }
  }
}

?>
