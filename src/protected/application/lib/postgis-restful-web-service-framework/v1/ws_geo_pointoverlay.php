<?php
/**
 * Project Point
 * Projects a XY coordinate from one projection to another.
 * 
 * @param 		string 		$x 				X coordinate
 * @param 		string		$y				Y coordinate
 * @param 		string		$srid			SRID of the coordinate
 * @param 		string		$geotable		PostGIS Layer Name
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
	$x = $_REQUEST['x'];
	$y = $_REQUEST['y'];
	$geotable = $_REQUEST['geotable'];
	$fields = $_REQUEST['fields'];
	$srid = $_REQUEST['srid'];
	$parameters = $_REQUEST['parameters'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "SELECT " . $fields ." FROM " . $geotable ." a WHERE 
	st_within(transform(GeometryFromText('POINT(" . $x . " " . $y .  ")', " . $srid . "),2264),a.the_geom)";
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