<?php 
use MapasCulturais\i;

if(!$entity->canUser('modifyValuers')){
    return;
}

$committee = $opportunity->getEvaluationCommittee(false);
$em = $opportunity->getEvaluationMethod();

$include_list = [];
$exclude_list = [];

foreach($committee as $valuer){
    if($em->canUserEvaluateRegistration($entity, $valuer->user)){
        $exclude_list[] = $valuer;
    } else {
        $include_list[] = $valuer;
    }
} 
?>
<div class="registration-fieldset">
    <?php $this->applyTemplateHook('valuers-list','begin'); ?>
    <h4><?php i::_e('Avaliadores desta inscrição') ?></h4>
    <form class="js--registration-valuers-include-exclude-form">
    <strong><?php i::_e('Lista de exclusão') ?></strong><br>
    <small><em><?php i::_e('Pelas regras de distribuição configuradas, os agentes abaixo SÃO avaliadores desta inscrição. Marque aqueles que você deseja EXCLUIR a permissão de avaliar esta inscrição.') ?></em></small>
    <ul>
        <?php foreach($exclude_list as $valuer): $checked = in_array($valuer->user->id, $entity->valuersExcludeList) ? 'checked="checked"' : '' ?>
            <li>
                <label>
                    <input type="checkbox" name="valuersExcludeList[]" value="<?php echo $valuer->user->id ?>" <?php echo $checked ?>/> 
                    <?php echo $valuer->name ?>
                </label>
            </li>
        <?php endforeach ?> 
    </ul>

    <strong><?php i::_e('Lista de inclusão') ?></strong><br>
    <small><em><?php i::_e('Pelas regras de distribuição configuradas, os agentes abaixo NÃO SÃO avaliadores desta inscrição. Marque aqueles que você deseja CONCEDER a permissão de avaliar esta inscrição.') ?></em></small>
    <ul>
        <?php foreach($include_list as $valuer): $checked = in_array($valuer->user->id, $entity->valuersIncludeList) ? 'checked="checked"' : '' ?>
            <li>
                <label>
                    <input type="checkbox" name="valuersIncludeList[]" value="<?php echo $valuer->user->id ?>" <?php echo $checked ?>/> 
                    <?php echo $valuer->name ?>
                </label>
            </li>
        <?php endforeach ?> 
    </ul>

    <?php $this->applyTemplateHook('valuers-list','end'); ?>
</div>