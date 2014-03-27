<?php
/**
 * Uber Search
 * This is the uber search. It's so cool it had to be German.
 * 
 * @param 		string 		$query 	  		query string
 * @return 		string		- resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");
require_once("../inc/database.inc.php");
require_once("../inc/security.inc.php");


# Set array for search types
$searchType[0] = "Address";
$searchType[1] = "Library";
$searchType[2] = "School";
$searchType[3] = "Park";
$searchType[4] = "GeoName";
$searchType[5] = "Street Name";
$searchType[6] = "CATS Light Rail";
$searchType[7] = "CATS Park and Ride";
$searchType[8] = "Intersection";

# array for POI
$poi = array("1","2","3","4","5","6","7");


# Retrive URL arguments
try {
	$query = preg_replace('/\s\s+/', ' ', trim($_REQUEST['query']));
	$searchTypes = explode(",",$_REQUEST["searchtypes"]);
	if (strlen($query) < 3) echo returnEmpty($query);
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}


# Set header type
# header("Content-Type: application/json");



# Performs the query and returns XML or JSON
try {
	$pgconn = pgConnection();
	if (is_numeric($query)) {  // ignore - probably a parcel id
		echo returnEmpty($query); 		
	}
	else {  // Process String
		// If it's an int and a space it's an address
		$query_array = explode(' ', $query);
		$pos = strpos($query, "&");
		
		
		// if the first element is numeric it's an address
		if (is_numeric($query_array[0]) and in_array("0", $searchTypes)) {
			// run full street name query		
			$sql = sanitizeSQL("select '0:objectid:' || objectid as getid, 'ADDRESS: ' || full_address as name from master_address_table where txt_street_number = '" . $query_array[0] . "' and full_address like '" . strtoupper($query) . "%'  and num_x_coord > 0 and cde_status='A' order by getid, name ");
			$recordSet = $pgconn->prepare($sql);
			$recordSet->execute();
			echo rs2ubersearch($recordSet, $query);
		}
		
		// if the first element isn't numeric and it has an ampersand it's an intersection
		else if ($pos != false and in_array("8", $searchTypes )) { 
			// get string before &
			$firstStreet = strtoupper(trim(substr($query, 0, $pos)));
			// get string after &
			$secondStreet = strtoupper(trim(substr($query,$pos + 1, strlen($query) - $pos)));
			
			if (strlen($secondStreet) > 0) { $secondClause = " where streetname like '$secondStreet%' "; }
			else  { $secondClause = ""; }
			
			$sql = "select distinct '8:streetname:$firstStreet:' || b.streetname as getid, 'INTERSECTION: $firstStreet & ' || b.streetname as name  from (select streetname, the_geom from roads where streetname = '$firstStreet') a, (select streetname,the_geom from roads $secondClause) b where a.the_geom && b.the_geom and intersects(a.the_geom, b.the_geom) and b.streetname <> '$firstStreet' ";
			$recordSet = $pgconn->prepare($sql);
			$recordSet->execute();
			echo rs2ubersearch($recordSet, $query);
		}
		// if the first part isn't numeric and it doesn't have an ampersand it must by a POI
		else if (array_intersect($poi, $searchTypes)) {
			// make sql array
			$poiSQL[1] = "(select '1:gid:' || gid as getid, 'LIBRARY: ' || name as name from libraries where name ~* '$query' )";
			$poiSQL[2] = "(select '2:gid:' || gid as getid, 'SCHOOL: ' || schlname as name from schools_1011 where schlname ~* '$query' )";
			$poiSQL[3] = "(select '3:gid:' || gid as getid, 'PARK: ' || prkname as name from parks where prkname ~* '$query' )";
			$poiSQL[4] = "(select '4:geonameid:' || geonameid as getid, 'GEONAME: ' || name as name from geonames where name ~* '$query' )";
			$poiSQL[5] = "(select '5:street_name:' || street_name as getid, 'ROAD: ' || street_name as name from street_names where street_name ~* '$query' )";
			$poiSQL[6] = "(select '6:gid:' || gid as getid, 'CATS: ' || name as name from cats_light_rail_stations where name ~* '$query' )";
			$poiSQL[7] = "(select '7:gid:' || gid as getid, 'CATS: ' || name as name from cats_park_and_ride where name ~* '$query' )";
			$sql = "";
			foreach ($searchTypes as $test) {
				if (in_array($test, $poi)) {
					if (strlen($sql) > 0) { $sql .= " union " . $poiSQL[$test]; }
					else {$sql = $poiSQL[$test] ; }
				}
			}

			$sql = "select getid, name from (" . $sql . ") as fubar order by getid, name ";
			$recordSet = $pgconn->prepare($sql);
			$recordSet->execute();
			echo rs2ubersearch($recordSet, $query);
		}
		else {
			echo returnEmpty($query);	
		}
	}	
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}


// return empty if nothing found
function returnEmpty($query) {
	//For jsonp
	if (isset($_REQUEST['callback'])) { 
		return $_REQUEST["callback"] . "({ query:'$query',suggestions:[],data:[] })";
	}
	else {
		return "{ query:'$query',suggestions:[],data:[] }";
	}
}

// Make json return
function rs2ubersearch($recordSet, $query) {
	$output = "{ query: '$query', ";
	$suggestions = "";
	$data = "";
	
	while ($row  = $recordSet->fetch(PDO::FETCH_ASSOC)) {
		if (strlen($suggestions) > 0) {
			$suggestions .= ",";
			$data .= ",";
		}	
		$suggestions .= "'". str_replace(",", " ", $row["name"]) . "'";
		$data .= "'". $row["getid"] . "'";
	}
	if (strlen($suggestions) > 0) { $output .= "suggestions:[" . $suggestions . "], data:[" . $data . "]}"; }
	else { $output = "{ query:'$query',suggestions:[],data:[] }"; }
	
	if (isset($_REQUEST['callback'])) { 
		$output = $_REQUEST['callback'] . '(' . $output . ')';
	}
	return $output;
}


?>