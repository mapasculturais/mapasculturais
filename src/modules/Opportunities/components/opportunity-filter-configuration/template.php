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
    <mc-modal :title="titleModal || '<?= i::__('Configuração de filtros') ?>'">
        <div class="grid-12">
            <div class="col-12 field">
                <select v-model="selectedField">
                    <option value="" disabled selected><?php i::_e("Selecione um filtro") ?></option>
                    <option v-if="showField('category')" value="category" :disabled="isFieldExcluded('category')"><?php i::_e("Categoria") ?></option>
                    <option v-if="showField('proponentType')" value="proponentType" :disabled="isFieldExcluded('proponentType')"><?php i::_e("Tipos do proponente") ?></option>
                    <option v-if="showField('range')" value="range" :disabled="isFieldExcluded('range')"><?php i::_e("Faixa/Linha") ?></option>
                </select>
            </div>

            <div v-if="selectedField == 'category'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="category in registrationCategories">
                    <input type="checkbox" :value="category" v-model="tempValue.categories"> {{category}}
                </label>
            </div>

            <div v-if="selectedField == 'proponentType'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="proponentType in registrationProponentTypes">
                    <input type="checkbox" :value="proponentType" v-model="tempValue.proponentTypes"> {{proponentType}}
                </label>
            </div>

            <div v-if="selectedField == 'range'" class="opportunity-registration-filter-configuration__related-input col-12 field">
                <label class="input__label input__checkboxLabel input__multiselect" v-for="range in registrationRanges">
                    <input type="checkbox" :value="range" v-model="tempValue.ranges"> {{range}}
                </label>
            </div>
        </div>

        <template #actions="modal">
            <button class="button button--text button--text-del" @click="cancelChanges(modal)"><?= i::__('Cancelar') ?></button>
            <button class="button button--primary" @click="confirmChanges(modal)"><?= i::__('Confirmar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="opportunity-registration-filter-configuration__add-filter button button--rounded button--sm button--icon button--primary">
                <?= i::__('Adicionar filtro') ?>
                <mc-icon name="add"></mc-icon>
            </button>
        </template>
    </mc-modal>

    <mc-tag-list classes="opportunity__background" :tags="tags" @remove="removeTag" editable></mc-tag-list>
</div>