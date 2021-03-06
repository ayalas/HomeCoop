<?php

include_once '../settings.php';
include_once '../authenticate.php';

//close session opened in 'authenticate.php' when not required anymore
UserSessionBase::Close();

$oExport = new Members();

if ($g_oMemberSession->ExportFormat == Consts::EXPORT_FORMAT_LIBRE_OFFICE_FLAT_ODS)
  header('content-type: application/vnd.oasis.opendocument.spreadsheet');
else
  header('content-type: application/vnd.ms-excel');

$oExport->EchoXML();

unset($oExport);

?>
