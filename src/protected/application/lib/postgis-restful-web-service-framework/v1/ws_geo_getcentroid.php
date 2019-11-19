<?php
/**
 * Get Extent
 * Get the extent of a feature or features in a geotable.
 * 
 * @param 		string 		$srid 			The projection of the resulting geometry
 * @param 		string		$geotable		The PostGIS layer name.
 * @param 		string		$parameters		SQL where clause parameters
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
	$srid = $_REQUEST['srid'];
	$geotable = $_REQUEST['geotable'];
	$parameters = $_REQUEST['parameters'];
	$format = trim($_REQUEST['format']);
	$forceonsurface = trim($_REQUEST['forceonsurface']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	if ($forceonsurface == 'true') $geofunction = 'ST_PointOnSurface'; else $geofunction = 'ST_Centroid';
	$sql = "select x(transform(" . $geofunction . "(the_geom), " . $srid . ")) as x, y(transform(" . $geofunction . "(the_geom), " . $srid . ")) as y from " . $geotable . " where " . $parameters ;
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