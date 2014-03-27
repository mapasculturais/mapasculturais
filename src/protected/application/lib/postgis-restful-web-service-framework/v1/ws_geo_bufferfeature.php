<?php
/**
 * Buffer Feature
 * Buffer a feature in one layer and return buffered features of the same or a 
 * different layer.
 * 
 * @param 		string 		$from_geotable 	The geotable that has the feature(s) to buffer
 * @param 		string		$to_geotable	The geotable with features to return
 * @param 		string		$fields			Fields to be returned
 * @param 		string		$parameters		Any additional SQL parameters
 * @param 		string		$distance		Units to buffer
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
	$from_geotable = $_REQUEST['from_geotable'];
	$to_geotable = $_REQUEST['to_geotable'];
	$fields = $_REQUEST['fields'];
	$parameters = $_REQUEST['parameters'];
	$distance = $_REQUEST['distance'];
	$format = trim($_REQUEST['format']);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select " . $fields . "	from " . $from_geotable . " as f, " . $to_geotable . " as t where 
	ST_DWithin(f.the_geom, t.the_geom, " . $distance .  ")  ";
	
	/*
	t.the_geom && Expand(f.the_geom," . $distance . ") 
	and intersects(t.the_geom, Buffer(f.the_geom," . $distance . ")) ";
	
	$sql = "SELECT " . $fields . " FROM " . $geotable . " a WHERE ST_DWithin(transform(a.the_geom,".$srid."), 
	GeomFromText('POINT(" . $x . " " . $y . ")'," . $srid . "), " . $distance .  ") " . $parameters ;
	*/
	
	if (strlen(trim($parameters)) > 0) { $sql .= " and " . $parameters; }
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