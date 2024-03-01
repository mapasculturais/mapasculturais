<?php

$action = preg_replace("#^(\w+/)#", "", $this->template);

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];

$opMetaSpace = $app->repo('OpportunityMeta')->findBy(['owner' =>  $entity->opportunity->id, 'key' => 'useSpaceRelationIntituicao']);

?>

<article class="main-content registration" ng-controller="OpportunityController">

    <article ng-controller="RegistrationFieldsController">
        <?php $this->applyTemplateHook('form', 'begin'); ?>

        <?php $this->part('singles/registration-edit--categories', $_params) ?>

        <?php $this->part('singles/registration-edit--range', $_params) ?>

        <?php $this->part('singles/registration-edit--proponent-types', $_params) ?>

        <div ng-controller="OpportunityController">
            <?php $this->part('singles/registration-edit--agents', $_params); ?>
        </div>

        <?php $this->part('singles/registration-edit--spaces', array('params' => $_params, 'query' => $opMetaSpace)) ?>

        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php $this->applyTemplateHook('form', 'end'); ?>
    </article>

</article>
<?php if ($entity->evaluationMethodConfiguration): ?>
    <?php $this->part('singles/registration--sidebar--left', $_params) ?>
    <?php $this->part('singles/registration--sidebar--right', $_params) ?>
<?php endif; ?>