<?php
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
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <!--.header-image-->
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>

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

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <li class="active"><a href="#sobre"><?php \MapasCulturais\i::_e("Sobre");?></a></li>
        <?php if(!($this->controller->action === 'create')):?>
        <li><a href="#permissao"><?php \MapasCulturais\i::_e("Permissões");?></a></li>
        <?php endif;?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="ficha-spcultura">
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning"><?php \MapasCulturais\i::_e("O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui");?> <?php echo strlen($entity->shortDescription) ?> <?php \MapasCulturais\i::_e("caracteres. Você deve alterar seu texto ou este será cortado ao salvar.");?></div>
                <?php endif; ?>

                <p>
                    <span class="js-editable required" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <?php $this->part('singles/space-servico', ['entity' => $entity]); ?>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

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

    <?php $this->applyTemplateHook('breadcrumb','begin'); ?>

    <?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'spaces','home_title' => 'entities: My Spaces']); ?>

    <?php $this->applyTemplateHook('breadcrumb','end'); ?>

    <?php $this->part('owner', ['entity' => $entity, 'owner' => $entity->owner]) ?>
</article>
<div class="sidebar-left sidebar space">
    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>

    <?php $this->part('singles/space-public', ['entity' => $entity]) ?>

    <?php $this->part('widget-areas', ['entity' => $entity]); ?>

    <?php $this->part('widget-tags', ['entity' => $entity]); ?>

    <?php $this->part('redes-sociais', ['entity' => $entity]); ?>
</div>
<div class="sidebar space sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar");?> <?php $this->dict('entities: the space') ?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <!-- Related Admin Agents BEGIN -->
        <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Admin Agents END -->

    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents', ['entity' => $entity]); ?>
    <!-- Related Agents END -->

    <?php $this->part('singles/space-children', ['entity' => $entity]); ?>

    <?php $this->part('downloads', ['entity' => $entity]); ?>

    <?php $this->part('link-list', ['entity' => $entity]); ?>

</div>
