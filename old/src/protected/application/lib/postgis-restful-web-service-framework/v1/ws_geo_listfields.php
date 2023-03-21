<?php
/**
 * List Fields
 * List the fields of a PostGIS layer.
 * 
 * @param 		string 		$geotable 		PostGIS table name
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
	$geotable = $_REQUEST['geotable'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "SELECT attname as field_name, typname as field_type FROM pg_namespace, pg_attribute, pg_type, pg_class
	WHERE pg_type.oid = atttypid AND pg_class.oid = attrelid
	AND pg_namespace.nspname = 'public'
	AND relnamespace = pg_namespace.oid
	AND relname = ? 
	AND attnum >= 1";
	$sql = sanitizeSQL($sql);
	$pgconn = pgConnection();

    /*** fetch into an PDOStatement object ***/
    $recordSet = $pgconn->prepare($sql);
    $recordSet->bindParam(1, $geotable);
    $recordSet->execute();

	require_once("../inc/format.inc.php");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>