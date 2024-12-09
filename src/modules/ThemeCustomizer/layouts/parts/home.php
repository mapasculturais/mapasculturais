<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    home-customizer
');
?>

<mc-tab label="<?php i::esc_attr_e('Home') ?>" slug="home">
    <div class="theme-customizer">
        <home-customizer></home-customizer>
    </div>
</mc-tab>