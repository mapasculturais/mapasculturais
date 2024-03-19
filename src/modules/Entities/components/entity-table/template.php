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
    
    <mc-entities :select="select" :type="apiController" :query="query" :order="entitiesOrder" :watch-debounce="watchDebounce" :limit="limit" :endpoint="endpoint" @fetch="resize()" watch-query>

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
                                <textarea ref="search" v-model="this.query['@keyword']" rows="1" placeholder="<?= i::__('Pesquisa por palavra-chave separados por ;') ?>" class="entity-table__search-input"></textarea>
                                
                                <button @click="keyword(entities)" class="entity-table__search-button">
                                    <mc-icon name="search"></mc-icon>
                                </button>
                            </div>
                            
                            <slot name="filters" :entities="entities" :filters="filters">
                            </slot>                            
                        </div>
                    </template>

                    <template #content>
                        <div class="entity-table__advanced-filters">
                            <slot name="advanced-filters" :entities="entities" :filters="filters">

                                <div class="grid-12">
                                    <div v-for="(filter, slug) in advancedFilters" class="field col-3">
                                        <label>{{filter.label}}</label>
    
                                        <div class="field__group">
                                            <label v-for="option in filter.options" :key="option" class="field__checkbox">
                                                <input type="checkbox" :checked="advancedFilterChecked(slug, option)" @change="toggleAdvancedFilter(slug, option)"> {{option}}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </slot>
                        </div>
                    </template>
                </mc-collapse>

                <div class="entity-table__tags">
                    <div class="mc-tag-list">
                        <ul class="mc-tag-list__tagList">
                            <li v-for="filter in appliedFilters" class="mc-tag-list__tag mc-tag-list__tag--editable opportunity__background opportunity__color">
                                <span>{{ filter.label }}</span>
                                <mc-icon name="delete" @click="removeFilter(filter, entities)" is-link></mc-icon>
                            </li>
                            <li v-if="appliedFilters.length > 0">
                                <button class="button button--sm button--text-danger button--icon" @click="clearFilters(entities)"> <?= i::__("Limpar filtros") ?> <mc-icon name="trash"></mc-icon> </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="entity-table__info">
                <?= i::__('Exibindo {{entities.length}} dos {{entities.metadata.count}} registros encontrados ordenados por ') ?>

                <mc-select small v-model:default-value="entitiesOrder" placeholder="<?= i::__('Selecione a ordem de listagem') ?>">
                    <option v-for="option in sortOptions" :value="option.order">{{option.label}}</option>
                </mc-select>
            </div>
        </template>


        <template #default="{entities, refresh}">
              <!-- SÓ O HEADER -->
            <div v-show="ready" style="overflow-x: hidden; position: sticky; top:0; background-color: white; z-index:100;" ref="headerWrapper" @scroll="scroll($event)">
                <div class="entity-table__table" style="width:50000px; padding-bottom: 0; position: sticky; top:0; ">
                    <div v-if="showIndex" class="entity-table__index sticky" style="display:inline-block; padding: 10px; left:0" :style="{width: columnsWidth['-index'] ?? '', minHeight: headerHeight + 'px'}">
                        <mc-popover>
                            <div class="entity-table__popover">
                                <label class="field__title bold"><?= i::__('Selecione as colunas que deseja exibir:')?></label>

                                <label class="field__checkbox">
                                    <input ref="allHeaders" type="checkbox" @click="showAllHeaders()" :checked="allHeadersActive"> <?= i::__('Todas as colunas') ?>
                                </label>

                                <label v-for="column in columns" class="field__checkbox">
                                    <input v-if="column.text" :checked="column.visible" type="checkbox" :value="column.slug" @click="toggleHeaders($event)"> {{column.text}} 
                                </label>
                            </div>

                            <template #button="popover">
                                <a href="#" title="<?= i::__("definir colunas habilitadas") ?> "  @click.prevent="popover.toggle()"  style="padding:1em;"><mc-icon name="columns"></mc-icon></a>
                            </template>
                        </mc-popover>
                    </div>
                    <template v-for="header in columns">
                        <div v-if="header.visible" class="table-header-cell" :class="{sticky: header.sticky || header.stickyRight}" :style="headerStyle(header)">{{header.text}}</div>
                    </template>
                </div>
            </div>

            <!-- <-- DIV PARA A TABELA COMPLETA + O SCROLL --> 
            <div>
                <div style="overflow-x: auto; scrollbar-width: none;" @scroll="scroll($event)" ref="contentWrapper">
                    <table class="entity-table__table" :style="{marginTop: (-headerHeight + 20) + 'px'}" style="min-width:100%" ref="contentTable">
                        <thead ref="headerTable" style="color:transparent">
                            <tr>
                                <th v-if="showIndex" ref="column--index" class="entity-table__index sticky" style="left:0; width:60px">&nbsp;</th>
                                <template v-for="header in columns">
                                    <th  v-if="header.visible" :ref="'column-' + header.slug" :class="{sticky: header.sticky || header.stickyRight}" :style="headerStyle(header)">{{header.text}}</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody >
                            <tr v-for="(entity, index) in entities" :key="entity.__objectId">
                                <td v-if="showIndex" class="entity-table__index sticky" style="left:0; width:60px">{{index+1}}</td>
                                <template v-for="header in columns">
                                    <td v-if="header.visible" :class="{sticky: header.sticky || header.stickyRight}" :style="headerStyle(header)">
                                        <slot :name="header.slug" :entity="entity" :refresh="refresh">
                                            {{getEntityData(entity, header.value)}}
                                        </slot>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>          
                <div ref="scrollWrapper" @scroll="scroll($event)" style="overflow-x: auto; position: sticky; bottom:0">
                    <div :style="{width}">&nbsp;</div>
                </div>
            </div>
        </template>
    </mc-entities>
</div>