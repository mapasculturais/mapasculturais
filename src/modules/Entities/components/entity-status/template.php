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
<div v-if="showMessage" class="entity-status">
    <mc-alert type="warning">
        <span v-html="message"></span>
    </mc-alert>
</div>