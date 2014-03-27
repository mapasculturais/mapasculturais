<?php
/**
 * Yahoo Traffic
 * Return GeoRSS of Yahoo Traffic
 * 
 * @param 		latitude 		$latitude		latitude
 * @param 		longitude 		$longitude		longitude
 * @param 		zip 			$zip			zipcode
 * @param 		radius 		$radius		radius in miles
 * @return 		string		- resulting Yahoo GeoRSS
 */

# Includes
require_once("../inc/error.inc.php");

# Set arguments for error email 
$err_user_name = "Tobin";
$err_email = "tobin.bradley@mecklenburgcountync.gov";


# Retrive URL arguments
try {
	$zip = $_REQUEST['zip'];
	$longitude = $_REQUEST['longitude'];
	$latitude = $_REQUEST['latitude'];
	$radius = $_REQUEST['radius'];
	$apikey = "SOktZrDV34FOkElZMf1ne2UMC0HnYDPUgd7PPRsO9Zn_TAMya_X9ODDtpWiDr.BL3p.c";
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {

		header("Content-Type: text/xml");
		
		$address = "http://local.yahooapis.com/MapsService/rss/trafficData.xml?appid=$apikey";
		
		$address .= "&radius=$radius";
		$address .= "&zip=$zip";
		$address .= "&longitude=$longitude&latitude=$latitude";
		
		//echo file_get_contents($address);
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$address);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,3);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		echo $buffer;

}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>