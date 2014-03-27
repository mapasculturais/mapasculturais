<?php
	/**
	 * Fins Stage Gauge Reading Retrieval Query 
	 *Retrieves the readings of all the Stage Gauges inside Mecklenburg County during the requested time frame
	 * 
	  * @param 		string		$format		format of output, either json or xml
	 * @return 		string					resulting json or xml string
	  */

	# Includes
	require_once("../inc/error.inc.php");
	require_once("../inc/database.inc.php");
	require_once("../inc/security.inc.php");

	# Set arguments for error email 
	$err_user_name = "Lak";
	$err_email = "lakshmanan.krishnan@mecklenburgcountync.gov";
	
# Retrive URL arguments
try {
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}
# Performs the query and returns XML or JSON
try {
	$sql = "select d.sensorid,a.usgs_station_no,s.[site name] as sitename,a.longitude,a.latitude,convert(varchar,round(d.datavalue,2)) as streamlevel,";
	$sql .= "convert(varchar,round(d.datavalue2,2)) as streamlevelsea"; 
	$sql .= " from datachron as d, sensordef as s, sensordefaux as a";
	$sql .= " where d.sensorid = s.sensorid and d.sensorid = a.sensorid"; 
	$sql .= " and d.datachronid in (select max(d.datachronid) from datachron as d where d.sensorid in (select sensorid from sensordefaux where gage_type ='Stage')"; 
	$sql .= " group by d.sensorid) order by d.sensorid";
	
	$sql = sanitizeSQL($sql);
	$mssqlconn = finsConnection();
	/*** fetch into an PDOStatement object ***/
	$recordSet = $mssqlconn->prepare($sql);
	$recordSet->execute();
	if ($format == 'xml') {
		require_once("../inc/xml.pdo.inc.php");
		header("Content-Type: text/xml");
		echo rs2xml($recordSet);
	}
	elseif ($format == 'json') {
		require_once("../inc/json.pdo.inc.php");
		header("Content-Type: application/json");
		echo rs2json($recordSet);
	}
	else {
		trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}
?>