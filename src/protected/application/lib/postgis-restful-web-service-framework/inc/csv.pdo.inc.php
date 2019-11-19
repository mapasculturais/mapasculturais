<?php
/**
 * Creates CSV from an ADODB record set
 *
 * @param 		object 		$rs 		- record set object
 * @return 		string		- resulting csv string
*/

function rs2csv($rs)
{
	if (!$rs) {
		trigger_error("Caught Exception: bad recordset passed to rs2csv function.", E_USER_ERROR);
		return false;
	}
    
    $header = '';
	$output = '';

    $rowCounter = 0;
    while ($row  = $rs->fetch(PDO::FETCH_ASSOC))
	{
		$rowOutput = '';
		foreach ($row as $key => $val)
		{
			if ($rowCounter===0){
				$header .= (strlen($header)>0 ? ',' : '') . trim($key);
			}
			$rowOutput .= (strlen($rowOutput)>0 ? ',' : '') . (is_numeric(trim($val)) ? trim($val) : '"' . trim($val) . '"');
		}
		$output .= $rowOutput . "\r\n";
		$rowCounter++;
	}
	
	$header .= "\r\n";
    $output = $header . $output;
	
	return $output;
}

?>
