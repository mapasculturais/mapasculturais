<?php
$this->layout = 'nolayout';

/*
 * Mapas Culturais entity seal atributed printing.
 */
$entity = $relation->seal;
$period = new DateInterval("P" . $entity->validPeriod . "M");
$dateIni = $relation->createTimestamp->format("d/m/Y");
$dateFin = $relation->createTimestamp->add($period);
$dateFin = $dateFin->format("d/m/Y");

$mensagem = "							Impressão do certificado do selo [sealName]
					ao [entityDefinition] [entityName] 
						durante o período de [dateIni] a [dateFin].
		
						____________________________________
									    				Marylly";

$mensagem = str_replace("[sealName]",$relation->seal->name,$mensagem);
$mensagem = str_replace("[entityDefinition]",$relation->owner->entityType,$mensagem);
$mensagem = str_replace("[entityName]",$relation->owner->name,$mensagem);
$mensagem = str_replace("[dateIni]",$dateIni,$mensagem);
$mensagem = str_replace("[dateFin]",$dateFin,$mensagem);
?>
<?php echo nl2br($mensagem);?>

