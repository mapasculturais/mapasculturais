<?php
/**
 * Arquivo usado somente para quem está criando a oportunidade em mode de edição
 */
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->jsObject['isEditable'] = true;

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity->opportunity);

$this->addOpportunitySelectFieldsToJs($entity->opportunity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];

// CONSULTA AO BANCO PARA SABER SE TEM REGISTRO
$opMetaSpace = $app->repo('OpportunityMeta')->findBy(['owner' =>  $entity->opportunity->id, 'key' => 'useSpaceRelationIntituicao']);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));?>

<article class="main-content registration" ng-controller="OpportunityController">

    <?php $this->part('singles/registration--header', $_params); ?>
    
    <article ng-controller="RegistrationFieldsController" >
        <?php $this->applyTemplateHook('form','begin');?>
        
        <?php $this->part('singles/registration-edit--header', $_params) ?>
        
        <?php $this->part('singles/registration-edit--categories', $_params) ?>
        
        <div ng-controller="OpportunityController">
            <?php $this->part('singles/registration-edit--agents', $_params);?>
        </div>
        
        <?php $this->part('singles/registration-edit--spaces', array('params' => $_params, 'query' => $opMetaSpace) ) ?>

        <?php // Desabilitando este template por enquanto, pois não é a melhor forma de apresentar para o usuário que está se inscrevendo ?>
        <?php //$this->part('singles/registration-edit--seals', $_params) ?>
        <?php $this->part('singles/registration-edit--fields', $_params) ?>

        <?php if(!$entity->preview): ?>
            <?php $this->part('singles/registration-edit--send-button', $_params) ?>
        <?php endif; ?>

        <?php $this->applyTemplateHook('form','end'); ?>
    </article>

</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
