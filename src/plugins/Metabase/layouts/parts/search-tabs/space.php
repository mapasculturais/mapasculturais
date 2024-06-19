<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    search-dashboard
');

?>
<mc-tab icon="indicator" label="<?php i::esc_attr_e('Indicadores') ?>" slug="indicator">
    <div class="search__tabs--indicator">
       <search-dashboard panel-Id="painel-espacos"></search-dashboard>
    </div>
</mc-tab>