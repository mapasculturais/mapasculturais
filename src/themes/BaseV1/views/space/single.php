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

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

$this->entity = $entity;

?>
<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'spaces','home_title' => 'entities: My Spaces']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', ['entity' => $entity, 'action' => $action]);  ?>

<article class="main-content space">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <!--.header-image-->
        <?php $this->applyTemplateHook('header-content','before'); ?>
        <div class="container-card">
            <div class="header-content edit-card ">
                <?php $this->applyTemplateHook('header-content','begin'); ?>
                <div class="edit-card-header">
                        <div class="edit-card-header-avatar">
                            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
                        </div>
                        <div class="edit-card-header-body">
                            <?php $this->part('singles/type', ['entity' => $entity]) ?>

                            <?php $this->part('entity-parent', ['entity' => $entity, 'child_entity_request' => $child_entity_request]) ?>

                            <?php $this->part('singles/name', ['entity' => $entity]) ?>

                            <?php $this->part('widget-areas', ['entity' => $entity]); ?>
                        </div>   
                </div> 
                <?php $this->part('widget-tags', ['entity' => $entity]); ?>
                
                <?php if($this->isEditable() && $entity->shortDescription && mb_strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning"><?php \MapasCulturais\i::_e("O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui");?> <?php echo mb_strlen($entity->shortDescription) ?> <?php \MapasCulturais\i::_e("caracteres. Você deve alterar seu texto ou este será cortado ao salvar.");?></div>
                <?php endif; ?>

                <div class="widget">
                    <h3 class=" <?php echo ($entity->isPropertyRequired($entity,"shortDescription") && $this->isEditable()? 'required': '');?>"><?php \MapasCulturais\i::_e("Descrição curta");?> <?php if($this->isEditable()){ ?>(<span data-element='countLength'><?=mb_strlen($entity->shortDescription)?></span><?php \MapasCulturais\i::_e("/400 Carecteres)");?> <?php } ?></h3>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-tpl='<textarea data-element="shortDescription" maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </div>

                <?php if($this->isEditable() || $entity->site): ?>
                    <div class="widget"><h3><?php \MapasCulturais\i::_e("Site");?></h3>
                    <?php if($this->isEditable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Site');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira a url de seu site');?>"><?php echo $entity->site; ?></span>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                    </div>
                 <?php endif; ?>


                <?php $this->part('redes-sociais', ['entity' => $entity]); ?>

                <?php $this->applyTemplateHook('header-content','end'); ?>
            </div>
</div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <?php $this->part('tab', ['id' => 'sobre', 'label' => i::__("Sobre"), 'active' => true]) ?>
        <?php if(!($this->controller->action === 'create')):?>
            <?php $this->part('tab', ['id' => 'permissao', 'label' => i::__("Responsáveis"), 'active' => true]) ?>
        <?php endif;?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="ficha-spcultura">
                    <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                    <?php $this->part('singles/space-servico', ['entity' => $entity]); ?>
                <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => false]); ?>
            </div>

            <?php $this->applyTemplateHook('tab-about-extra-info','before'); ?>
            <?php $this->part('singles/space-extra-info', ['entity' => $entity]) ?>
            <?php $this->applyTemplateHook('tab-about-extra-info','after'); ?>

            <?php $this->part('video-gallery.php', ['entity' => $entity]); ?>

            <?php $this->part('gallery.php', ['entity' => $entity]); ?>

            <?php $this->applyTemplateHook('tab-about','end'); ?>
        </div>
        <!-- #sobre -->
        <!-- #permissao -->
        <?php $this->part('singles/permissions') ?>
        <!-- #permissao -->
        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', ['entity' => $entity, 'owner' => $entity->owner]) ?>

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar space">
    <?php $this->applyTemplateHook('sidebar-left','begin'); ?>

    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>

    <?php $this->part('singles/space-public', ['entity' => $entity]) ?>


    <?php $this->applyTemplateHook('sidebar-left','begin'); ?>
</div>
<div class="sidebar space sidebar-right">
    <?php $this->applyTemplateHook('sidebar-right','begin'); ?>

    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar");?> <?php $this->dict('entities: the space') ?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>

    <?php $this->part('related-agents', ['entity' => $entity]); ?>

    <?php $this->part('singles/space-children', ['entity' => $entity]); ?>

    <?php $this->part('downloads', ['entity' => $entity]); ?>

    <?php $this->part('link-list', ['entity' => $entity]); ?>

    <?php $this->part('history.php', array('entity' => $entity)); ?>
    
    <?php $this->applyTemplateHook('sidebar-right','end'); ?>
</div>
