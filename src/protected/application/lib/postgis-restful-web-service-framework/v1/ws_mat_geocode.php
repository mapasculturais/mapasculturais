<?php
/**
 * MAT Geocode
 * Geocode address to the master address table.
 * 
 * @param 		string 		$housenum		house number
 * @param 		string 		$prefix			prefix
 * @param 		string 		$streetname		street name
 * @param 		string 		$roadtype		road type
 * @param 		string 		$suffix			street suffix
 * @param 		string 		$zipcode		zip code
 * @param 		string 		$city			city
 * @param 		string 		$unit			house unit
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
	$streetname = str_replace("'", "''", strtoupper($_REQUEST['streetname']));  
	$roadtype = strtoupper($_REQUEST['roadtype']);
	$suffix = strtoupper($_REQUEST['suffix']);
	$zipcode = $_REQUEST['zipcode'];
	$city = strtoupper($_REQUEST['city']);
	$unit = strtoupper($_REQUEST['unit']);
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select x(transform(GeomFromText('POINT(NUM_X_COORD NUM_Y_COORD)', 2264), 4326)) as x_coordinate, 
	y(transform(GeomFromText('POINT(NUM_X_COORD NUM_Y_COORD)', 2264), 4326)) as y_coordinate";
	
	
	$sql = "SELECT  OBJECTID, NUM_X_COORD as x_coordinate, NUM_Y_COORD as y_coordinate, 
		x(transform(SETSRID(makepoint(num_x_coord ,  num_y_coord), 2264), 4326)) as longitude,
		y(transform(SETSRID(makepoint(num_x_coord ,  num_y_coord), 2264), 4326)) as latitude,
		TXT_STREET_NUMBER as house_number, CDE_STREET_DIR_PRFX as prefix, NME_STREET as street_name, 
		NME_PO_CITY as postal_city, CDE_ROADWAY_TYPE as road_type, CDE_STREET_DIR_SUFF as suffix, 
		TXT_ADDR_UNIT as unit, NME_CITY as city, nme_po_city as postal_city, CDE_ZIP1 as zipcode, NUM_PARENT_PARCEL as parcel_id 
        FROM master_address_table WHERE cde_status = 'A' ";
	if (strlen(trim($housenum)) > 0) { $sql .= " and txt_street_number = '" . $housenum . "'";}
	if (strlen(trim($prefix)) > 0) { $sql .= " and CDE_STREET_DIR_PRFX = '" . $prefix . "'";}
	if (strlen(trim($roadtype)) > 0) { $sql .= " and CDE_ROADWAY_TYPE = '" . $roadtype . "'";}
	if (strlen(trim($suffix)) > 0) { $sql .= " and CDE_STREET_DIR_SUFF = '" . $suffix . "'";}
	if (strlen(trim($zipcode)) > 0) { $sql .= " and CDE_ZIP1 = '" . $zipcode . "'";}
	if (strlen(trim($city)) > 0) { $sql .= " and nme_po_city = '" . $city . "'";}
	if (strlen(trim($unit)) > 0) { $sql .= " and TXT_ADDR_UNIT = '" . $unit . "'"; }
	if (strlen(trim($streetname)) > 0) { $sql .= " and NME_STREET = '" . $streetname . "'"; }
	else { trigger_error("Caught Exception: must pass streetname argument.", E_USER_ERROR); }
	$sql .= " order by nme_city, nme_street, txt_street_number ";
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