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

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

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
        <li class="active"><a href="#sobre">Sobre</a></li>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="ficha-spcultura">
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning">O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui <?php echo strlen($entity->shortDescription) ?> caracteres. Você deve alterar seu texto ou este será cortado ao salvar.</div>
                <?php endif; ?>

                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <?php $this->part('singles/space-servico', ['entity' => $entity]); ?>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => false]); ?>
            </div>

            <?php $this->part('singles/space-extra-info', ['entity' => $entity]) ?>

            <!-- Video Gallery BEGIN -->
            <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
            <!-- Video Gallery END -->

            <!-- Image Gallery BEGIN -->
            <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
            <!-- Image Gallery END -->
            
            <?php $this->applyTemplateHook('tab-about','end'); ?>
        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="sidebar-left sidebar space">

    <!-- Related Seals BEGIN -->
    <?php $this->part('related-seals.php', array('entity'=>$entity)); ?>
    <!-- Related Seals END -->

    <div class="widget">
        <h3>Status</h3>
        <?php if($this->isEditable()): ?>
            <div id="editable-space-status" class="js-editable" data-edit="public" data-type="select" data-value="<?php echo $entity->public ? '1' : '0' ?>"  data-source="[{value: 0, text: 'Publicação restrita - requer autorização para criar eventos'},{value: 1, text:'Publicação livre - qualquer pessoa pode criar eventos'}]">
                <?php if ($entity->public) : ?>
                    <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicação livre</div>
                    <p class="venue-status-definition">Qualquer pessoa pode criar eventos.</p>
                <?php else: ?>
                    <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicação restrita</div>
                    <p class="venue-status-definition">Requer autorização para criar eventos.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($entity->public) : ?>
                <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicação livre</div>
                <p class="venue-status-definition">Qualquer pessoa pode criar eventos.</p>
            <?php else: ?>
                <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicação restrita</div>
                <p class="venue-status-definition">Requer autorização para criar eventos.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar space sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para adicionar arquivos para download ou links, primeiro é preciso salvar o espaço.<span class="close"></span></p>
        </div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="widget">
            <?php if($entity->children && $entity->children->count()): ?>
            <h3>Sub-espaços</h3>
            <ul class="js-slimScroll widget-list">
                <?php foreach($entity->children as $space): ?>
                <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entity->id && $entity->canUser('createChild')): ?>
            <a class="btn btn-default add" href="<?php echo $app->createUrl('space','create', array('parentId' => $entity->id)) ?>">Adicionar sub-espaço</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Downloads BEGIN -->
    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
