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
    global $g_oTimeZone;
    
    if (!$oRec)
    {
      echo '<div class="norecords">No records.</div>';
      return;
    }
    
    $sThisYear = HtmlDateString::GetThisYear();
    $bHasMemberCol = empty($this->m_aData[self::PROPERTY_TABLE]->{TransactionTable::PROPERTY_FILTER_MEMBER_ID});
    $bHasPLCol = empty($this->m_aData[self::PROPERTY_TABLE]->{TransactionTable::PROPERTY_FILTER_PICKUP_LOCATION_ID});        
    $bPrintedHeaders = false;
    
    while($oRec)
    {
      echo '<div class="resgridrow">';
      
      echo '<div class="resgridcell">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, 'Time');
            
      echo '<div class="resgriddatahlong">';
      $oDate = new DateTime($oRec["dDate"], $g_oTimeZone);
      if (($oDate->format('Y')+0) == $sThisYear)
        echo $oDate->format('n.j g:i A');
      else
        echo $oDate->format('n.j.Y g:i A');
      echo '</div>'; //data
      echo '</div>'; //cell
      
      if ($bHasMemberCol)
      {
        echo '<div class="resgridcell">';
        HtmlDivTable::EchoTitle($bPrintedHeaders, 'Name');
        echo '<div class="resgriddatahlong">', htmlspecialchars($oRec['MemberName']),  '</div>';
        echo '</div>';
      }
      if ($bHasPLCol)
      {        
        echo '<div class="resgridcell">';
        HtmlDivTable::EchoTitle($bPrintedHeaders, 'Location Name');
        echo '<div class="resgriddatalong">', htmlspecialchars($oRec['sPickupLocation']),  '</div>';
        echo '</div>';
      }
            
      echo '<div class="resgridcell numericcolumn">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, 'Amount');
      echo '<div class="resgriddatashort" dir="ltr">', $oRec['mAmount'],  '</div>';
      echo '</div>';
      
      echo '<div class="resgridcell">';
      HtmlDivTable::EchoTitle($bPrintedHeaders, 'Transaction Desc.');
      echo '<div class="resgriddatalong">', htmlspecialchars($oRec['sTransaction']),  '</div>';
      echo '</div>';
      
      echo '<div class="resgridcell">';

      HtmlDivTable::EchoTitle($bPrintedHeaders, 'Coordinator');
      echo '<div class="resgriddatahlong">', htmlspecialchars($oRec['ModifierName']),  '</div>';
      echo '</div>';
        
      echo '</div>';

      $bPrintedHeaders = true;
      $oRec = $this->m_aData[self::PROPERTY_TABLE]->fetch();
    }
  }
}

?>
