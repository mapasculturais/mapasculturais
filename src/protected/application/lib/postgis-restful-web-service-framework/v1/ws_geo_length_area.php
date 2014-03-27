                                        
<?php
/**
 * Length/Area
 * Get the length of a line or area of a polygon passed as coordinate pairs.
 * 
 * @param 		string 		$in_srid			input SRID
 * @param 		string		$out_srid			output SRID, output length and area units are set from this
 * @param 		string		$points				series of points in "x,y" format separated by "|" (-80.6351,35.2365|-80.65456,34.213|-79.36522,36.2569)
 * @param			string		$format				xml,json
 */

# Includes
/* Just adding a comment to see if it commits */
require_once("../inc/error.inc.php");
require_once("../inc/database.inc.php");
require_once("../inc/security.inc.php");

# Set arguments for error email 
$err_user_name = "Jason";
$err_email = "jasonsanford@gmail.com";

# Retrive URL arguments
try {
	$in_srid = $_REQUEST['in_srid'];
	$out_srid = $_REQUEST['out_srid'];
	$points = explode("|",$_REQUEST['points']);
	$format = $_REQUEST['format'];
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	$sql = "select length(line_geom), area(poly_geom) from (select ";
	$st_line_from_text = "Transform(ST_GeomFromText('LINESTRING(";
	$st_poly_from_text = "Transform(ST_GeomFromText('POLYGON((";
	$first_point = $points[0];
	$first_point_parts = explode(",",$first_point);
	$first_point = $first_point_parts[0] . " " . $first_point_parts[1];
  
	foreach ($points as $point){
		$pointparts = explode(",",$point);
		$x = $pointparts[0];
		$y = $pointparts[1];
		$st_line_from_text .= $x . " " . $y . ", ";
		$st_poly_from_text .= $x . " " . $y . ", ";
	}

	$st_line_from_text = substr_replace($st_line_from_text,"",-2);
	$st_poly_from_text .= $first_point;
  
	$st_line_from_text .= ")'," . $in_srid . ")," . $out_srid . ") as line_geom, ";
	$st_poly_from_text .= "))'," . $in_srid . ")," . $out_srid . ") as poly_geom";
	$sql .= $st_line_from_text . $st_poly_from_text . ") as geoms";
  
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
