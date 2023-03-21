<?php
/**
 * Buffer Point
 * Buffer a point by a given distance and return the results.
 * 
 * @param 		string 	$streetname1 		First street name for intersection search
 * @param 		string		$streetname2		Second street name for intersection search
 * @param 		string		$srid				SRID of the coordinate output
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
	$streetname1 = strtoupper($_REQUEST['streetname1']);
	$streetname2 = strtoupper($_REQUEST['streetname2']);
	$srid = $_REQUEST['srid'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select distinct x(transform(intersection(b.the_geom, a.the_geom), ?)) as xcoord,
	y(transform(intersection(b.the_geom, a.the_geom), ?)) as ycoord from
	(select * from roads where streetname = ? ) a,
	(select * from roads where streetname = ? ) b
	where a.the_geom && b.the_geom and intersects(b.the_geom, a.the_geom)";


	$sql = sanitizeSQL($sql);
	$pgconn = pgConnection();

    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->bindParam(1, $srid);
    $recordSet->bindParam(2, $srid);
    $recordSet->bindParam(3, $streetname1);
    $recordSet->bindParam(4, $streetname2);
    $recordSet->execute();

	require_once("../inc/format.inc.php");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>