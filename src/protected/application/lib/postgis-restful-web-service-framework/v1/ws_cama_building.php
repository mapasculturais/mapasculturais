<?php
/**
 * CAMA Building Information
 * Return CAMA building information for a parcel.
 * 
 * @param 		string 		$pid 			Parcel ID
 * @param 		string		$pidtype		Parcel ID type, tax or common
 * @param 		string		$format			format of output, either json or xml
 * @return 		string						resulting json or xml string
 */

# Includes
require_once("../../inc/error.inc.php");
require_once("../../inc/database.inc.php");
require_once("../../inc/security.inc.php");

# Set arguments for error email 
$err_user_name = "Tobin";
$err_email = "tobin.bradley@mecklenburgcountync.gov";

# Retrive URL arguments
try {
	$pid = $_REQUEST['pid'];
	$pidtype = $_REQUEST['pidtype'];
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select id_pid as parcel_id, id_common_pid as common_parcel_id, num_card_no as card_number, txt_propertyuse_desc as property_use_description, cnt_units as units, num_yearbuilt as year_built, 
	num_grossarea as total_square_feet, num_heatedarea as heated_square_feet, 
	txt_foundation_desc as foundation_description, txt_extwall_desc as exterior_wall_description, txt_heatingtype_desc as heat_type, txt_actype_desc as ac_type, 
	txt_storyheight_desc as stories, num_bedrooms as bedrooms, cnt_fullbaths as full_baths, cnt_threeqtrbaths as three_quarter_baths, cnt_halfbaths as half_baths
	 from dbo.tb_PubBuilding where ";

	if ($pidtype == "tax") { $sql .= "id_pid = ?"; }
	elseif ($pidtype == "common") { $sql .= "id_common_pid = ?"; }
	else { trigger_error("Caught Exception: pidtype must be either tax or common", E_USER_ERROR); }
	$sql .= " order by num_card_no";
	$sql = sanitizeSQL($sql);
	$camaconn = camaConnection();

	/*** fetch into an PDOStatement object ***/
    $recordSet = $camaconn->prepare($sql);
    $recordSet->bindParam(1, $pid);
    $recordSet->execute();

	if ($format == 'xml') {
		require_once("../../inc/xml.pdo.inc.php");
		header("Content-Type: text/xml");
		echo rs2xml($recordSet);
	}
	elseif ($format == 'json') {
		require_once("../../inc/json.pdo.inc.php");
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