<?php
use MapasCulturais\Entities\Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';
$this->jsObject['angularAppDependencies'][] = 'ui.sortable';

$this->addEntityToJs($entity);

$this->addOpportunityToJs($entity);

$this->addOpportunitySelectFieldsToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');
}

$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

?>



<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'opportunities','home_title' => 'entities: My Opportunities']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content opportunity" ng-controller="OpportunityController">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <?php $this->part('singles/opportunity-header--owner-entity', ['entity' => $entity]) ?>

        <!--.header-image-->
        <?php $this->applyTemplateHook('header-content','before'); ?>
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--opportunity.png']); ?>

            <?php $this->part('singles/type', ['entity' => $entity]) ?>


            <?php $this->part('entity-parent', ['entity' => $entity, 'child_entity_request' => $child_entity_request]) ?>

            <?php $this->part('singles/name', ['entity' => $entity]) ?>
            <?php $this->applyTemplateHook('header-content','end'); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->part('singles/opportunity-tabs', ['entity' => $entity]) ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>

        <?php $this->part('singles/opportunity-about', ['entity' => $entity]) ?>

        <?php if($this->isEditable()): ?>
            <?php $this->part('singles/opportunity-registrations--config', ['entity' => $entity]) ?>
            <?php if(!$entity->isNew()): ?>
                <?php $this->part('singles/opportunity-evaluations--config', ['entity' => $entity]) ?>
            <?php endif; ?>

        <?php else : ?>
            <?php $this->part('singles/opportunity-registrations--tables', ['entity' => $entity]) ?>

            <?php if($entity->canUser('viewEvaluations') || $entity->canUser('@control')): ?>
                <?php $this->part('singles/opportunity-evaluations', ['entity' => $entity]) ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar opportunity">
    <?php $this->applyTemplateHook('sidebar-left','begin'); ?>
    
    <!-- Related Seals BEGIN -->
    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>
    <!-- Related Seals END -->
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>

    <?php $this->applyTemplateHook('sidebar-left','end'); ?>
</div>

<div class="sidebar opportunity sidebar-right">
    <?php $this->applyTemplateHook('sidebar-right','begin'); ?>
    
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar o projeto");?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>

    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>

    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>

    <?php $this->part('link-list.php', array('entity'=>$entity)); ?>

    <?php $this->applyTemplateHook('sidebar-right','end'); ?>
</div>
