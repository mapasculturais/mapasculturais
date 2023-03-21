<?php
/**
 * Project Point
 * Projects a XY coordinate from one projection to another.
 * 
 * @param 		string 		$x 				X coordinate
 * @param 		string		$y				Y coordinate
 * @param 		string		$fromsrid		SRID of input point
 * @param 		string		$tosrid			SRID of output point
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
	$fromsrid = $_REQUEST['fromsrid'];
	$tosrid = $_REQUEST['tosrid'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select x(transform(GeomFromText('POINT(" . $x . " " . $y . ")', " . $fromsrid . "), " . $tosrid . ")) as x_coordinate, 
	y(transform(GeomFromText('POINT(" . $x . " " . $y . ")', " . $fromsrid . "), " . $tosrid . ")) as y_coordinate";
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