<?php
use MapasCulturais\i;
use MapasCulturais\Entities\RegistrationEvaluation;

$template_hook_params = ['registration' => $registration, 'opportunity' => $opportunity];
$disable = ($evaluation->status == RegistrationEvaluation::STATUS_EVALUATED) ? " disabled" : null;
// $this->jsObject['evaluationConfiguration'] = $entity->evaluationMethodConfiguration;
?>
<?php $this->applyTemplateHook('evaluationForm.accountability', 'before', $template_hook_params) ?>
<div class="registration-fieldset accountability-evaluation-form">
    <?php $this->applyTemplateHook('evaluationForm.accountability', 'begin', $template_hook_params) ?>
    <section>
        <?php if(!$disable && $registration->canUser('evaluate')){?>
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
        <?php } ?>
    </section>
    <?php if(!$disable){?>
        <section>
            <h4><?= i::__('Parecer técnico') ?></h4>
            <?php if($registration->canUser('evaluate')){?>
            
            <div id="evaluation-editor" ng-bind-html="::evaluationData.obs"></div>

            <input id="evaluate-editor-input" type="hidden" ng-model="evaluationData.obs">
            <?php } else {?>
                <p class="registration-help" ng-bind-html="::evaluationData.obs"></p>
            <?php } ?>
        </section>
    <?php } ?>

    <section class="actions">
    <?php if($registration->canUser('evaluate')){?>
        <?php if (!empty($disable)) { ?>
            <span><?php i::_e("Parecer técnico enviado com status") ?> <b>{{resultString}}</b></span>
            <?php if(!$registration->isPublishedResult){?>
                <button class="btn btn-primary align-right" ng-click="reopenAccountability()"><?php i::_e("Reabrir prestação de contas") ?></button>
            <?php } else { ?>
                <button class="btn btn-success align-right"><?php i::_e("Resultado já publicado") ?></button>
            <?php }?>
            <div class="evaluation-obs" ng-bind-html="::evaluationData.obs"></div>
        <?php } else { ?>
            <button class="btn btn-primary align-right" ng-click="sendEvaluation()"><?php i::_e("Finalizar e enviar o parecer técnico") ?></button>
        <?php } ?>
    <?php } ?>
    </section>
    <?php $this->applyTemplateHook('evaluationForm.accountability', 'end', $template_hook_params) ?>
</div>
<?php $this->applyTemplateHook('evaluationForm.accountability', 'after', $template_hook_params) ?>
