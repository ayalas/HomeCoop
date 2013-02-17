<?php

include_once '../settings.php';
include_once '../authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

$oExport = new Members();

header('content-type: application/vnd.oasis.opendocument.spreadsheet');

$oExport->EchoXML();

unset($oExport);

?>
