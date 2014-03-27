<?php
/**
 * Owner Name Geocode
 * Return address for an owner name. Fuzzy search.
 * 
 * @param 		string 		$firstname		owner first name
 * @param 		string 		$lastname		owner last name
 * @param 		string 		$fuzzy			perform fuzzy search, true or false
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
	$lastname = $_REQUEST['lastname'];
	$firstname = $_REQUEST['firstname'];
	$fuzzy = $_REQUEST['fuzzy'];
	$format = $_REQUEST['format'];
	if (strlen(trim($lastname)) < 1) { trigger_error("Caught Exception: lastname parameter must be at least one character.", E_USER_ERROR); }
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "SELECT top 50 OBJECTID, nme_ownerfirstname as First_Name, nme_ownerlastname as Last_Name, NUM_X_COORD as x_coordinate, NUM_Y_COORD as y_coordinate,		
		TXT_STREET_NUMBER as house_number, CDE_STREET_DIR_PRFX as prefix, NME_STREET as street_name, 
		NME_PO_CITY as postal_city, CDE_ROADWAY_TYPE as road_type, CDE_STREET_DIR_SUFF as suffix, 
		TXT_ADDR_UNIT as unit, NME_CITY as city, nme_po_city as postal_city, CDE_ZIP1 as zipcode, NUM_PARENT_PARCEL as parcel_id 
        FROM dbo.dv_MAT_ownername WHERE "; 
	if ($fuzzy == "true") { $sql .= "NME_OWNERLASTNAME Like '" . $lastname . "%' and nme_ownerfirstname like '" . $firstname . "%' order by nme_ownerlastname, nme_ownerfirstname"; }
	elseif ($fuzzy == "false") { $sql .= "NME_OWNERLASTNAME = '" . $lastname . "' and nme_ownerfirstname = '" . $firstname . "' order by nme_ownerlastname, nme_ownerfirstname"; }
	else { trigger_error("Caught Exception: fuzzy argument must be true or false.", E_USER_ERROR); }
	$sql = sanitizeSQL($sql);
	//echo $sql;
	$esdeconn = esdeConnection();
	//$esdeconn->Execute("set ansi_nulls on");
	//$esdeconn->Execute("set ansi_warnings on");

    /*** fetch into an PDOStatement object ***/
    $recordSet = $esdeconn->prepare($sql);
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