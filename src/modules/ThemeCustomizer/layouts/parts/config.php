<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    subsite-configurations
');
?>

<mc-tab label="<?php i::esc_attr_e('Configurações') ?>" slug="config">
    <div class="theme-customizer">
        <subsite-configurations :subsite="entity"></subsite-configurations>
    </div>
</mc-tab>