<?php
$this->layout = 'nolayout';
$app = \MapasCulturais\App::i();

/*
 * Mapas Culturais entity seal atributed printing.
 */
$entity = $relation->seal;
$period = new DateInterval("P" . $entity->validPeriod . "M");
$dateIni = $relation->createTimestamp->format("d/m/Y");
$dateFin = $relation->createTimestamp->add($period);
$dateFin = $dateFin->format("d/m/Y");

$mensagem = $relation->seal->certificateText;
$mensagem = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$mensagem);
$mensagem = str_replace("[sealName]",$relation->seal->name,$mensagem);
$mensagem = str_replace("[sealOwner]",$relation->seal->agent->name,$mensagem);
$mensagem = str_replace("[sealShortDescription]",$relation->seal->shortDescription,$mensagem);
$mensagem = str_replace("[sealRelationLink]",$app->createUrl('seal','printsealrelation',[$relation->id]),$mensagem);
$mensagem = str_replace("[entityDefinition]",$relation->owner->entityTypeLabel(),$mensagem);
$mensagem = str_replace("[entityName]",$relation->owner->name,$mensagem);
$mensagem = str_replace("[dateIni]",$dateIni,$mensagem);
$mensagem = str_replace("[dateFin]",$dateFin,$mensagem);
?>
<h1 align="center"><?php \MapasCulturais\i::_e("Certificado");?></h1>
<div align="center">
  <?php echo nl2br($mensagem);?>
</div>
