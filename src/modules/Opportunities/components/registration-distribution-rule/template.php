<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
    mc-tag-list
    mc-datepicker
');
?>

<div class="opportunity-registration-filter-configuration">
    <mc-modal :title="titleModal || '<?= i::__('Segmentação de inscrições') ?>'">
        <div class="grid-12">
            <div class="col-12 field">
                <select v-model="selectedField" @change="handleSelection($event)">
                    <option value="" disabled selected><?php i::_e("Selecione um filtro") ?></option>
                    <option v-if="isFilterAvailable('categories')" value="categories" :disabled="isFilterDisabled('categories')"><?php i::_e("Categoria") ?></option>
                    <option v-if="isFilterAvailable('proponentTypes')" value="proponentTypes" :disabled="isFilterDisabled('proponentTypes')"><?php i::_e("Tipos de proponente") ?></option>
                    <option v-if="isFilterAvailable('ranges')" value="ranges" :disabled="isFilterDisabled('ranges')"><?php i::_e("Faixa/Linha") ?></option>
                    <option v-if="isFilterAvailable('distribution')" value="distribution" :disabled="isFilterDisabled('distribution')"><?php i::_e("Distribuição") ?></option>
                    <option v-if="isFilterAvailable('sentTimestamp')" value="sentTimestamp" :disabled="isFilterDisabled('sentTimestamp')"><?php i::_e("Data de envio da inscrição") ?></option>
                    <option v-if="isFilterAvailable('fields')" v-for="(field, fieldId) in selectionFields" :key="fieldId" :value="'field:' + fieldId" :disabled="isFilterDisabled('fields') || !isFieldAllowedByParent(fieldId)">{{ field.title }}</option>
                </select>
            </div>

            <div v-if="selectedField === 'categories'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="category in filteredCategories" :key="category">
                    <input :checked="selectedConfigs.includes(category)" type="checkbox" :value="category" v-model="selectedConfigs"> {{ category }}
                </label>
            </div>

            <div v-if="selectedField === 'proponentTypes'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="proponentType in filteredProponentTypes" :key="proponentType">
                    <input :checked="selectedConfigs.includes(proponentType)" type="checkbox" :value="proponentType" v-model="selectedConfigs"> {{ proponentType }}
                </label>
            </div>

            <div v-if="selectedField === 'ranges'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="range in filteredRanges" :key="range">
                    <input :checked="selectedConfigs.includes(range)" type="checkbox" :value="range" v-model="selectedConfigs"> {{ range }}
                </label>
            </div>

            <div v-if="selectedField === 'sentTimestamp'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <div>
                    <?= i::__('de') ?> <mc-datepicker v-model:modelValue="selectedConfigs.from" field-type="date"></mc-datepicker>
                    <div v-if="sentTimestampErrors.from" class="opportunity-registration-filter-configuration__error">{{ sentTimestampErrors.from }}</div>
                </div>
                <div>
                    <?= i::__('até') ?> <mc-datepicker v-model:modelValue="selectedConfigs.to" field-type="date"></mc-datepicker>
                    <div v-if="sentTimestampErrors.to" class="opportunity-registration-filter-configuration__error">{{ sentTimestampErrors.to }}</div>
                </div>
            </div>

            <div v-if="selectedField === 'distribution'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <input type="text" :placeholder="text('placeholderDistribution')" maxlength="5" v-model="selectedDistribution" />
            </div>

            <div v-if="selectedFieldType === 'field' && selectedFieldId && selectionFields[selectedFieldId]?.fieldOptions?.length" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="option in selectionFields[selectedFieldId].fieldOptions" :key="option">
                    <input :checked="selectedConfigs.includes(option)" type="checkbox" :value="option" v-model="selectedConfigs"> {{ option }}
                </label>
            </div>
        </div>

        <template #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close(); selectedField = '';"><?= i::__('Cancelar') ?></button>
            <button class="button button--primary" :disabled="!canConfirm" @click="addConfig(modal)"><?= i::__('Confirmar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="opportunity-registration-filter-configuration__add-filter button button--rounded button--sm button--icon button--primary">
                <?= i::__('Adicionar filtro') ?>
                <mc-icon name="add"></mc-icon>
            </button>
        </template>
    </mc-modal>
    <mc-tag-list classes="opportunity__background" :tags="tagsList" :labels="tagsLabels" @remove="removeTag($event)" editable></mc-tag-list>
</div>
