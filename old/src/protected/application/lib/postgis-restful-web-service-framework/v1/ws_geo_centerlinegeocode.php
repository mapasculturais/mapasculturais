<?php
/**
 * Centerline Geocode
 * Geocode an address based on the street centerline.
 * 
 * @param 		string 		$housenum		house number
 * @param 		string 		$prefix			prefix
 * @param 		string 		$streetname		street name
 * @param 		string 		$roadtype		road type
 * @param 		string 		$suffix			street suffix
 * @param 		string 		$zipcode		zip code
 * @param 		string 		$city			city
 * @param 		string		$format			output format, xml or json
 * @return 		string		- resulting json or xml string
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
	$housenum = $_REQUEST['housenum'];
	$prefix = strtoupper($_REQUEST['prefix']);
	$streetname = strtoupper($_REQUEST['streetname']);
	$roadtype = strtoupper($_REQUEST['roadtype']);
	$suffix = strtoupper($_REQUEST['suffix']);
	$zipcode = $_REQUEST['zipcode'];
	$city = strtoupper($_REQUEST['city']);
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "SELECT  x(line_interpolate_point(geometryn(the_geom, 1), ((ur_add - $housenum ) / (ur_add  - lr_add)) )) as x_coordinate, 
		y(line_interpolate_point(geometryn(the_geom, 1), ((ur_add - $housenum ) / (ur_add  - lr_add)) )) as y_coordinate,
		ll_add, ul_add, lr_add, ur_add, prefixdire as prefix, streetname as street_name, 
		streettype as road_type, suffix, l_juris as city, l_zipcode as zipcode
 		from roads where ";
	
	if (strlen(trim($housenum)) > 0) { 
		$sql .= " lr_add <= " . $housenum;
		$sql .= " and ul_add >= " . $housenum;
	}
	else { trigger_error("Caught Exception: must pass house number.", E_USER_ERROR); }
	if (strlen(trim($prefix)) > 0) { $sql .= " and prefixdire = '" . $prefix . "'";}
	if (strlen(trim($roadtype)) > 0) { $sql .= " and streettype = '" . $roadtype . "'";}
	if (strlen(trim($suffix)) > 0) { $sql .= " and suffix = '" . $suffix . "'";}
	if (strlen(trim($zipcode)) > 0) { $sql .= " and l_zipcode = '" . $zipcode . "'";}
	if (strlen(trim($city)) > 0) { $sql .= " and l_juris = '" . $city . "'";}
	if (strlen(trim($streetname)) > 0) { $sql .= " and streetname like '" . $streetname . "%'"; }
	else { trigger_error("Caught Exception: must pass streetname argument.", E_USER_ERROR); }
	$sql .= " limit 5";
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