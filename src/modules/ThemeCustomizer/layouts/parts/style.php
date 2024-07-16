<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    colors-customizer
    logo-customizer
');
?>

<mc-tab label="<?php i::esc_attr_e('Cores e estilos') ?>" slug="style">
    <div class="theme-customizer">
        <mc-alert small type="warning">
            <b><?= i::__('Atenção!') ?></b> <?= i::__('As modificações só serão aplicadas no sistema após atualizar a página.') ?>
        </mc-alert>
    
        <colors-customizer></colors-customizer>
        <logo-customizer></logo-customizer>
    </div>
</mc-tab>