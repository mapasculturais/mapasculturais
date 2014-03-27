<?php
/**
 * BOE Officials
 * Return elected representatives based on district and qualifier.
 * 
 * @param 		string 		$district_type 		Type of political district
 * @param 		string		$district			Specific district if needed
 * @param 		string		$format			format of output, either json or xml
 * @return 		string						resulting json or xml string
 */

# Includes
require_once("../inc/error.inc.php");
require_once("../inc/security.inc.php");
require_once("../inc/xml_regex.inc.php");

# Set arguments for error email 
#$err_user_name = "Tobin";
#$err_email = "tobin.bradley@mecklenburgcountync.gov";

# Retrive URL arguments
try {
	$search = $_REQUEST['search'];
	$field = $_REQUEST['field'];
	$format = $_REQUEST['format'];
	# set up metadata directory
	$thedir = "D:/metadata/";
} 
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

# Performs the query and returns XML or JSON
try {
	if ($format == "json" || $format == "xml") {
		# Open folder
		$dir= opendir($thedir);
		
		
		$xmlresponse = ""; 
		$jsonresponse = "";
		$i = 0;
		while($file=readdir($dir)){
			if ($file!="." and $file!="..") {
				$thepath = $dir . "/" . $file;
				$xml = file_get_contents($thedir . $file);
				$searchfield = $xml;
				if (strlen($field) > 0) $searchfield = value_in($field, $xml);
				$title = value_in('title', $xml);
				$abstract = value_in('abstract', $xml);
				if(stristr($searchfield, $search)) {
					$jsonresponse .= '{"row":{"title":"' . $title . '", "abstract":"' . urlencode($abstract) . '", "file_name":"' . urlencode($file) . '"}},';		
					$xmlresponse .= '<row><column name="title">' . $title . '</column><column name="abstract">' . $abstract . '</column><column name="file_name">' . $file . '</column></row>';
					$i++;
				}
			}
		}
		if ($i > 1) {
			$xmlresponse .= "</rows>";
			$xmlresponse = '<rows total-rows="' . $i . '">' . $xmlresponse;
			$jsonresponse .= "]}";
			$jsonresponse = '{"total_rows":"' . $i . '","rows":[' . $jsonresponse;
		}
		else {			
			$xmlresponse =  '<rows total-rows="0"/>';
			$jsonresponse =  '{"total_rows":"0","rows":"row"}';
		}
		
		
		if ($format == 'xml') {
			header("Content-Type: text/xml");
			echo $xmlresponse;
		}
		
		elseif ($format == 'json') {
			header("Content-Type: application/json");
			if (isset($_REQUEST['callback'])) $jsonresponse = $_REQUEST['callback'] . '(' . $jsonresponse . ')';
			echo $jsonresponse;
		}
		else {
			trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
		}
	}
	elseif ($format == "raw") {
		$xml = file_get_contents($thedir . $search);
		header("Content-Type: text/xml");
		echo $xml;
	}
	elseif ($format == "classic") {
		
		header("Content-Type: text/html");
		$xml = new DomDocument();
		$xml->load($thedir . $search);
																					   
		$xsl = new DomDocument;
		$xsl->load('../inc/FGDC_Classic_for_Web_body.xsl');
																					   
		$proc = new xsltprocessor();
		$proc->importStyleSheet($xsl);
		echo($proc->transformToXML($xml));

	}
	elseif ($format == "faq") {
		
		header("Content-Type: text/html");
		$xml = new DomDocument();
		$xml->load($thedir . $search);
																					   
		$xsl = new DomDocument;
		$xsl->load('../inc/FGDC_FAQ_for_Web_body.xsl');
																					   
		$proc = new xsltprocessor();
		$proc->importStyleSheet($xsl);
		echo($proc->transformToXML($xml));

	}
	elseif ($format == "summary") {
		
		header("Content-Type: text/html");
		$xml = new DomDocument();
		$xml->load($thedir . $search);
																	   
		$xsl = new DomDocument;
		$xsl->load('../inc/FGDC_Summary.xsl');
																					   
		$proc = new xsltprocessor();
		$proc->importStyleSheet($xsl);
		echo($proc->transformToXML($xml));

	}
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}


?>