<?php
/**
 * Buffer Point
 * Buffer a point by a given distance and return the results.
 * 
 * @param 		string 		$x 				X coordinate
 * @param 		string		$y				Y coordinate
 * @param 		string		$srid			SRID of the coordinate
 * @param 		string		$geotable		PostGIS Layer Name
 * @param 		string		$fields			Fields to be returned
 * @param 		string		$parameters		Any additional SQL parameters
 * @param 		string		$distance		Units to buffer
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
	$distance = $_REQUEST['distance'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	if (strlen(trim($parameters)) > 0) { $parameters = " and " . $parameters; }
	$sql = "SELECT " . $fields . " FROM " . $geotable . " a WHERE transform(a.the_geom,".$srid.") && Expand(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid . ")," . $distance . ") 
	AND Distance(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid . "),transform(a.the_geom,".$srid.")) < " . $distance . " " . $parameters . "
	order by Distance(GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid ."),transform(a.the_geom,".$srid.")) limit 100";

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