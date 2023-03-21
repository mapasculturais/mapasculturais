<?php
/**
 * Geo Attribute Query
 * Performs attribute query on a geotable.
 * 
 * @param 		string 		$fields 		Fields to be returned
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
	$fields = $_REQUEST['fields'];
	$geotable = $_REQUEST['geotable'];
	$parameters = $_REQUEST['parameters'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select " . $fields . " from " . $geotable; 
	if (strlen(trim($parameters)) > 0) {$sql .= " where " . $parameters;}
	$sql = sanitizeSQL($sql);
	$sql = str_replace('transform', 'st_transform', $sql);	
	//echo $sql;
	$pgconn = pgConnection();

    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->execute();
	//echo $sql;

	require_once("../inc/format.inc.php");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>
