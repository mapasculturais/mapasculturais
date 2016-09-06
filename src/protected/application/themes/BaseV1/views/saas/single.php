<?php
    $action = preg_replace("#^(\w+/)#", "", $this->template);
    $this->bodyProperties['ng-app'] = "entity.app";
    $this->bodyProperties['ng-controller'] = "EntityController";

    $this->addEntityToJs($entity);

    $this->includeAngularEntityAssets($entity);

    $this->includeMapAssets();

    $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));
?>
<article class="main-content saas-container">
  <header class="main-content-header">
      <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
    </header>

    <!--.header-image-->
    <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
            <?php $this->applyTemplateHook('header-content','end'); ?>

            <?php if($this->isEditable() || $entity->nome_instalacao): ?>
                <p>
                    <span class="setup-name js-editable" data-edit="name" data-original-title="Nome da Instalação" data-emptytext="Nome da instalação"><?php echo $entity->name; ?></span>
                </p>
            <?php endif; ?>

            <br />
            <?php if($this->isEditable() || $entity->slug): ?>
                    <span class="js-editable header-field" data-edit="slug" data-original-title="Digite um slug" data-emptytext="Slug"><?php echo $entity->slug; ?></span>
            <?php endif; ?>

            <?php if($this->isEditable() || $entity->url): ?>
                <p>
                    <span class="js-editable" data-edit="url" data-original-title="URL" data-emptytext="Ex: .mapas.cultura.gov.br"><?php echo $entity->url; ?></span>
                </p>
            <?php endif; ?>
    </div>
    <!--.header-content-->
    <?php $this->applyTemplateHook('header-content','after'); ?>

    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <br>

    <div class="saas-infos">
        <?php $this->part('singles/saas-tabs', ['entity' => $entity]) ?>

        <div class="tabs-content">
            <?php $this->applyTemplateHook('tabs-content','begin'); ?>

            <?php $this->part('singles/saas-about', ['entity' => $entity]) ?>
            <?php $this->part('singles/saas-entities', ['entity' => $entity]) ?>
            <?php $this->part('singles/saas-images', ['entity' => $entity]) ?>
            <?php $this->part('singles/saas-map', ['entity' => $entity]) ?>

            <?php $this->applyTemplateHook('tabs-content','end'); ?>
        </div>
        <?php $this->applyTemplateHook('tabs-content','after'); ?>
    </div>
