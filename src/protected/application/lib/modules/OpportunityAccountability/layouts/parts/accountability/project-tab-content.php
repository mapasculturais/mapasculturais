<?php
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
    
    <h2><?= $registration->number ?></h2>
    
    <?php $this->part('singles/project--events', ['project' => $entity]) ?>
   
    <?php if($registration->status > MapasCulturais\Entities\Registration::STATUS_DRAFT): ?>
        <?php $this->part('singles/registration-single--fields', $_params) ?>
    <?php else: ?>
        <?php $this->part('singles/registration-edit--fields', $_params) ?>
        <?php $this->part('accountability/send-button', $_params) ?>

    <?php endif; ?>

    <?php $this->applyTemplateHook('accountability-content', 'end', $template_hook_params) ?>
    </div>
</div>
<?php $this->applyTemplateHook('accountability-content', 'after', $template_hook_params) ?>