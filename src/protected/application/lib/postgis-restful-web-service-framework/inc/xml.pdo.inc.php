<?php
/**
 * Recordset to XML
 * Accepts and ADODB recordset, converts it to XML, and returns the result.
 *
 * @param 		object 		$rs 		record set object
 * @return 		string		$xml		resulting xml
 */

function rs2xml($rs)
{
	if (!$rs) {
		trigger_error("Caught Exception: bad recordset passed to rs2xml function.", E_USER_ERROR);
		return false;
	}

	$xml = '';
	$totalRows = 0;

	$totalRows = count($rs);

	$domxml = new DOMDocument('1.0', 'utf-8');
	$root = $domxml->appendChild($domxml->createElement('rows'));


	$row_count = 0;
	while($line = $rs->fetch(PDO::FETCH_ASSOC))
	{
        $row = $root->appendChild($domxml->createElement('row'));

		foreach ($line as $col_key => $col_val)
		{
			$col = $row->appendChild($domxml->createElement('column'));
			$col->setAttribute('name', strtolower($col_key));
			$col->appendChild($domxml->createTextNode(trim($col_val)));
		}
		$row_count++;
	}
    $root->setAttribute('total-rows', $row_count);
	$domxml->formatOutput = true;
	$xml = $domxml->saveXML();
	$domxml = null;

	return $xml;
}

/**
 * Multiple Recordsets to XML
 * Accepts multiple ADODB recordsets, converts it to XML, and returns the result.
 *
 * @param 		array 		$rs 	- an array of arrays containing geotable name and the recordset
 *									array(
 *										[0] => array('geotable' => 'streets, 'recordSet' => $rs_streets),
 *										[1] => array('geotable' => 'neighborhoods', 'recordSet' => $rs_neighborhoods)
 *									)
 * @return 		string		$xml		resulting xml
 */

function multi_rs2xml($queries)
{
	$xml = '';
	
	$domxml = new DOMDocument('1.0', 'utf-8');
	$results = $domxml->appendChild($domxml->createElement('results'));
	
	foreach ($queries as $query) {
		$rs = $query['recordSet'];
		$geotable = $query['geotable'];
		
		if (!$rs) {
			trigger_error("Caught Exception: bad recordset passed to multi_rs2xml function.", E_USER_ERROR);
			return false;
		}
		
		if ($rs->rowCount()>0){

			$totalRows = 0;
			$totalRows = count($rs);
		
			$result = $results->appendChild($domxml->createElement('result'));
			$result->setAttribute('geotable',$geotable);

			$root = $result->appendChild($domxml->createElement('rows'));


			$row_count = 0;
			while($line = $rs->fetch(PDO::FETCH_ASSOC))
			{
        		$row = $root->appendChild($domxml->createElement('row'));

				foreach ($line as $col_key => $col_val)
				{
					$col = $row->appendChild($domxml->createElement('column'));
					$col->setAttribute('name', strtolower($col_key));
					$col->appendChild($domxml->createTextNode(trim($col_val)));
				}
				$row_count++;
			}
    		$root->setAttribute('total-rows', $row_count);
    	
    	}
		
	}
	
	$domxml->formatOutput = true;
	$xml = $domxml->saveXML();
	$domxml = null;

	return $xml;
}

?>
