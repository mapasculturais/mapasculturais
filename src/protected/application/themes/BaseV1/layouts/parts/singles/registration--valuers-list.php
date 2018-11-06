<?php 
use MapasCulturais\i;

if(false) $entity = new MapasCulturais\Entities\Registration;
if(false) $opportunity = new MapasCulturais\Entities\Opportunity;

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
        <?php foreach($exclude_list as $valuer): $checked = $entity->valuers_exclude_list?>
            <li>
                <label>
                    <input type="checkbox" name="valuers_include_list[]" value="<?php echo $valuer->user->id ?>"/> 
                    <?php echo $valuer->name ?>
                </label>
            </li>
        <?php endforeach ?> 
    </ul>

    <strong><?php i::_e('Lista de inclusão') ?></strong><br>
    <small><em><?php i::_e('Pelas regras de distribuição configuradas, os agentes abaixo NÃO SÃO avaliadores desta inscrição. Marque aqueles que você deseja CONCEDER a permissão de avaliar esta inscrição.') ?></em></small>
    <ul>
        <?php foreach($include_list as $valuer): $checked = $entity->valuers_include_list?>
            <li>
                <label>
                    <input type="checkbox" name="valuers_include_list[]" value="<?php echo $valuer->user->id ?>"/> 
                    <?php echo $valuer->name ?>
                </label>
            </li>
        <?php endforeach ?> 
    </ul>

    <?php $this->applyTemplateHook('valuers-list','end'); ?>
</div>