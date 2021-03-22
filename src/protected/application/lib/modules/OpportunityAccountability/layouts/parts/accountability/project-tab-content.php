<?php

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;
$registration = $entity->registration->accountabilityPhase;
$opportunity = $registration->opportunity;

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
        <div class="registration-id alignleft"><?= $registration->number ?></div>
    </div>
    
    <?php $this->part('singles/project--events', ['project' => $entity]) ?>
   
    <?php if($registration->status > MapasCulturais\Entities\Registration::STATUS_DRAFT): ?>

        <?php $this->part('singles/registration-single--fields', $_params) ?>
    <?php else : ?>
        <?php $this->part('singles/registration-edit--fields', $_params) ?>
        <?php $this->part('accountability/send-button', $_params) ?>
    <?php endif; ?>

    <?php $this->applyTemplateHook('accountability-content', 'end', $template_hook_params) ?>
    </div>
</div>
<?php $this->applyTemplateHook('accountability-content', 'after', $template_hook_params) ?>