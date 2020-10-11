<?php 
use MapasCulturais\i;

$url = $this->controller->createUrl('applyEvaluationsSimple', [$entity->id]);
?>
<a href="<?= $url ?>" class="btn btn-primary hltip" onclick="return confirm('Deseja aplicar as avaliações? Esta ação não poderá ser desfeita.')" title="<?php i::_e('Esta ação definirá o status das inscrições de acordo com o resultado consolidado das avaliações') ?>"> 
    <?php i::_e('Aplicar avaliações'); ?> 
</a>