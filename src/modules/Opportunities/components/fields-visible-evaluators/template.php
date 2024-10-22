<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
    mc-select
    mc-icon
');
?>
<div class="fields-visible-evaluators">

    <div class="fields-visible-evaluators__header">
        <h3 class="title"><?= i::__('Configurar campos visíveis para os avaliadores') ?></h3>
        <p class="subtitle"><?= i::__('Defina quais campos serão habilitados para avaliação.') ?></p>
    </div>

    <mc-modal title="<?= i::esc_attr__('Configurar campos visíveis para os avaliadores') ?>" classes="modalEmbedTools">
        <template #default="modal">
            <div class="fields-visible-evaluators__search-wrapper field--horizontal">
                <label class="semibold"><?= i::__('Filtrar campo') ?></label>
                <div class="fields-visible-evaluators__search field">
                    <input type="text" v-model="searchQuery" @input="searchField()">
                    <small><?= i::__('Pesquise pelo título ou pelo ID') ?></small>
                </div>
            </div>
            <div class="fields-visible-evaluators__content">
                <div class="fields-visible-evaluators__fields field">
                    <div class="fields-visible-evaluators__field__select-all field">
                        <input type="checkbox" v-model="selectAll" @change="toggleSelectAll()">
                        <label><?= i::__('Selecionar todos os campos') ?></label>
                    </div>
                    <div v-for="field in fieldsResult()" :class="['fields-visible-evaluators__field' , 'field', {'disabled':field.disabled}]">
                        <label>
                            <mc-icon :name="fieldType(field)"></mc-icon>
                            <input type="checkbox" :disabled="field.disabled" v-model="avaliableEvaluationFields[field.fieldName || field.groupName]" @change="toggleSelect(field.fieldName || field.groupName)" />
                            <span v-if="field.id">#{{field.id}}</span>
                            <div class="fields-visible-evaluators__field__title">
                                <span>{{field.title}}</span>
                                <small v-if="field.titleDisabled">{{field.titleDisabled}}</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <template #button="modal">
            <button class="button button--bg button--primarylight fields-visible-evaluators__button" @click="modal.open"><?= i::__('Abrir lista de campos') ?></button>
        </template>
    </mc-modal>

</div>