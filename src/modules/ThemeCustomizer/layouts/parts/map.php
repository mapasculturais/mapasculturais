<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    subsite-config-map
');
?>

<mc-tab label="<?php i::esc_attr_e('Mapa') ?>" slug="map">
    <div class="theme-customizer">
        <subsite-config-map></subsite-config-map>
    </div>
</mc-tab>