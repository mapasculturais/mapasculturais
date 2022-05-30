<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$editEntity = $action === 'create' || $action === 'edit';

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.project';

$this->addEntityToJs($entity);

$this->addProjectToJs($entity);

if(!$entity->isNew() && $entity->canUser('@control')){
    $this->addProjectEventsToJs($entity);
}

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');
}

$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

//$this->part('singles/breadcrumb', ['entity' => $entity]);

?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'projects','home_title' => 'entities: My Projects']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content project" ng-controller="ProjectController">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <!--.header-image-->
        <?php $this->applyTemplateHook('header-content','before'); ?>
        <div class="container-card">
            <div class="header-content edit-card">
                <?php $this->applyTemplateHook('header-content','begin'); ?>
                <div class="edit-card-header">
                    <div class="edit-card-header-avatar">
                        <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--project.png']); ?>
                    </div>
                    <div class= "edit-card-header-body">
                        <?php $this->part('singles/type', ['entity' => $entity]) ?>

                        <?php $this->part('entity-parent', ['entity' => $entity, 'child_entity_request' => $child_entity_request]) ?>

                        <?php $this->part('singles/name', ['entity' => $entity]) ?>
                        
                    </div>
                </div>
                <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
                <?php if($this->isEditable() && $entity->shortDescription && mb_strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning"><?php \MapasCulturais\i::_e("O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui");?> <?php echo mb_strlen($entity->shortDescription) ?> <?php \MapasCulturais\i::_e("caracteres. Você deve alterar seu texto ou este será cortado ao salvar.");?></div>
                <?php endif; ?>
                <div class="widget">
                    <?php if ($this->isEditable() || $entity->shortDescription): ?>
                        <h3 class=" <?php echo ($entity->isPropertyRequired($entity,"shortDescription") && $editEntity? 'required': '');?>"> <?php \MapasCulturais\i::_e("Descrição curta");?> <?php if($this->isEditable()){ ?>(<span data-element='countLength'><?=mb_strlen($entity->shortDescription)?></span><?php \MapasCulturais\i::_e("/400 Caracteres)");?></span><?php } ?></h3>
                        <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta para o projeto");?>" data-tpl='<textarea data-element="shortDescription" maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                    <?php endif; ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
                    <?php if($this->isEditable() || $entity->site): ?>
                        <div class="widget">
                            <h3 class="label <?php echo ($entity->isPropertyRequired($entity,"site") && $editEntity? 'required': '');?>"><?php \MapasCulturais\i::_e("Site");?></h3>
                            <span ng-if="data.isEditable" class="js-editable" data-edit="site" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Site");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira a url de seu site");?>"><?php echo $entity->site; ?></span>
                            <a ng-if="!data.isEditable" class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    </div>
                    <?php endif; ?>
                    
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                
                <?php $this->applyTemplateHook('tab-about-service','after'); ?> 
                <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->part('singles/project-tabs', ['entity' => $entity]) ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>

        <?php $this->part('singles/project-events', ['entity' => $entity]) ?>

        <?php $this->part('singles/project-about', ['entity' => $entity]) ?>

        <!-- #permissao -->
        <?php $this->part('singles/permissions') ?>
        <!-- #permissao -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar project">
    <?php $this->applyTemplateHook('sidebar-left','begin'); ?>
    
    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>
    
    

    <?php $this->applyTemplateHook('sidebar-left','end'); ?>
</div>
<div class="sidebar project sidebar-right">
    <?php $this->applyTemplateHook('sidebar-right','begin'); ?>

    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar o projeto");?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>

    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>

    <?php $this->part('singles/widget-projects', ['entity' => $entity, 'projects' => $entity->children->toArray()]); ?>

    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>

    <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    
    <?php $this->applyTemplateHook('sidebar-right','end'); ?>
</div>
