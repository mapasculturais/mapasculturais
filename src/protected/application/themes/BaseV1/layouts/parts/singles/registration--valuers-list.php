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
    if ($em->canUserEvaluateRegistration($entity, $valuer->user)) {
        $exclude_list[] = $valuer;
    } else {
        $include_list[] = $valuer;
    }
}
?>
<div class="registration-fieldset" id="registration-valuers--admin">
    <?php $this->applyTemplateHook('valuers-list','begin'); ?>
    <h4><?php i::_e('Avaliadores desta inscrição') ?></h4>
    <form class="js--registration-valuers-include-exclude-form">
        <small>
            <em>
                <?php i::_e('Marque/desmarque os avaliadores desta inscrição. Por padrão, são selecionados aqueles que avaliam de acordo com as regras de distribuição definidas.'); ?>
            </em>
        </small>
        <ul id="registration-commitee">
            <?php foreach($exclude_list as $valuer):
                $checked = $this->getValuersCheckedAttribute($valuer->user->id, $entity->valuersExcludeList);
                $inverse = $this->getValuersCheckedAttribute($valuer->user->id, $entity->valuersExcludeList, true);
            ?>
                <li>
                    <label>
                        <input type="checkbox" value="ref-<?php echo $valuer->user->id ?>" <?php echo $inverse; ?>
                               class="user-toggable" onclick="toggleRegistrationEvaluator(this)" />
                        <input type="checkbox" name="valuersExcludeList[]" value="<?php echo $valuer->user->id ?>"
                               class="sendable" <?php echo $checked ?>/>
                        <?php echo $valuer->name ?> <small><em><span>*</span></em></small>
                    </label>
                </li>
            <?php
            endforeach;
            foreach($include_list as $valuer):
                $checked = $this->getValuersCheckedAttribute($valuer->user->id, $entity->valuersIncludeList);
            ?>
                <li>
                    <label>
                        <input type="checkbox" value="ref-<?php echo $valuer->user->id ?>" <?php echo $checked; ?>
                               class="user-toggable" onclick="toggleRegistrationEvaluator(this)" />
                        <input type="checkbox" name="valuersIncludeList[]" value="<?php echo $valuer->user->id ?>"
                               class="sendable" <?php echo $checked ?> />
                        <?php echo $valuer->name ?>
                    </label>
                </li>
            <?php endforeach ?>
        </ul>
        <p>
            <small><span>*</span><em> <?php i::_e('Avaliador desta inscrição pela regra de distribuição.') ?></em></small>
        </p>
    </form>

    <?php $this->applyTemplateHook('valuers-list','end'); ?>
</div>