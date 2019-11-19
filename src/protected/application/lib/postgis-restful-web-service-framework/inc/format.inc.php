<?php
/**
 * Format Include
 * Handles all of the data formatting from $recordSet to 
 * format of choice (xml,json,jsonp,kml,csv)
 */

if ($format == 'xml') {
	require_once("xml.pdo.inc.php");
	header("Content-Type: text/xml");
	echo rs2xml($recordSet);
}
elseif ($format == 'json') {
	require_once("json.pdo.inc.php");
	header("Content-Type: application/json");
	echo rs2json($recordSet);
}else if($format == 'kml'){
	require_once("kml.pdo.inc.php");
	header("Content-Type: application/vnd.google-earth.kml+xml");
	header("Content-Disposition: attachment; filename=kml_export.kml");
	echo rs2kml($recordSet);
}else if($format == 'csv'){
	require_once("csv.pdo.inc.php");
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=csv_export.csv");
	echo rs2csv($recordSet);
}
else {
	trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
}	
	
?>