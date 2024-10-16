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
    <mc-modal title="<?= i::__('Configuração de filtros de inscrição para avaliadores/comissão') ?>">
        <div class="grid-12">
            <div :class="isSelected ? 'col-6 field' : 'col-12 field'">
                <select v-model="selectedField" @change="handleSelection">
                    <option value="" disabled selected>Selecione um filtro</option>
                    <option v-if="registrationCategories.length > 0" value="category" :disabled="isFieldExcluded('category')"><?php i::_e("Categoria") ?></option>
                    <option v-if="registrationProponentTypes.length > 0" value="proponentType" :disabled="isFieldExcluded('proponentType')"><?php i::_e("Tipos do proponente") ?></option>
                    <option v-if="registrationRanges.length > 0" value="range" :disabled="isFieldExcluded('range')"><?php i::_e("Faixa/Linha") ?></option>
                    <option value="distribution" :disabled="isGlobal"><?php i::_e("Distribuição") ?></option>
                    <option v-if="Object.keys(registrationSelectionFields).length > 0" v-for="(options, title) in registrationSelectionFields" :key="title" :value="title" :disabled="isFieldExcluded(title)">{{ title }}</option>
                </select>
            </div>

            <div v-if="selectedField == 'category'" class="opportunity-registration-filter-configuration__related-input col-6 field">
                <select class="scrollbar" v-model="selectedConfigs" multiple>
                    <option v-for="category in filteredFields.categories" :key="category" :value="category">
                        {{ category }}
                    </option>
                </select>
            </div>

            <div v-if="selectedField == 'proponentType'" class="opportunity-registration-filter-configuration__related-input col-6 field">
                <select class="scrollbar" v-model="selectedConfigs" multiple>
                    <option v-for="proponentType in filteredFields.proponentTypes" :key="proponentType" :value="proponentType">
                        {{ proponentType }}
                    </option>
                </select>
            </div>

            <div v-if="selectedField == 'range'" class="opportunity-registration-filter-configuration__related-input col-6 field">
                <select class="scrollbar" v-model="selectedConfigs" multiple>
                    <option v-for="range in filteredFields.ranges" :key="range" :value="range">
                        {{ range }}
                    </option>
                </select>
            </div>

            <div v-if="selectedField == 'distribution'" class="opportunity-registration-filter-configuration__related-input col-6 field">
                <input type="text" placeholder="00-99" maxlength="5" v-model="selectedDistribution" />
            </div>

            <div v-if="registrationSelectionFields[selectedField] && registrationSelectionFields[selectedField].length > 0" class="opportunity-registration-filter-configuration__related-input col-6 field">
                <select class="scrollbar" v-model="selectedConfigs" multiple>
                    <option v-for="option in registrationSelectionFields[selectedField]" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>

        <template #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('Cancelar') ?></button>
            <button class="button button--primary" @click="addConfig(); modal.close();"><?= i::__('Confirmar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="button button--rounded button--sm button--icon button--primary">
                <?= i::__('Adicionar') ?>
                <mc-icon name="add"></mc-icon>
            </button>
        </template>
    </mc-modal>

    <mc-tag-list classes="opportunity__background" :tags="fillTagsList" @remove="removeTag" editable></mc-tag-list>
</div>
