<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    home-texts
    image-customizer
');
?>

<mc-tab label="<?php i::esc_attr_e('Home') ?>" slug="home">
    <div class="theme-customizer">
        <home-texts></home-texts>
        <image-customizer />
    </div>
</mc-tab>