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

$avaliable_evaluationFields = $entity->opportunity->avaliableEvaluationFields ?? [];
$app->view->jsObject['avaliableEvaluationFields'] = $avaliable_evaluationFields;

$can_see = function($field) use ($entity, $avaliable_evaluationFields){  

    /** O Gestor pode ver todos os campos */
    if($entity->opportunity->canUser("@control")){
        return true;
    }

    if($entity->canUser("viewUserEvaluation")  && !isset($avaliable_evaluationFields[$field])){
        return false;
    }

    return true;
};

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

        <?php if($can_see('category')): ?>
       
        <?php $this->part('singles/registration-single--categories', $_params) ?>   

        <?php endif ?>
        
        <?php if($can_see('agentsSummary')): ?>

        <?php $this->part('singles/registration-single--agents', $_params) ?>

        <?php endif ?>

        <?php if($can_see('spaceSummary')): ?>

        <?php $this->part('singles/registration-single--spaces', $_params) ?>
        
        <?php endif ?>
        
        <?php $this->part('singles/registration-single--fields', $_params) ?>

        <?php $this->applyTemplateHook('form','end'); ?>
    </article>
    <?php $this->part('singles/registration--valuers-list', $_params) ?>
</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
