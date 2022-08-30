<?php
use MapasCulturais\i;
$this->import('mc-icon');
?>

<!-- Logo -->
<div class="mc-header-logo">
    <button class="mc-header-menu__btn-mobile" @click="toggleMobile()">
        <mc-icon name="menu-mobile"></mc-icon>
    </button>

    <slot name="logo"></slot>
</div>

<!-- Menu principal -->
<ul class="mc-header-menu">   
    <slot name="default"></slot>
</ul>

<ul v-if="openMobile" class="mc-header-menu mobile">   
    <slot name="default"></slot>

    <li> 
        <a href="<?= $app->createUrl('panel', 'index') ?>" class="mc-header-menu--item painel">
            <span class="icon"> <mc-icon name="dashboard"></mc-icon> </span>
            <p class="label"> <?php i::_e('Painel de controle') ?> </p>      
        </a> 
    </li>
</ul>