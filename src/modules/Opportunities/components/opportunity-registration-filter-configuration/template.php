<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
    mc-tag-list
');
?>

<div class="opportunity-registration-filter-configuration">
    <mc-modal :title="titleModal || '<?= i::__('Configuração de filtros de inscrição para avaliadores/comissão') ?>'">
        <div class="grid-12">
            <div class="col-12 field">
                <select v-model="selectedField" @change="handleSelection($event)">
                    <option value="" disabled selected><?php i::_e("Selecione um filtro") ?></option>
                    <option v-if="showField('category')" value="category" :disabled="isFieldExcluded('category')"><?php i::_e("Categoria") ?></option>
                    <option v-if="showField('proponentType')" value="proponentType" :disabled="isFieldExcluded('proponentType')"><?php i::_e("Tipos do proponente") ?></option>
                    <option v-if="showField('range')" value="range" :disabled="isFieldExcluded('range')"><?php i::_e("Faixa/Linha") ?></option>
                    <option v-if="useDistributionField" value="distribution" :disabled="isGlobal"><?php i::_e("Distribuição") ?></option>
                    <option v-if="Object.keys(registrationSelectionFields).length > 0" v-for="(options, title) in registrationSelectionFields" :key="title" :value="title" :disabled="isFieldExcluded(title)">{{ title }}</option>
                </select>
            </div>

            <div v-if="selectedField == 'category'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="category in filteredFields.categories">
                    <input :checked="selectedConfigs.includes(category)" type="checkbox" :value="category" v-model="selectedConfigs"> {{category}}
                </label>
            </div>

            <div v-if="selectedField == 'proponentType'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="proponentType in filteredFields.proponentTypes">
                    <input :checked="selectedConfigs.includes(proponentType)" type="checkbox" :value="proponentType" v-model="selectedConfigs"> {{proponentType}}
                </label>
            </div>

            <div v-if="selectedField == 'range'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="range in filteredFields.ranges">
                    <input :checked="selectedConfigs.includes(range)" type="checkbox" :value="range" v-model="selectedConfigs"> {{range}}
                </label>
            </div>

            <div v-if="selectedField == 'distribution'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <input type="text" placeholder="00-99" maxlength="5" v-model="selectedDistribution" />
            </div>

            <div v-if="registrationSelectionFields[selectedField] && registrationSelectionFields[selectedField].length > 0" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="option in registrationSelectionFields[selectedField]">
                    <input :checked="selectedConfigs.includes[option]" type="checkbox" :value="option" v-model="selectedConfigs"> {{option}}
                </label>
            </div>
        </div>

        <template #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close(); this.selectedField = '';"><?= i::__('Cancelar') ?></button>
            <button class="button button--primary" @click="addConfig(modal)"><?= i::__('Confirmar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="opportunity-registration-filter-configuration__add-filter button button--rounded button--sm button--icon button--primary">
                <?= i::__('Adicionar filtro') ?>
                <mc-icon name="add"></mc-icon>
            </button>
        </template>
    </mc-modal>

    <mc-tag-list classes="opportunity__background" :tags="fillTagsList" @remove="removeTag($event)" editable></mc-tag-list>
</div>