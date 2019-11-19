<?php
/**
 * Google Geocode
 * Connect to and use Google's Geocode engine
 * 
 * @param 		string 		$address		address to geocode
 * @param 		string		$format			output format, xml or json
 * @return 		string		- resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");

# Set arguments for error email 
$err_user_name = "Tobin";
$err_email = "tobin.bradley@mecklenburgcountync.gov";


# Retrive URL arguments
try {
	$address = urlencode($_REQUEST['address']);
	$format = $_REQUEST['format'];
	$apikey = "ABQIAAAAm9Ct4PrMS0FyB27XUOQdYxSmcYbtpieKSvoX8WKxcytqmdvJURRUlZKJvHEnv0LRz4_ULRhcdwBqDg";
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	if ($format == 'xml') {
		header("Content-Type: text/xml");
		$address = "http://maps.google.com/maps/geo?q=$address&output=xml&key=$apikey";
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$address);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,4);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		//echo htmlspecialchars_decode(file_get_contents($address));
		echo htmlspecialchars_decode($buffer);
	}
	elseif ($format == 'json') {
		header("Content-Type: application/json");
		$address = "http://maps.google.com/maps/geo?q=$address&output=json&key=$apikey";
		//echo (file_get_contents($address));
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$address);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,4);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		
		echo $buffer;
		
	}
	else {
		trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>