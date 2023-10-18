<?php 
use MapasCulturais\i;

if(!$entity->canUser('modifyValuers')){
    return;
}

$committee = $opportunity->getEvaluationCommittee(false);
$em = $opportunity->getEvaluationMethod();

$can_evaluate = [];
$rules_list = [];
$exclude_list = [];
$include_list = [];

foreach($committee as $valuer) {
    if ($em->canUserEvaluateRegistration($entity, $valuer->user)) {
        $can_evaluate[] = $valuer;
        if(in_array($valuer->user->id, $entity->valuersIncludeList)) {
            $include_list[] = $valuer;
        } else {
            $rules_list[] = $valuer;
        }
    } elseif (in_array($valuer->user->id, $entity->valuersExcludeList)) {
        $rules_list[] = $valuer;
        $exclude_list[] = $valuer;
    }
}

usort($committee, function ($v1, $v2) use($rules_list) {
    if(in_array($v1, $rules_list) && in_array($v2, $rules_list)) {
        return $v1->name <=> $v2->name;
    } else if(in_array($v1, $rules_list) && !in_array($v2, $rules_list)) {
        return -1;
    } else if(!in_array($v1, $rules_list) && in_array($v2, $rules_list)) {
        return 1;
    } else {
        return $v1->name <=> $v2->name;
    }
});

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
            <?php 
            foreach($committee as $valuer): 
                $checked = in_array($valuer, $can_evaluate);
                $from_rule = in_array($valuer, $rules_list);
                $list = $from_rule ? 'valuersExcludeList' : 'valuersIncludeList';
            ?>
                <li>
                    <label>
                        <input type="checkbox" value="ref-<?php echo $valuer->user->id ?>"
                               class="user-toggable" onclick="toggleRegistrationEvaluator(this)" <?= $checked ? 'checked' : '' ?>/>
                        <input type="checkbox" name="<?=$list?>[]" value="<?php echo $valuer->user->id ?>"
                               class="sendable" <?= !$from_rule && $checked ? 'checked' : '' ?>/>
                        <?php echo $valuer->name ?> 
                        <?php if($from_rule): ?>
                            <small><em><span>*</span></em></small>
                        <?php endif ?>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
        <p>
            <small><span>*</span><em> <?php i::_e('Avaliador desta inscrição pela regra de distribuição.') ?></em></small>
        </p>
    </form>

    <?php $this->applyTemplateHook('valuers-list','end'); ?>
</div>