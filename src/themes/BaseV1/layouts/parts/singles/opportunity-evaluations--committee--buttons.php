<?php
$today = new DateTime('now');
$em = $entity->evaluationMethodConfiguration;

$mess = "";
if($today < $em->evaluationFrom){
    $mess = \MapasCulturais\i::__('Não é possível enviar as avaliações antes do início do período de avaliação');
}elseif($today > $em->evaluationTo){
    $mess = \MapasCulturais\i::__('Não é possível enviar após término do período de avaliação');
}else{
    $mess = \MapasCulturais\i::__('Para Enviar as avaliações, você deve avaliar todas as inscrições');
}

?>
<div id="evaluation-committee-buttons" class="clearfix" style="margin-bottom: 20px;">
    <?php if($entity->canUser('sendUserEvaluations')): ?>
        <a href="<?php echo $entity->sendEvaluationsUrl ?>" class="btn btn-primary alignright"><?php \MapasCulturais\i::_e('Enviar Avaliações'); ?></a>
    <?php elseif($entity->canUser('evaluateRegistrations')): ?>
        <a class="btn btn-primary disabled hltip alignright" title="<?= $mess ?>"><?php \MapasCulturais\i::_e('Enviar Avaliações'); ?></a>
    <?php endif; ?>
</div>