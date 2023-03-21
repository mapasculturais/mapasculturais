<?php
/**
 * Feature Overlay
 * Overlay features from one PostGIS layer onto another PostGIS layer and
 * return the results.
 * 
 * @param 		string 		$from_geotable 		Geotable with selecting feature(s)
 * @param 		string		$to_geotable		Geotable with features to be selected
 * @param 		string		$fields			Fields to be returned
 * @param 		string		$parameters		Any additional SQL parameters
 * @param 		string		$format			format of output, either json or xml
 * @return 		string						resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");
require_once("../inc/database.inc.php");
require_once("../inc/security.inc.php");

# Set arguments for error email 
$err_user_name = "Tobin";
$err_email = "tobin.bradley@mecklenburgcountync.gov";

# Retrive URL arguments
try {
	$from_geotable = $_REQUEST['from_geotable'];
	$to_geotable = $_REQUEST['to_geotable'];
	$fields = $_REQUEST['fields'];
	$parameters = $_REQUEST['parameters'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select " . $fields . " from " . $from_geotable . " as f, " . $to_geotable . " as t where f.the_geom && t.the_geom and intersects(f.the_geom, t.the_geom) ";
	if (strlen(trim($parameters)) > 0) { $sql .= " and " . $parameters; }
	$sql = sanitizeSQL($sql);
	$pgconn = pgConnection();

    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->execute();

	require_once("../inc/format.inc.php");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>