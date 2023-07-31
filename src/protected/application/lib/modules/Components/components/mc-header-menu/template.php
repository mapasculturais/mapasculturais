<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>
<!-- Logo -->
<?php $this->applyTemplateHook('mc-header-logo','before'); ?>
<div class="mc-header-logo">
    <?php $this->applyTemplateHook('mc-header-logo','begin'); ?>
    <a class="mc-header-menu__btn-mobile" href="#main-app" @click="toggleMobile()">
        <mc-icon name="menu-mobile"></mc-icon>
    </a>
    <slot name="logo"></slot>
    <?php $this->applyTemplateHook('mc-header-logo','end'); ?>
</div>
<?php $this->applyTemplateHook('mc-header-logo','after'); ?>

<!-- Menu principal -->
<?php $this->applyTemplateHook('mc-entity-menu','before'); ?>
<ul class="mc-header-menu">   
    <?php $this->applyTemplateHook('mc-entity-menu','begin'); ?>
    <slot name="default"></slot>
    <?php $this->applyTemplateHook('mc-entity-menu','end'); ?>
</ul>
<?php $this->applyTemplateHook('mc-entity-menu','after'); ?>

<!-- Menu principal mobile -->
<div v-if="openMobile" class="mc-header-menu mobile">
    <div class="close"> 
        <a class="close__btn" href="#main-app" @click="toggleMobile()">
            <mc-icon name="close"></mc-icon> 
        </a>
        <theme-logo href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
    </div>
    <?php $this->applyTemplateHook('mc-entity-menu-mobile','before'); ?>
    <ul class="mc-header-menu__itens">
        <?php $this->applyTemplateHook('mc-entity-menu-mobile','begin'); ?>
        <slot name="default"></slot>
        <li> 
            <a href="<?= $app->createUrl('panel', 'index') ?>" class="mc-header-menu--item painel">
                <span class="icon"> <mc-icon name="dashboard"></mc-icon> </span>
                <p class="label"> <?php i::_e('Painel de controle') ?> </p>      
            </a> 
        </li>
        <?php $this->applyTemplateHook('mc-entity-menu-mobile','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('mc-entity-menu-mobile','after'); ?>
</div>