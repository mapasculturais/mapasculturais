<?php
use MapasCulturais\i;
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('area');
    $this->addTaxonoyTermsToJs('tag');
}

$this->includeMapAssets();
$this->includeAngularEntityAssets($entity);
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'agents','home_title' => 'entities: My Agents']); ?><!--.part/singles/breadcrumb.php -->

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action)); ?><!--.part/editable-entity.php -->

<article class="main-content agent">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?><!--.part/singles/header-image.php -->

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?><!--.part/singles/entity-status.php -->

        <?php $this->applyTemplateHook('header-content','before'); ?>
        <div class="container-card">            
            <div class="header-content">
                    <?php $this->applyTemplateHook('header-content','begin'); ?>

                    <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--agent.png']); ?><!--.part/singles/avatar.php -->

                    <?php $this->part('singles/type', ['entity' => $entity]) ?><!--.part/singles/type.php -->

                    <?php $this->part('singles/name', ['entity' => $entity]) ?><!--.part/singles/name.php -->

                    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
                    <?php $this->applyTemplateHook('header-content','end'); ?>
            </div>
            <!--.header-content-->
            <?php $this->applyTemplateHook('header-content','after'); ?>
        </div>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>

        <?php $this->part('tab', ['id' => 'sobre', 'label' => i::__("Sobre"), 'active' => true]) ?>
        <?php if(!($this->controller->action === 'create')):?>
            <?php $this->part('tab', ['id' => 'permissao', 'label' => i::__("Responsáveis")]) ?>
        <?php endif;?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>

            <?php $this->part('singles/agent-form', ['entity' => $entity, 'editEntity' => $editEntity]); ?><!--.part/singles/agent-form.php -->
            
            <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?><!--.part/singles/location.php -->
            
            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
                <span class="descricao js-editable <?php echo ($entity->isPropertyRequired($entity,"longDescription") && $this->isEditable()? 'required': '');?>" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Agente");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do agente");?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>

                <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?><!--.part/video-gallery.php -->

                <?php $this->part('gallery.php', array('entity'=>$entity)); ?><!--.part/gallery.php -->

            <?php $this->applyTemplateHook('tab-about','end'); ?>
        </div>
        <!-- #sobre -->
        <?php $this->part('singles/permissions') ?><!--.part/singles/permissions.php -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after');?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?><!--.part/owner.php -->

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar agent">
    <!-- Related Seals BEGIN -->
    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>
    <!-- Related Seals END -->

    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar agent sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar o agente.");?><span class="close"></span></p>
        </div>
    <?php endif; ?>

    <!-- Related Admin Agents BEGIN -->
        <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Admin Agents END -->

    <!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->spaces, 'title' => 'entities: Spaces of the agent')); ?>
    <!-- Children END -->

    <!-- Relations Groups BEGIN -->
        <?php $this->part('singles/list-relations.php', array('entities'=>$entity)); ?>
    <!-- Relations Groups END -->
    
    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->projects, 'title' => 'entities: Projects of the agent')); ?>
    <!-- Children END -->

    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->children, 'title' => 'entities: Agent children')); ?>
    <!-- Children END -->

    <!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->

    <!-- History BEGIN -->
        <?php $this->part('history.php', array('entity' => $entity)); ?>
    <!-- History END -->
</div>
