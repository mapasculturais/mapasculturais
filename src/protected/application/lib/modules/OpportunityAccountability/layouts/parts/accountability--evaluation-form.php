<?php
use MapasCulturais\i;
use MapasCulturais\Entities\RegistrationEvaluation;

$template_hook_params = ['registration' => $registration, 'opportunity' => $opportunity];
$disable = ($evaluation->status == RegistrationEvaluation::STATUS_EVALUATED) ? " disabled" : "";
// $this->jsObject['evaluationConfiguration'] = $entity->evaluationMethodConfiguration;
?>
<?php $this->applyTemplateHook('evaluationForm.accountability', 'before', $template_hook_params) ?>
<div class="registration-fieldset accountability-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.accountability', 'begin', $template_hook_params) ?>
    <section>
        <h4> <?php i::_e('Resultado') ?> </h4>
        <label>
            <input type="radio" ng-model="evaluationData.result" value="10" <?=$disable?>>
            <?php i::_e('Aprovado') ?>
        </label>

        <label>
            <input type="radio" ng-model="evaluationData.result" value="8" <?=$disable?>>
            <?php i::_e('Aprovado com ressalvas') ?>
        </label>

        <label>
            <input type="radio" ng-model="evaluationData.result" value="3" <?=$disable?>>
            <?php i::_e('Não aprovado') ?>
        </label>
    </section>

    <section>
        <h4><?= i::__('Parecer técnico') ?></h4>
        <textarea ng-model="evaluationData.obs" class="auto-height" <?=$disable?>></textarea>
    </section>

    <section class="actions">
        <?php if (!empty($disable)) { ?>
            <button class="btn btn-primary align-right" ng-click="reopenAccountability()"><?php i::_e("Reabrir prestação de contas") ?></button>
        <?php } else { ?>
            <button class="btn btn-primary align-right" ng-click="sendEvaluation()"><?php i::_e("Finalizar e enviar o parecer técnico") ?></button>
        <?php } ?>
    </section>
    <?php $this->applyTemplateHook('evaluationForm.accountability', 'end', $template_hook_params) ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.accountability', 'after', $template_hook_params) ?>
