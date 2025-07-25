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
<div class="opportunity-phase-config-status grid-12">
    <h4 class="bold col-12"><?= i::__("Configuração de status") ?></h4>

    <div v-for="status in statuses" :key="status.key" class="col-12">
        <div class="status-line">
            <input type="checkbox" v-model="status.enabled" @change="updateStatus(status)">

            <input v-if="status.enabled && status.isEditing" type="text" v-model="status.label" @input="updateLabel(status)" />

            <div v-else class="mc-status-like" :class="{ disabled: !status.enabled }">
                <mc-icon name="dot"></mc-icon>
                <span>{{ status.label }}</span>
            </div>

            <button v-if="status.enabled" @click="toggleEdit(status)">
                {{ status.isEditing ? 'Cancelar edição' : 'Editar' }}
            </button>

            <button @click="toggleShowOriginal(status)">
                {{ status.showOriginal ? 'Ocultar original' : 'Mostrar original' }}
            </button>
        </div>

        <div v-if="status.showOriginal" class="original-label">
            <?= i::__("Original") ?> {{ status.defaultLabel }}
        </div>
    </div>
</div>