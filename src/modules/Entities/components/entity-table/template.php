<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-entities
    mc-link
    mc-multiselect
    mc-tag-list
');
?>
<div class="entity-table">
    <mc-entities :select="select" :type="type" :query="query" :limit="limit" :endpoint="endpoint">

        <template #header="{entities, filters}">
            <div class="entity-table__filters">

                <div v-if="hasSlot('actions-primary')" class="entity-table__actions-primary">
                    <slot name="actions-primary" :entities="entities" :filters="filters"></slot>
                </div>

                <div class="entity-table__mid">
                    <div class="entity-table__search">
                        <input v-model="searchText" @keyup="keyword(entities)" type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="entity-table__search-field" />
                        <button @click="keyword(entities)" class="entity-table__search-button">
                            <mc-icon name="search"></mc-icon>
                        </button>
                    </div>

                    <div v-if="hasSlot('actions-secondary')" class="entity-table__actions-secondary">
                        <slot name="actions-secondary" :entities="entities" :filters="filters"></slot>
                    </div>
                </div>
                
                <div v-if="hasSlot('table-filters')" class="entity-table__filter">
                    <slot name="table-filters" :entities="entities" :filters="filters"></slot>
                </div>

                <div class="field entity-table__select-tag">
                    <mc-multiselect #default="{setFilter, popover}" @selected="addInColumns($event)" @removed="removeFromColumns($event)" :model="selectedColumns" :items="items" hide-filter hide-button>
                        <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Colunas habilitadas na tabela') ?>">
                    </mc-multiselect>
                    <mc-tag-list editable class="entity-table__taglists" classes="opportunity__background" :tags="selectedColumns" @remove="removeFromColumns"></mc-tag-list>
                </div>

            </div>
        </template>

        <template #default="{entities}">
            <table class="entity-table__table">
                <thead>
                    <tr>
                        <template v-for="header in columns">
                            <th v-if="header.visible">{{header.text}}</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entity in entities">
                        <template v-for="header in columns">
                            <td v-if="header.visible">
                                <slot :name="header.slug" v-bind="entity">
                                    {{getEntityData(entity, header.value)}}
                                </slot>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </template>

    </mc-entities>
</div>