<?php

include_once '../settings.php';
include_once 'configure.php'; //sets parameters for php date picker

$dtNow = $g_dNow; 

$sUrl = 'calendar.php';

$ctl = 'date';

if (isset($_GET['ctl']))
    $ctl = $_GET['ctl'];

$sUrl .= "?ctl=" . $ctl;

$month = NULL;
if (isset($_GET['month']))
    $month = $_GET['month'];
else
   $month = $dtNow->format('n');

$year = NULL;
if (isset($_GET['year']))
   $year = $_GET['year'];
else
   $year = $dtNow->format('Y');


?>
<!DOCTYPE HTML>
<html dir="ltr">
<head>
<title>Select a Date</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="copyright" content="(c) 2005 separd" />
<link href="style.css" type="text/css" rel="stylesheet" />
<script type="text/javascript">

function insertdate(d) {
  window.close();
  window.opener.document.getElementById('<?php echo $ctl; ?>').value = d;
}
</script>
</head>
<body>
<?php


$dFirstDayOfMonth = new DateTime("now",$g_oTimeZone );
$dFirstDayOfMonth->setDate($year, $month, 1); // get the first day of the month

//translate first day of the week to a (zero based) value correlating to php date(w', [date]) returned value
$nZeroBasedFirstDayOfWeek = $first_day_of_week - 1; 
$nZeroBasedLastDayOfWeek = $nZeroBasedFirstDayOfWeek - 1;
if ($nZeroBasedLastDayOfWeek < 0)
  $nZeroBasedLastDayOfWeek = 6;

//a value between 0 (sunday) to 6 (saturday)
$nMonthBeginDayOfWeek = intval($dFirstDayOfMonth->format('w'));

//$nFirstDayOfCal should be a value from -5 (month begin is last day of week). to 1 (month begin is 1st day of week)
//for example, if $nZeroBasedFirstDayOfWeek = 1 (Monday)
//and $nMonthBeginDayOfWeek = 1 (Mon), then $nFirstDayOfCa = 1
//and $nMonthBeginDayOfWeek = 2 (Tue), then $nFirstDayOfCa = 0
//and $nMonthBeginDayOfWeek = 3 (Wed), then $nFirstDayOfCa = -1
//and $nMonthBeginDayOfWeek = 4 (Thu), then $nFirstDayOfCa = -2
//and $nMonthBeginDayOfWeek = 5 (Fri), then $nFirstDayOfCa = -3
//and $nMonthBeginDayOfWeek = 6 (Sat), then $nFirstDayOfCa = -4
//and $nMonthBeginDayOfWeek = 0 (San), then $nFirstDayOfCa = -5

$nFirstDayOfCal = 1; //good for nZeroBasedFirstDayOfWeek === nMonthBeginDayOfWeek
$aCalIndex = array( 1, 0, -1, -2, -3, -4, -5);
if ($nZeroBasedFirstDayOfWeek > $nMonthBeginDayOfWeek)
{
  $aArrReverse = array_reverse($aCalIndex);
  $nFirstDayOfCal = $aArrReverse[$nZeroBasedFirstDayOfWeek - $nMonthBeginDayOfWeek - 1];
}
else if ($nZeroBasedFirstDayOfWeek < $nMonthBeginDayOfWeek)
    $nFirstDayOfCal = $aCalIndex[$nMonthBeginDayOfWeek - $nZeroBasedFirstDayOfWeek]; //start from end

$nLastDayOfMonth = intval($dFirstDayOfMonth->format('t')); // last day of month
echo '<div class="month_title">'
        , '<a title="Previous Month" href="';

$dDate = clone $dFirstDayOfMonth;

//$dDate->modify("-1 month"); //cancelled: php 5.2 style
$dDate->sub( new DateInterval("P1M"));
$pmonth = $dDate->format('n');
$pyear = $dDate->format('Y');

$sUrlPrev =  $sUrl . '&month=' . $pmonth . '&year=' . $pyear;

echo $sUrlPrev ,'" class="month_move">«</a>'
        , '<div class="month_name">',$month_names[ intval($dFirstDayOfMonth->format('n'))],
        ' ',
        $dFirstDayOfMonth->format('Y'),
        '</div>'
        , '<a title="Next Month" href="';
$dDate = clone $dFirstDayOfMonth;

//$dDate->modify("+1 month");  //cancelled: php 5.2 style
$dDate->add( new DateInterval("P1M"));
$nmonth = $dDate->format('n');
$nyear = $dDate->format('Y');


echo $sUrl , '&month=' , $nmonth , '&year=' , $nyear , '" class="month_move">»</a>' ,
        '<div class="r"></div>' ,
    '</div>';
for ($d=0;$d<7;$d++) {
  echo '<div class="week_day">',$day_names[$d],'</div>';
}
echo '<div class="r"></div>';

$dTodayStart = clone $dtNow;
$dTodayStart->setTime(0,0,0);

$sToday = $dtNow->format('Ymd');
$sDate = '';
$today = '';
$diFirstDayOfMonth = NULL;
$diToday = NULL;

for ($d=$nFirstDayOfCal;$d<=$nLastDayOfMonth;$d++) 
{
  $dDate->setDate($year, $month, $d);
  $sDate = $dDate->format('Ymd');
  $diFirstDayOfMonth = $dFirstDayOfMonth->diff($dDate);
  if ($diFirstDayOfMonth->invert == 0) 
  {
    if ($sDate == $sToday)
      $today = '_today';
    else
      $today = '';
    
    $diToday = $dDate->diff( $dTodayStart );

    echo '<div class="day',$today,'">';
    if ($diToday->invert == 0 && !$allow_past)
    {
      echo $dDate->format('j'),'</div>'; //just write the day number if not allowing to go to past days
    }
    else //otherwise, write the day number with a link
    {
      echo '<a title="', $dDate->format($date_format), '" href="javascript:insertdate(\'',
              $dDate->format($date_format), '\')">', $dDate->format('j'),'</a>','</div>';
    }
    
    if (intval($dDate->format('w')) == $nZeroBasedLastDayOfWeek) {
     echo '<div class="r"></div>'; //ends a row
    }
  } 
  else {
      echo '<div class="no_day">&nbsp;</div>';
  }
}
?>
</body>
</html>
<?php

//close DB connection and release memory
if ($g_oDBAccess != NULL)
  $g_oDBAccess->Close();
unset($g_oDBAccess);
unset($g_oTimeZone);
unset($g_dNow);
unset($g_aSupportedLanguages);
unset($g_oError);

?>
