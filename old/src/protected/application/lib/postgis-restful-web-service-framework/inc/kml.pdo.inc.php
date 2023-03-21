<?php
/**
 * Recordset to KML
 * Accepts and ADODB recordset, converts it to KML, and returns the result.
 *
 * @param 		object 		$rs 		record set object
 * @return 		string		$kml		resulting kml
 */

function rs2kml($rs)
{
	if (!$rs) {
		trigger_error("Caught Exception: bad recordset passed to rs2kml function.", E_USER_ERROR);
		return false;
	}

	$kml = '';

	$domxml = new DOMDocument('1.0', 'utf-8');
	$root = $domxml->appendChild($domxml->createElement('kml'));
	$root->setAttribute('xmlns','http://www.opengis.net/kml/2.2');
	
	$document = $root->appendChild($domxml->createElement('Document'));
	
	$style = $document->appendChild($domxml->createElement('Style'));   
  $style->setAttribute('id', 'stylish');             
  $line = $style->appendChild($domxml->createElement('LineStyle'));   
  $lineColor = $line->appendChild($domxml->createElement('color', 'ff0086ff'));    
  $lineWidth = $line->appendChild($domxml->createElement('width', '3'));  
  $poly = $style->appendChild($domxml->createElement('PolyStyle'));    
  $lineColor = $poly->appendChild($domxml->createElement('color', '9aefefef')); 

	while($line = $rs->fetch(PDO::FETCH_ASSOC))
	{
    $placemark = $document->appendChild($domxml->createElement('Placemark'));
    
    $name = null;
    
    $kml_geom = $domxml->createDocumentFragment();
    $kml_geom->appendXML($line['kml']);
    
    $extended_data = $domxml->createElement('ExtendedData');

		foreach ($line as $col_key => $col_val)
		{
		  if ($col_key !== "kml"){
		    if (!isset($name)){
		      $name = $domxml->createElement('name');
		      $name->appendChild($domxml->createTextNode($col_val));
		    }
		    $data = $extended_data->appendChild($domxml->createElement('Data'));
		    $data->setAttribute('name', strtolower($col_key));
		    $value = $data->appendChild($domxml->createElement('value'));
		    $value->appendChild($domxml->createTextNode(trim($col_val)));
      }
    }
    
    $placemark->appendChild($name);
    $placemark->appendChild($kml_geom);
    $placemark->appendChild($extended_data);
    $placemark->appendChild($domxml->createElement('styleUrl', '#stylish'));
	}
	$domxml->formatOutput = true;
	$kml = $domxml->saveXML();
	$domxml = null;
  
	return $kml;
}

?>
