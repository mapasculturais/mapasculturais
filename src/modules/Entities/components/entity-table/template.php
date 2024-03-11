<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-collapse
    mc-entities
    mc-icon
    mc-link
    mc-multiselect
    mc-popover
    mc-select
    mc-tag-list
');
?>
<div class="entity-table">
    
    <mc-entities :select="select" :type="apiController" :query="query" :order="entitiesOrder" :limit="limit" :endpoint="endpoint" watch-query>

        <template #header="{entities, filters}">
            <div class="entity-table__header">
                <!-- título - opcional -->
                <div v-if="hasSlot('title')" class="entity-table__title">
                    <slot name="title"></slot>
                </div>

                <!-- ações - opcional -->
                <mc-collapse v-if="hasSlot('actions')">
                    <template #header>
                        <slot name="actions" :entities="entities" :filters="filters"></slot>
                    </template>

                    <template v-if="hasSlot('advanced-actions')" #content>
                        <slot name="advanced-actions" :entities="entities" :filters="filters"></slot>
                    </template>
                </mc-collapse>

                <!-- filtros - pré-definido -->
                <mc-collapse>
                    <template #header>
                        <div class="entity-table__main-filter">
                            <div class="entity-table__search-field">
                                <textarea ref="search" v-model="searchText" @input="keyword(entities)" rows="1" placeholder="<?= i::__('Pesquisa por palavra-chave separados por ;') ?>" class="entity-table__search-input"></textarea>
                                
                                <button @click="keyword(entities)" class="entity-table__search-button">
                                    <mc-icon name="search"></mc-icon>
                                </button>
                            </div>
                            
                            <slot name="filters" :entities="entities" :filters="filters">
                                <!-- <mc-select placeholder="<?= i::__('Selecione o tipo de entidade') ?>">
                                    <option v-for="option in $description.type" :value="option.order">{{option.label}}</option>
                                </mc-select> -->
                            </slot>                            
                        </div>
                    </template>

                    <template #content>
                        <div class="entity-table__advanced-filters">
                            <slot name="advanced-filters" :entities="entities" :filters="filters"></slot>
                        </div>
                    </template>
                </mc-collapse>

                <div class="entity-table__tags">
                    <div class="mc-tag-list">
                        <ul class="mc-tag-list__tagList">
                            <li v-for="filter in appliedFilters" class="mc-tag-list__tag mc-tag-list__tag--editable opportunity__background opportunity__color">
                                <span>{{ filter }}</span>
                                <mc-icon name="delete" @click="removeFilter(filter)" is-link></mc-icon>
                            </li>
                            <li>
                                <button class="button button--sm button--text-danger button--icon" @click="clearFilters(entities)"> <?= i::__("Limpar filtros") ?> <mc-icon name="trash"></mc-icon> </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </template>

        <template #default="{entities, refresh}">
            <div class="entity-table__info">
                <?= i::__('Exibindo {{entities.length}} dos {{entities.metadata.count}} registros encontrados ordenados por ') ?>

                <mc-select small :default-value="entitiesOrder" placeholder="<?= i::__('Selecione a ordem de listagem') ?>" @change-option="entitiesOrder = $event.value">
                    <option v-for="option in sortOptions" :value="option.order">{{option.label}}</option>
                </mc-select>
            </div>

            <table class="entity-table__table">
                <thead>
                    <tr>
                        <th v-if="showIndex" class="entity-table__index">&nbsp;</th>
                        <template v-for="header in visibleColumns">
                            <th v-if="header.visible">{{header.text}}</th>
                        </template>
                        <th class="entity-table__select_columns">
                            <mc-popover>
                                <label><?= i::__('Exibir colunas')?></label>

                                <label class="field__checkbox">
                                    <input ref="allHeaders" type="checkbox" @click="showAllHeaders()"> <?= i::__('Todas as colunas') ?>
                                </label>
                                <label v-for="column in columns" class="field__checkbox">
                                    <input v-if="column.text" :checked="column.visible" type="checkbox" :value="column.slug" @click="toggleHeaders($event)"> {{column.text}} 
                                </label>

                                <template #button="popover">
                                    <a href="#" title="<?= i::__("definir colunas habilitadas") ?> "><mc-icon name="columns" @click.prevent="popover.toggle()" ></mc-icon></a>
                                </template>
                            </mc-popover>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(entity, index) in entities" :key="entity.__objectId">
                        <td v-if="showIndex" class="entity-table__index">{{index+1}}</td>
                        <template v-for="(header, index) in visibleColumns">
                            <td :colspan="index + 1 == visibleColumns.length ? 2 : 1">
                                <slot :name="header.slug" :entity="entity" :refresh="refresh">
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