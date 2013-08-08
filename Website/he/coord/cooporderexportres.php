<?php

include_once '../settings.php';
include_once '../authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

$oExport = new CoopOrderExport();

if (isset($_GET['coid']))
  $oExport->CoopOrderID = intval($_GET['coid']);

if (isset($_GET['id']))
  $oExport->ID = intval($_GET['id']);

header('content-type: application/vnd.oasis.opendocument.spreadsheet');

$oExport->EchoXML();

unset($oExport);

?>
