<?php
/**
 * Error Handler
 * Returns error some error information to the user and, if provided, sends detailed
 * error information to a service administrator.
 */

# Set level at which to trap errors, error handler, and script execution time limit.
//error_reporting(E_ALL & E_NOTICE);
set_error_handler('errorHandler');
set_time_limit(10);

# Error handler function
function errorHandler($errno, $errstr ,$errfile, $errline, $errcontext)
{
    # capture some additional information
    $agent       = $_SERVER['HTTP_USER_AGENT'];
	$ip          = $_SERVER['REMOTE_ADDR'];
	$referrer 	 = $_SERVER['HTTP_REFERER'];
    $dt 		 = date("Y-m-d H:i:s (T)");
    
    # grab email info if available
    global $err_email, $err_user_name;

    # use this to email problem to maintainer if maintainer info is set
    if (isset($err_user_name) && isset($err_email)) {
	    
    }
		    
    
	# Write error message to user with less details
	$xmldoc = new DomDocument('1.0');
	$xmldoc->formatOutput = true;
	# Set root
	$root = $xmldoc->createElement("error_handler");
	$root = $xmldoc->appendChild($root);
	# Set child 
	$occ = $xmldoc->createElement("error");
  	$occ = $root->appendChild($occ);
  	# Write error message		
  	$child = $xmldoc->createElement("error_message");
  	$child = $occ->appendChild($child);
  	$fvalue = $xmldoc->createTextNode("Your request has returned an error: ".$errstr);
    $fvalue = $child->appendChild($fvalue);
	$xml_string = $xmldoc->saveXML();
	echo $xml_string;
		

	# exit request
	exit;
}

?>