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
        <p class="warning" v-if="!registration.opportunity.enableWorkplan"> <?php i::_e('Os dados da sua inscrição serão salvos automaticamente a cada {{resultTime}} segundos.')?></p>
        <p class="warning" v-if="registration.opportunity.enableWorkplan"> <?php i::_e('Os dados da sua inscrição serão salvos automaticamente a cada {{resultTime}} segundos, exceto o plano de metas.')?></p>
    </mc-alert>
</div>