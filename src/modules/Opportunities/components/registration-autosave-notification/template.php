<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
mc-alert
');
?>
<div>
    <mc-alert type="warning">
        <p class="warning"> <?php i::_e('Os dados da sua inscrição serão salvos automaticamente a cada {{resultTime}} segundos.')?></p>
    </mc-alert>
</div>