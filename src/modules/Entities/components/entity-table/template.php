<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-link
    mc-multiselect
    mc-tag-list
');
?>
<div class="entity-table">

    <div class="opportunity-registration-table__filter">
        <div class="opportunity-registration-table__search-key">
            <input type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
            <button @click="search()" class="opportunity-registration-table__search-button">
                <mc-icon name="search"></mc-icon>
            </button>
        </div>
        <div class="opportunity-registration-table__search-fields">
            <h4 class="bold"><?= i::__('Filtrar:') ?></h4>
            <div class="field"><input type="number" /></div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Operador:') ?></span></option>
                </select>
            </div>
            <div class="field"><input type="number"></div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Status de inscrição:') ?></span></option>
                </select>
            </div>
            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Exequibilidade (R$)') ?></span></option>
                </select>
            </div>
        </div>
        <div class="field opportunity-registration-table__select-tag">

           <!-- <select @change="addInColumns($event.target.value)">
                <option value="" disabled selected><span><?= i::__('Colunas habilitadas na tabela:') ?></span></option>
                <option v-for="header in optionalHeaders" :key="header.value" :value="header.value"> {{header.value}}</option>
            </select> -->
            <mc-multiselect #default="{setFilter, popover}" :model="selectedColumns" :items="optionalHeaders" hide-filter hide-button>
                <input @input="addInColumns($event.target.value)"  class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as Colunas') ?>">
            </mc-multiselect>

            <mc-tag-list editable class="opportunity-registration-table__taglists" classes="opportunity__background" :tags="selectedColumns" @remove="removeFromColumns"></mc-tag-list>
        </div>
    </div>
    <!-- <ul>
        <template v-for="header in headers" :key="header.value">
            <li v-if="!header.required">
                <button type="button" @click="toggleColumn(header)">
                    {{header.text}}
                    
                </button>
            </li>
        </template> 
    </ul>  -->
    <!-- {{itemsSelected}}
        {{visibleColumns}} -->
    <!-- v-model:items-selected="itemsSelected" -->
    <EasyDataTable :headers="activeHeaders" table-class-name="entity-table__table" :body-row-class-name="customRowClassName" :items="items" rows-per-page-message="<?= i::esc_attr__('linhas por página') ?>">
        <template v-for="slot in activeSlots" #[slot]="item">
            <slot :name="slot" v-bind="item"></slot>
        </template>
        <!-- <template #item-checkbox="{id}">
                <input type="checkbox" :checked="itemsSelected.includes(item)" @change="toggleSelection(item)">
        </template> -->
        <template #item-open="{id}">
            <mc-link class="button button--primary" :params="[id]" route="registration/single"><?= i::esc_attr__('Conferir inscrição') ?></mc-link>
        </template>

        <template #item-option="{option}">
            <select v-model="option">
                <option v-for="options in option" :value="options">{{options}}</option>
            </select>
        </template>
    </EasyDataTable>
</div>