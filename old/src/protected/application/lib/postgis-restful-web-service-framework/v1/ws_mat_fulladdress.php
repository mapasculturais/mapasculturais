<?php
/**
 * Street Name Search
 * Search for a street name beginning with the string param from the master
 * address table.
 * 
 * @param 		string 		$streetname 	street name to search for
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
	$address = $_REQUEST['address'];
	$format = trim($_REQUEST['format']);
	if (strlen($address) < 3) trigger_error("Caught exception: address parameter must include at least three characters.", E_USER_ERROR);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	// if completed house number exists, find it and use it
	if (strpos( $address, " " )) {
		$houseno = trim(substr($address, 0, strpos($address, " ")));
		$sql = sanitizeSQL("select objectid, full_address as address from master_address_table where txt_street_number = '" . $houseno . "' and full_address like '" . strtoupper($address) . "%'  and cde_status='A' order by nme_street, txt_street_number ");
	}
	else {
		$sql = sanitizeSQL("select objectid, full_address as address from master_address_table where full_address like '" . strtoupper($address) . "%'  and cde_status='A' order by nme_street, txt_street_number ");
	}
	
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
	elseif ($format == "text") {
		header("Content-Type: application/text");
		while (!$recordSet->EOF)
		{
			echo $recordSet->fields['address'] . "|" . $recordSet->fields['objectid'] . "\n";
			$recordSet->MoveNext();
		}
	}
	else {
		trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>