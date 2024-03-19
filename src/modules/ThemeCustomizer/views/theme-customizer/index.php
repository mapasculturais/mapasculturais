<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    mc-tabs
    logo-customizer
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
            <?= i::__('Area de customização do tema') ?>
        </p>
    </header>
    
    <mc-tabs class="panel-home__tabs">    
        <mc-tab label="<?php i::esc_attr_e('Home') ?>" slug="home">
        </mc-tab>
    
        <mc-tab label="<?php i::esc_attr_e('Cores e estilos') ?>" slug="style">
            <logo-customizer />
        </mc-tab>

        <mc-tab label="<?php i::esc_attr_e('Mapa') ?>" slug="map">
        </mc-tab>
    </mc-tabs>
</div>