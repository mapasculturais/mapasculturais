<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.project';

$project = $entity->project;

$this->addEntityToJs($entity);

$this->addProjectToJs($project);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);

$_params = [
    'entity' => $entity,
    'project' => $project,
    'action' => $action
];


?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="ProjectController">
    <?php $this->part('singles/registration--header', $_params); ?>

    <article>
        <?php $this->applyTemplateHook('form','begin'); ?>

        <?php $this->part('singles/registration-single--header', $_params) ?>

        <?php $this->part('singles/registration-single--categories', $_params) ?>

        <?php $this->part('singles/registration-single--agents', $_params) ?>
        
        <?php $this->part('singles/registration-single--seals', $_params) ?>

        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <?php $this->applyTemplateHook('form','end'); ?>
    </article>
</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
