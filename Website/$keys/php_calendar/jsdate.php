<?php

include_once '../settings.php';

//this script is used in ajax request to return php formatted date according to language-specific setting of DATE_PICKER_DATE_FORMAT
if (!isset($_GET['d']) || empty($_GET['d']))
{
  echo 'NaN';
  return;
}

try
{
  $dDate = DateTime::createFromFormat('<!$DATE_PICKER_DATE_FORMAT$!>', $_GET['d'], $g_oTimeZone);
  if ($dDate !== FALSE)
    echo $dDate->format('Y-m-d');
  else
    echo 'NaN';
}
catch(Exception $e)
{
  echo 'NaN';
}
?>
