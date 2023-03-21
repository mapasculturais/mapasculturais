<?php
/**
 * PID Geocode
 * Get MAT records for a PID.
 * 
 * @param 		string 		$pid 			street name to search for
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
	$pid = $_REQUEST['pid'];
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "SELECT  OBJECTID, NUM_X_COORD as x_coordinate, NUM_Y_COORD as y_coordinate, 
		x(transform(SETSRID(makepoint(num_x_coord ,  num_y_coord), 2264), 4326)) as longitude,
		y(transform(SETSRID(makepoint(num_x_coord ,  num_y_coord), 2264), 4326)) as latitude,
		TXT_STREET_NUMBER as house_number, CDE_STREET_DIR_PRFX as prefix, NME_STREET as street_name, 
		NME_PO_CITY as postal_city, CDE_ROADWAY_TYPE as road_type, CDE_STREET_DIR_SUFF as suffix, 
		TXT_ADDR_UNIT as unit, NME_CITY as city, nme_po_city as postal_city, CDE_ZIP1 as zipcode, NUM_PARENT_PARCEL as parcel_id, full_address as address
        FROM master_address_table WHERE cde_status = 'A' and num_x_coord > 0 and num_parent_parcel ";
	if (strlen($pid) == 8) { $sql .= " = '" . $pid ."' limit 100"; }
	elseif (strlen($pid) > 4) { $sql .= " like '" . $pid ."%' limit 100"; }
	else { trigger_error("Caught Exception: pid must be at least 5 characters in length.", E_USER_ERROR); }
	$sql = sanitizeSQL($sql);
	$pgconn = pgConnection();

    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
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