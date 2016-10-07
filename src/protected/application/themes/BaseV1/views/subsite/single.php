<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

$this->includeAngularEntityAssets($entity);

$this->includeMapAssets();

$this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));

$editEntity = $this->isEditable();

$slag_editbla = $this->controller->action === 'create';

?>
<article class="main-content subsite-container">
    
    <!--.header-image-->
    <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
            <?php $this->applyTemplateHook('header-content','end'); ?>

            <?php if($this->isEditable() || $entity->nome_instalacao): ?>
                <p>
                    <span class="setup-name js-editable <?php echo ($entity->isPropertyRequired($entity,"nome_instalacao") && $editEntity? 'required': '');?>" data-edit="name" data-original-title="Nome da Instalação" data-emptytext="Nome da instalação"><?php echo $entity->name; ?></span>
                </p>
            <?php endif; ?>

            <div>
                <span class="icon"></span><span class="label">Namespace:</span> 
                <span class="js-editable" data-edit="namespace" data-original-title="Namespace do Tema" data-emptytext="Selecione a o namespace do tema a ser utilizado"><?php echo $entity->namespace; ?></span>
            </div>
            
            <div>
                <span class="icon"></span><span class="label">Slug:</span> 
                <span class="<?php if($slag_editbla): ?>js-editable <?php endif; ?>header-field <?php echo ($entity->isPropertyRequired($entity,"slug") && $editEntity? 'required': '');?>" data-edit="slug" data-original-title="Digite um slug" data-emptytext="Slug"><?php echo $entity->slug; ?></span>
            </div>
            
            <div>
                <span class="icon"></span><span class="label">Domínio Principal:</span> 
                <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"url") && $editEntity? 'required': '');?>" data-edit="url" data-original-title="Domínio Principal" data-emptytext="Ex: mapas.cultura.gov.br"><?php echo $entity->url; ?></span>
            </div>
            
    </div>
    <!--.header-content-->
    <?php $this->applyTemplateHook('header-content','after'); ?>

    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <br>

    <div class="subsite-infos">
        <?php $this->part('singles/subsite-tabs', ['entity' => $entity]) ?>

        <div class="tabs-content">
            <?php $this->applyTemplateHook('tabs-content','begin'); ?>

            <?php $this->part('singles/subsite-about', ['entity' => $entity]) ?>
            <?php $this->part('singles/subsite-entities', ['entity' => $entity]) ?>
            <?php $this->part('singles/subsite-images', ['entity' => $entity]) ?>
            <?php $this->part('singles/subsite-map', ['entity' => $entity]) ?>

            <?php $this->applyTemplateHook('tabs-content','end'); ?>
        </div>
        <?php $this->applyTemplateHook('tabs-content','after'); ?>

        <?php $this->part('owner', ['entity' => $entity, 'owner' => $entity->owner]) ?>
    </div>
</article>

<div class="sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para adicionar arquivos para imagens, download ou links, primeiro é preciso salvar o selo.<span class="close"></span></p>
        </div>
    <?php endif; ?>
</div>
