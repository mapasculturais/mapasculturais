<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
    mc-select
');
?>
<div class="fields-visible-evaluators">

    <div class="fields-visible-evaluators__header">
        <h3 class="title"><?= i::__('Configurar campos visíveis para os avaliadores') ?></h3>
        <p class="subtitle"><?= i::__('Defina quais campos serão habilitados para avaliação.') ?></p>
    </div>

    <mc-modal title="<?= i::esc_attr__('Configurar campos visíveis para os avaliadores') ?>" classes="modalEmbedTools">
        <template #default="modal">
            <div>
                <label><?= i::__('Filtrar campo') ?></label>
            </div>
            <div>
                <input type="text" v-model="searchQuery"><small><?= i::__('Pesquise pelo título ou pelo ID') ?></small>
            </div>
            <div class="fields-visible-evaluators__content">
                <div>
                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll()">
                    <label><?= i::__('Selecionar todos os campos') ?></label>
                </div>
                <div class="fields-visible-evaluators__fields">
                    <div v-for="field in combinedFields" :class="['fields-visible-evaluators__field' , {'disabled':field.disabled}]">
                        <label>
                            <input type="checkbox" :disabled="field.disabled" v-model="avaliableEvaluationFields[field.fieldName]" @change="toggleSelect(field.fieldName)" /> <span v-if="field.id">#{{field.id}}</span> {{field.title}}
                            <small v-if="field.titleDisabled">{{field.titleDisabled}}</small>
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <template #button="modal">
            <button class="button button--bg button--primarylight" @click="modal.open"><?= i::__('Abrir lista de campos') ?></button>
        </template>
    </mc-modal>

</div>