<?php

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
$app = App::i();
$entity = $this->controller->requestedEntity;
$registration = $entity->registration->accountabilityPhase;
$opportunity = $registration->opportunity;
$evaluation = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration]);

$this->jsObject['accountabilityPermissions'] = $registration->openFields;

$opportunity->registerRegistrationMetadata();

$_params = [
    'entity' => $registration,
    'action' => $this->controller->action,
    'opportunity' => $opportunity
];

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
$this->jsObject['entity']['entity']['object']['opportunity'] = $opportunity;

$this->addOpportunityToJs($opportunity);

$this->addOpportunitySelectFieldsToJs($opportunity);

$this->addRegistrationToJs($registration);
$this->jsObject['entity']['registrationStatus'] = $registration->status;
$this->jsObject['entity']['isPublishedResult'] = $registration->isPublishedResult ?: false;

$this->includeAngularEntityAssets($opportunity);

$template_hook_params = ['project' => $entity, 'registration' => $registration, 'opportunity' => $opportunity];
?>
<?php $this->applyTemplateHook('accountability-content', 'before', $template_hook_params) ?>
<div id="accountability" class="aba-content" ng-controller="OpportunityController">
    <div ng-controller="AccountabilityEvaluationForm">
    <?php $this->applyTemplateHook('accountability-content', 'begin', $template_hook_params) ?>

    <?php if(!$registration->canUser('evaluate')): ?>
        <section class="highlighted-message clearfix">
            <?php
            $registration_from = $registration->opportunity->registrationFrom->format('d/m/Y') ?? false;
            $registration_to = $registration->opportunity->registrationTo->format('d/m/Y') ?? false;
            $registration_to_hour = $registration->opportunity->registrationTo->format('H:i');

            printf(i::__("Prazo da prestação é de %s a %s até às %s."), '<strong>' . $registration_from . '</strong>', '<strong>' . $registration_to . '</strong>', '<strong>' . $registration_to_hour . '</strong>'); ?>
        </section>

        <h4><?php i::_e("Formulário de prestação de contas"); ?></h4>
        <p><?php i::_e("Confira as informações e preencha os campos em aberto para realizar a prestação solicitada pela equipe gestora da oportunidade."); ?></p>
    <?php endif; ?>

    <div class="registration-fieldset clearfix">
        <h4><?php i::_e("Número da Inscrição"); ?></h4>
        <?php if ($registration->canUser('evaluate')) :

            $last_phase = \OpportunityPhases\Module::getLastCreatedPhase($opportunity);
            $registration_last_phase = $app->repo('Registration')->findOneBy(['opportunity' => $last_phase, 'owner' => $registration->owner]);
            $url = $registration_last_phase ? $registration_last_phase->singleUrl : $registration->singleUrl;

            ?>

            <div class="registration-id alignleft"><a href="<?= $url ?>" style="font-weight: normal;"><?= $registration->number ?></a></div>
        <?php else: ?>
            <div class="registration-id alignleft"><?= $registration->number ?></div>
        <?php endif; ?>
    </div>

        <?php $this->part('singles/project--events', ['project' => $entity, 'create_rule_string' => $create_rule_string]) ?>

        <div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
            <?php $this->applyTemplateHook('registration-field-list', 'before'); ?>

            <?php if ($registration->canUser('evaluate')) : ?>
                <h4><?php i::_e("Formulário de prestação de contas"); ?></h4>
            <?php endif; ?>

            <ul class="attachment-list" ng-controller="RegistrationFieldsController">
                <?php $this->applyTemplateHook('registration-field-list', 'begin'); ?>
                    <li ng-repeat="field in data.fields" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : ''" ng-controller="OpportunityAccountability">
                        <div ng-if="canUserEdit(field)">
                            <?php
                                if ($registration->canUser('modify')) {
                                    $this->applyTemplateHook('registration-field-item', 'begin');
                                    $this->part('singles/registration-field-edit');
                                    $this->applyTemplateHook('registration-field-item', 'end');
                                } else {
                                    $this->part('singles/registration-field-view');
                                }
                            ?>
                        </div>
                        <div ng-if="!canUserEdit(field)">
                            <?php $this->part('singles/registration-field-view'); ?>
                        </div>
                    </li>
                <?php $this->applyTemplateHook('registration-field-list', 'end'); ?>
            </ul>
            <?php $this->applyTemplateHook('registration-field-list', 'after'); ?>
        </div>
         
    <?php if ($registration->canUser('modify')) { ?>
        <?php $this->part('accountability/send-button', ['entity' => $registration]); ?>
    <?php } ?>
    
    <?php if($registration->status > Registration::STATUS_DRAFT){ ?>
        <?php $this->part('accountability/registration-message', ['entity' => $registration]); ?>
    <?php }?>

    <?php $this->applyTemplateHook('accountability-content', 'end', $template_hook_params); ?>
    </div>
</div>
<?php $this->applyTemplateHook('accountability-content', 'after', $template_hook_params); ?>
