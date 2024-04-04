<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    mc-tabs
'); 
?>
<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon default"> <mc-icon name="appearance"></mc-icon> </div>
                <h1 class="title__title"> <?= i::__('Aparência') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::__('Área de customização do tema') ?>
        </p>
    </header>
    
    <mc-tabs class="panel-home__tabs">    
        <?php $this->part('home'); ?>
        <?php $this->part('style'); ?>
        <?php $this->part('map'); ?>        
    </mc-tabs>
</div>