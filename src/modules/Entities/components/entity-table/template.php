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
    mc-entities
    mc-tag-list
');
?>
<div class="entity-table">

    <div class="opportunity-registration-table__filter">
        <div class="opportunity-registration-table__search-key">
            <input v-model="searchText" @keyup="search(searchText)" type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
            <button @click="search(searchText)" class="opportunity-registration-table__search-button">
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
                <select >
                    <option value=""><?= i::__('Status da inscrição') ?></option>
                    <option value="-10"><?= i::__('Lixeira') ?></option>
                    <option value="-2"><?= i::__('Arquivado') ?></option>
                    <option value="-9"><?= i::__('Não habilitada') ?></option>
                    <option value="0"><?= i::__('Em rascunho') ?></option>
                    <option value="1"><?= i::__('Enviada') ?></option>
                    <option value="2"><?= i::__('Inválida') ?></option>
                    <option value="3"><?= i::__('Não aprovada') ?></option>
                    <option value="8"><?= i::__('Suplente') ?></option>
                    <option value="10"><?= i::__('Aprovada:') ?></option>
                </select>
            </div>

            <div class="field">
                <select>
                    <option value=""><span><?= i::__('Exequibilidade (R$)') ?></span></option>
                </select>
            </div>
        </div>
        <div class="field opportunity-registration-table__select-tag">

            <mc-multiselect #default="{setFilter, popover}" @selected="addInColumns" @removed="removeFromColumns" :model="selectedColumns" :items="optionalHeaders" hide-filter hide-button>
                <input @input="addInColumns($event.target.value)" class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Colunas habilitadas na tabela') ?>">
            </mc-multiselect>

            <mc-tag-list editable class="opportunity-registration-table__taglists" classes="opportunity__background" :tags="selectedColumns" @remove="removeFromColumns"></mc-tag-list>
        </div>
    </div>
    <mc-entities name="registrationsList" type="registration" endpoint="find" :query="query" :order="query['@order']" select="status,number,category,createTimestamp,sentTimestamp,owner.{name,files.avatar},opportunity.{name,files.avatar,isOpportunityPhase,parent.{name,files.avatar}}">
        
        <template #default="{entities}">
            <div class="registrations__list">
                <span v-for="registration in entities" >
                    {{registration.number}}
                    {{registration.status}}
                </span>
            </div>
        </template>
    </mc-entities>

    <EasyDataTable :headers="activeHeaders" :filter-options="filterOptions" table-class-name="entity-table__table" :body-row-class-name="customRowClassName" :items="activeItems" rows-per-page-message="<?= i::esc_attr__('linhas por página') ?>">
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