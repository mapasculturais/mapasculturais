<?php
use MapasCulturais\i;
$this->import('mc-icon');
?>

<!-- Logo -->
<div class="mc-header-logo">
    <a class="mc-header-menu__btn-mobile" href="#main-app" @click="toggleMobile()">
        <mc-icon name="menu-mobile"></mc-icon>
    </a>

    <slot name="logo"></slot>
</div>

<!-- Menu principal -->
<ul class="mc-header-menu">   
    <slot name="default"></slot>
</ul>

<!-- Menu principal mobile -->
<div v-if="openMobile" class="mc-header-menu mobile">
    <div class="close"> 
        <a class="close__btn" href="#main-app" @click="toggleMobile()">
            <mc-icon name="close"></mc-icon> 
        </a>

        <theme-logo title="mapa cultural" subtitle="do Pará" href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
    </div>

    <ul class="mc-header-menu__itens">
        <slot name="default"></slot>

        <li> 
            <a href="<?= $app->createUrl('panel', 'index') ?>" class="mc-header-menu--item painel">
                <span class="icon"> <mc-icon name="dashboard"></mc-icon> </span>
                <p class="label"> <?php i::_e('Painel de controle') ?> </p>      
            </a> 
        </li>
    </ul>
</div>