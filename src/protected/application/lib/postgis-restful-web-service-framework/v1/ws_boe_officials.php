<?php
/**
 * BOE Officials
 * Return elected representatives based on district and qualifier.
 * 
 * @param 		string 		$district_type 		Type of political district
 * @param 		string		$district			Specific district if needed
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
	$district_type = $_REQUEST['district_type'];
	$district = $_REQUEST['district'];
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	switch ($district_type) {
	case "national_senate":
		$sql = "SELECT '' as district, representative FROM elected_officials where district_type='national_senate'";
		break;
	case "board_of_education":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'board_of_education' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	case "charlotte_city_council":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'charlotte_city_council' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	case "county_commissioners":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'county_commission' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	case "national_congressional":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'national_congressional' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	case "state_house":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'state_house' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	case "state_senate":
		$sql = "SELECT district, representative FROM elected_officials where district_type = 'state_senate' and (district = 'At-Large' or district = '" . $district ."') order by district";
		break;
	default:
		trigger_error("Caught Exception: district_type parameter must be national_senate, board_of_education, 
		charlotte_city_council, county_commissioners, national_congressional, state_house, or state_senate.", E_USER_ERROR);
	}

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