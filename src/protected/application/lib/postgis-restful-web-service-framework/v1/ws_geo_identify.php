<?php
/**
 * Identify
 * Identify features from multiple layers near an x,y using a given distance and return the results.
 * 
 * @param 		string 		$x 					X coordinate
 * @param 		string		$y					Y coordinate
 * @param 		string		$srid				SRID of the coordinate
 * @param 		string		$geotables			Comma separated PostGIS Layers (address_points,roads,subdivisions)
 * @param 		string		$fields				Comma separated fields to be returned separated by layer with "|" (gid,display|gid+road_name|gid,name)
 * @param 		string		$distance			Units to buffer
 * @param 		string		$format				format of output, either json or xml
 * @return 		string									resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");
require_once("../inc/database.inc.php");
require_once("../inc/security.inc.php");

# Set arguments for error email 
$err_user_name = "Jason";
$err_email = "jasonsanford@gmail.com";

# Retrive URL arguments
try {
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	$srid = $_REQUEST['srid'];
	$geotables = explode(",",$_REQUEST['geotables']);
	$fields = explode("|",$_REQUEST['fields']);
	$distance = $_REQUEST['distance'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}
$queries = array();
for ($i=0; $i<count($geotables); $i++){

	$geotable = $geotables[$i];
	$these_fields = $fields[$i];
	
	try{
	
		$sql = "SELECT " . $these_fields . " FROM " . $geotable . " a WHERE transform(a.the_geom,".$srid.") && Expand(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid . ")," . $distance . ") 
		AND Distance(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid . "),transform(a.the_geom,".$srid.")) < " . $distance . "
		order by Distance(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid ."),transform(a.the_geom,".$srid.")) limit 100";

		$sql = sanitizeSQL($sql);
		$pgconn = pgConnection();

		/*** fetch into an PDOStatement object ***/
		$recordSet = $pgconn->prepare($sql);
		$recordSet->execute();
		
		/*** Push geotable name and recordset into $queries to be processed by multi_rs2[format] ***/
		array_push($queries, array('geotable' => $geotable, 'recordSet' => $recordSet));
	}catch(Exception $e){
		trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
	}
}

# Performs the query and returns XML or JSON
try {

	if ($format == 'xml') {
		require_once("../inc/xml.pdo.inc.php");
		header("Content-Type: text/xml");
		echo multi_rs2xml($queries);
	}
	elseif ($format == 'json') {
		require_once("../inc/json.pdo.inc.php");
		header("Content-Type: application/json");
		echo multi_rs2json($queries);
	}
	else {
		trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>
