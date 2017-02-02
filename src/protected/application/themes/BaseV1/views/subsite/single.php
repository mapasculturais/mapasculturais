<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

$this->includeAngularEntityAssets($entity);

$this->includeMapAssets();

$this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));

$this->enqueueScript('app', 'subsite-map', 'js/single-subsite.js', ['map']);
$this->localizeScript('singleSubsite', [
            'examples' =>  \MapasCulturais\i::__('exemplos:'),
        ]);

?>

<style>
    section.filter-section {
        margin-bottom: 2em;
    }
    
    section.filter-section .help {
        color:#666;
        font-style: italic;
    }

    section.filter-section>p { 
        font-size:.9em; 
        margin-bottom:.9em;
    }

    section.filter-section header {
        border-bottom:1px solid #bbb;
        margin-bottom:.5em;
        font-size: 1em;
        text-transform:uppercase;
        font-weight:bold;
    }
    
    section.filter-section header .show-all { 
        font-size: .9em;
        font-weight: initial;
        text-transform: lowercase;
        float: right;
        
    }
    
    .botoes {
        position: absolute;
        top: -12px;
        right: -6px;
        a {
            background-color: #fff;
            border-radius: 100%;
            &:before {
                line-height: 180%;
            }
        }
    }
    .img-seal {
        max-height: 70px;
        max-width: 70px;
    }
</style>

<article class="main-content subsite-container">

    <?php $this->part('singles/subsite-header', ['entity' => $entity]) ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <br>

    <div class="subsite-infos">
        <?php $this->part('singles/subsite-tabs', ['entity' => $entity]) ?>

        <div class="tabs-content">
            <?php $this->applyTemplateHook('tabs-content','begin'); ?>

            <?php $this->part('singles/subsite-filters', ['entity' => $entity]) ?>
            <?php $this->part('singles/subsite-texts', ['entity' => $entity]) ?>
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
            <p class="alert info"><?php \MapasCulturais\i::_e('Parar configurar os administradores você deve primeiro salvar o subsite.'); ?><span class="close"></span></p>
        </div>
    <?php else: ?>
        <!-- Related Profile Agents BEGIN -->
        <?php $this->part('related-profiles-agents.php', array('entity' => $entity)); ?>
        <!-- Related Profile Agents END -->
    <?php endif; ?>

    <!-- Downloads BEGIN -->
    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->
</div>
