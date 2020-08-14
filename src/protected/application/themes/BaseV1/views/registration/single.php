<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);
$this->includeEditableEntityAssets();

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="OpportunityController">
    <?php $this->part('singles/registration--header', $_params); ?>

    <article>
        <?php $this->applyTemplateHook('form','begin'); ?>

        <?php $this->part('singles/registration-single--header', $_params) ?>

        <?php $this->part('singles/registration-single--categories', $_params) ?>   
        
        <?php $this->part('singles/registration-single--agents', $_params) ?>

        <?php $this->part('singles/registration-single--spaces', $_params) ?>
        
        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <?php $this->applyTemplateHook('form','end'); ?>
    </article>
    <?php $this->part('singles/registration--valuers-list', $_params) ?>
</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
