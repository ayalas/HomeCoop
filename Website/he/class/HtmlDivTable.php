<?php

if(realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
   return;

/*
 * Helper functions for producing mobile friendly Html Div table
 */
class HtmlDivTable {
  public static function EchoTitle($bPrintedHeaders, $sTitle)
  {
    echo '<div class="resgridtitle'; 
    if ($bPrintedHeaders)
      echo ' mobiledisplay';
    echo '">', $sTitle, '</div>';
  }
}

?>
