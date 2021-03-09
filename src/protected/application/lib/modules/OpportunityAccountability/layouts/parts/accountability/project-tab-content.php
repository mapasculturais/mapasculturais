<?php
$entity = $this->controller->requestedEntity;
$registration = $entity->registration->accountabilityPhase;
$opportunity = $registration->opportunity;

$opportunity->registerRegistrationMetadata();

$_params = [
    'entity' => $registration,
    'action' => $action,
    'opportunity' => $opportunity
];

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
$this->jsObject['entity']['entity']['object']['opportunity'] = $opportunity;

$this->addOpportunityToJs($opportunity);

$this->addOpportunitySelectFieldsToJs($opportunity);

$this->addRegistrationToJs($registration);

$this->includeAngularEntityAssets($opportunity);
?>
<div id="accountability" class="aba-content" ng-controller="OpportunityController">
    <h2><?= $registration->number ?></h2>
    <?php $this->part('singles/registration-edit--fields', $_params) ?>

    <?php $this->part('accountability/send-button', $_params) ?>
</div>