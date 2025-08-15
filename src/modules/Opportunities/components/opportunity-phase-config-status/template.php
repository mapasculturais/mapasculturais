<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-status
');

?>
<div class="opportunity-phase-config-status col-12 grid-12">
    <h4 class="bold col-12"><?= i::__("Configuração de status") ?></h4>

    <div v-for="(status, index) in statuses" :key="index" class="col-12 opportunity-phase-config-status__status">
        <div class="opportunity-phase-config-status__status-line">
            <input type="checkbox" v-model="status.enabled" @change="updateStatus(status)" />

            <div v-if="status.enabled && status.isEditing" class="field">
                <input @change="toggleEdit(status); updateLabel(status);" type="text" v-model="status.label" />
            </div>

            <mc-status v-else :status-name="status || ''" :class="{ disabled: !status.enabled }"></mc-status>

            <button class="opportunity-phase-config-status__button" v-if="status.enabled" @click="toggleEdit(status)">
                <template v-if="status.isEditing"> <?= i::__('Concluir edição') ?> </template>
                <template v-if="!status.isEditing"> <?= i::__('Editar') ?> </template>
            </button>

            <button v-if="status.label !== status.defaultLabel" class="opportunity-phase-config-status__button" @click="restoreOriginal(status)">
                <?= i::__("Restaurar") ?>
            </button>
        </div>

        <div class="opportunity-phase-config-status__status-default">
            <b><?= i::__("Original") ?>: </b> {{ status.defaultLabel }}
        </div>
    </div>
</div>