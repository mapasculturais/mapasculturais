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
    <mc-entities :select="select" :type="type" :query="query" :limit="limit">
        <template #header="{entities, filters}">
            <div class="opportunity-registration-table__filter">
                <slot name="actions-table" :entities="entities" :filters="filters"></slot>
                <div class="opportunity-registration-table__search-key">
                    <input v-model="searchText" @keyup="keyword(entities)" type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
                    <button @click="keyword(entities)" class="opportunity-registration-table__search-button">
                        <mc-icon name="search"></mc-icon>
                    </button>
                </div>
                <slot name="filters-table" :entities="entities" :filters="filters"></slot>
                <div class="field opportunity-registration-table__select-tag">

                    <mc-multiselect #default="{setFilter, popover}" @selected="addInColumns" @removed="removeFromColumns" :model="selectedColumns" :items="optionalHeaders" hide-filter hide-button>
                        <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Colunas habilitadas na tabela') ?>">
                    </mc-multiselect>

                    <mc-tag-list editable class="opportunity-registration-table__taglists" classes="opportunity__background" :tags="selectedColumns" @remove="removeFromColumns"></mc-tag-list>
                </div>
            </div>
        </template>

        <template #default="{entities}">
            <div>
                <table>
                    <thead>
                        <tr>
                            <template v-for="header in activeHeaders">
                                <th>{{header.text}}</th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in entities">
                            <template v-for="header in activeHeaders">
                                <td>
                                    <slot :name="parseSlug(header)" v-bind="item">
                                        {{getEntityData(item, header.value)}}
                                    </slot>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </mc-entities>
</div>