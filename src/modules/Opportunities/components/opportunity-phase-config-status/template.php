<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

// $this->import('');
?>
<div class="opportunity-phase-config-status grid-12">
    <h4 class="bold col-12">  <?= i::__("Configuração de status") ?></h4>

    <div v-for="status in statuses" :key="status.key" class="col-12">
        <label>
            <input type="checkbox" v-model="status.enabled" @change="updateStatus(status)">
            {{ status.defaultLabel }}
        </label>

        <input type="text" v-model="status.label" :disabled="!status.enabled" @input="updateLabel(status)"/>
    </div>
</div>