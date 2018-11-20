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
<div class="registration-fieldset">
    <?php $this->applyTemplateHook('valuers-list','begin'); ?>
    <h4><?php i::_e('Avaliadores desta inscrição') ?></h4>
    <form class="js--registration-valuers-include-exclude-form">
        <small>
            <em>
                <?php i::_e('Marque/desmarque os avaliadores desta inscrição. Por padrão, são selecionados aqueles que avaliam de acordo com as regras de distribuição definidas.'); ?>
            </em>
        </small>
        <ul id="registration-commitee" style="list-style: none; margin-top: 10px; margin-bottom: 5px; padding: 0;">
            <?php
            foreach($exclude_list as $valuer):
                $checked = in_array($valuer->user->id, $entity->valuersExcludeList) ? 'checked="checked"' : '';
                $inverse = !in_array($valuer->user->id, $entity->valuersExcludeList) ? 'checked="checked"' : '';
            ?>
                <li>
                    <label>
                        <input type="checkbox" value="ref-<?php echo $valuer->user->id ?>" <?php echo $inverse; ?> class="user-toggable" onclick="toggleRegistrationEvaluator(this)"/>
                        <input type="checkbox" name="valuersExcludeList[]" value="<?php echo $valuer->user->id ?>"
                               style="display: none" class="sendable" <?php echo $checked ?>/>
                        <?php echo $valuer->name ?> <small><em><span style="color: darkred; font-weight: bolder">*</span></em></small>
                    </label>
                </li>
            <?php endforeach ?>
            <?php foreach($include_list as $valuer):
                $checked = in_array($valuer->user->id, $entity->valuersIncludeList) ? 'checked="checked"' : '';
            ?>
                <li>
                    <label>
                        <input type="checkbox" name="valuersIncludeList[]" value="<?php echo $valuer->user->id ?>" <?php echo $checked ?>/>
                        <?php echo $valuer->name ?>
                    </label>
                </li>
            <?php endforeach ?>
        </ul>
        <p>
            <small><span style="color: darkred; font-weight: bolder">*</span><em> Avaliador desta inscrição pela regra de distribuição.</em></small>
        </p>

<!--    <strong>--><?php //i::_e('Lista de inclusão') ?><!--</strong><br>-->
<!--    <small><em>--><?php //i::_e('Pelas regras de distribuição configuradas, os agentes abaixo NÃO SÃO avaliadores desta inscrição. Marque aqueles que você deseja CONCEDER a permissão de avaliar esta inscrição.') ?><!--</em></small>-->
<!--    <ul>-->
<!--        --><?php //foreach($include_list as $valuer): $checked = in_array($valuer->user->id, $entity->valuersIncludeList) ? 'checked="checked"' : '' ?>
<!--            <li>-->
<!--                <label>-->
<!--                    <input type="checkbox" name="valuersIncludeList[]" value="--><?php //echo $valuer->user->id ?><!--" --><?php //echo $checked ?><!--/> -->
<!--                    --><?php //echo $valuer->name ?>
<!--                </label>-->
<!--            </li>-->
<!--        --><?php //endforeach ?><!-- -->
<!--    </ul>-->
    </form>

    <?php $this->applyTemplateHook('valuers-list','end'); ?>
</div>