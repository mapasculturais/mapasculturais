<?php
	/**
	 * Fins Rain Gauge Reading Retrieval Query 
	 *Retrieves the readings of all the Rain Gauges inside Mecklenburg County during the requested time frame
	 * 
	 * @param 		string 	$timeframe 	 	timeframe for which reading requested
	 * @param 		string		$format		format of output, either json or xml
	 * @return 		string					resulting json or xml string
	  */

	# Includes
	require_once("../inc/error.inc.php");
	require_once("../inc/database.inc.php");
	require_once("../inc/security.inc.php");

	# Set arguments for error email 
	$err_user_name = "Lak";
	$err_email = "lakshmanan.krishnan@mecklenburgcountync.gov";
	
	//set constants
	$datacutoff = 360;

	# Retrive URL arguments
	try {
		$timeframe = $_REQUEST['timeframe'];
		$format = trim($_REQUEST['format']);
	} 
	catch (Exception $e) {
		trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
	}
	# Performs the query and returns XML or JSON
	try {
		if ($timeframe <= $datacutoff){
			$sql = "select str(a.sensorid) as sensorid,a.usgs_station_no,s.[site name] as sitename,a.longitude,a.latitude,v.rainamt"; 
			$sql .= " from sensordef as s,sensordefaux as a";  
			$sql .= " left join(select str(d.sensorid) as sensorid,convert(varchar,round(sum(d.datavalue),3)) as rainamt";  
			$sql .= " from datachron as d,sensordef as s,sensordefaux as a";  
			$sql .= " where d.sensorid = s.sensorid and s.sensorid = a.sensorid";  
			$sql .= " and d.sensorid in (select sensorid from sensordefaux where gage_type ='Rain')";  
			$sql .= " and datediff(minute,d.datetime,current_timestamp) between 0 and ".$timeframe;  
			$sql .= " group by d.sensorid) as v"; 
			$sql .= " on a.sensorid = v.sensorid"; 
			$sql .= " where s.sensorid = a.sensorid";   
			$sql .= " and a.sensorid in (select sensorid from sensordefaux where gage_type ='Rain')"; 
			$sql .= " order by a.sensorid"; 
		}else{
			$sql = "select str(a.sensorid) as sensorid,a.usgs_station_no,s.[site name] as sitename,a.longitude,a.latitude,v.rainamt"; 
			$sql .= " from sensordef as s,sensordefaux as a";  
			$sql .= " left join(select str(u.sensorid) as sensorid,convert(varchar,round(sum(u.datavalue),3)) as rainamt"; 
			$sql .= " from sensordef as s,sensordefaux as a,(select d.sensorid,d.datetime,d.datavalue";  
			$sql .= " from datachron as d where d.sensorid in (select sensorid from sensordefaux where gage_type ='Rain')";  
			$sql .= " and datediff(minute,d.datetime,current_timestamp) between 0 and ".$datacutoff;  
			$sql .= " union all";  
			$sql .= " select a.sensorid,n.datetime,n.datavalue from nwisdata n, sensordefaux as a";  
			$sql .= " where n.siteno = a.usgs_station_no and n.site_type = 'Rain'";  
			$sql .= " and a.sensorid in (select sensorid from sensordefaux where gage_type ='Rain')";  
			$sql .= " and datediff(minute,n.datetime,current_timestamp) between ".($datacutoff+1)." and ".$timeframe.") as u";  
			$sql .= " where u.sensorid = s.sensorid and s.sensorid = a.sensorid group by u.sensorid) as v";  
			$sql .= " on a.sensorid = v.sensorid"; 
			$sql .= " where s.sensorid = a.sensorid";   
			$sql .= " and a.sensorid in (select sensorid from sensordefaux where gage_type ='Rain')"; 
			$sql .= " order by a.sensorid"; 
		}	
		
		$sql = sanitizeSQL($sql);
		$mssqlconn = finsConnection();
		/*** fetch into an PDOStatement object ***/
		$recordSet = $mssqlconn->prepare($sql);
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
		else {
			trigger_error("Caught Exception: format must be xml or json.", E_USER_ERROR);
		}
	}
	catch (Exception $e) {
		trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
	}
?>