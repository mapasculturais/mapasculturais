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

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content seal">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
        
        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
        
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--seal.png']); ?>
            
            <?php $this->part('singles/type', ['entity' => $entity]) ?>
            
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
        <li><a href="#agenda">Agenda</a></li>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>
    
    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

                    <?php if($this->isEditable()): ?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Nome:</span> <span class="js-editable" data-edit="nomeCompleto" data-original-title="Nome Completo ou Razão Social" data-emptytext="Insira seu nome completo ou razão social"><?php echo $entity->nomeCompleto; ?></span></p>
                        </p>
                    <?php endif; ?>

                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?>

            </div>
            <!--.ficha-spcultura-->

            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descrição</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Agente" data-emptytext="Insira uma descrição do agente" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->
            <!-- Video Gallery BEGIN -->
                <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
            <!-- Video Gallery END -->
            <!-- Image Gallery BEGIN -->
                <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
            <!-- Image Gallery END -->
        </div>
        <!-- #sobre -->
        <div id="agenda" class="aba-content">
            <?php $this->part('agenda', array('entity' => $entity)); ?>
        </div>
        <!-- #agenda -->
        
        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?>
</article>
<div class="sidebar-left sidebar agent">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar seal sidebar-right">

    <!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
