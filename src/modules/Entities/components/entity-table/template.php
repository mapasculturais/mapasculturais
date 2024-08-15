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
    
    <mc-entities :select="select" :raw-processor="rawProcessor" :type="apiController" :query="query" :order="entitiesOrder" :watch-debounce="watchDebounce" :limit="limit" :endpoint="endpoint" @fetch="resize()" watch-query>

        <template #header="{entities, filters}">
            <div v-if="!hideHeader" class="entity-table__header">
                <!-- título - opcional -->
                <div v-if="hasSlot('title')" class="entity-table__title">
                    <slot name="title"></slot>
                </div>

                <!-- ações - opcional -->
                <mc-collapse v-if="hasSlot('actions') || !hideActions">
                    <template #header>
                        <slot name="actions" :entities="entities" :filters="filters"></slot>
                    </template>

                    <template v-if="hasSlot('advanced-actions')" #content>
                        <slot name="advanced-actions" :entities="entities" :filters="filters"></slot>
                    </template>
                </mc-collapse>

                <!-- filtros - pré-definido -->
                <mc-collapse v-if="!hideFilters">
                    <template #header>
                        <div class="entity-table__main-filter">
                            <div class="entity-table__search-field">
                                <slot name="searchKeyword" :query="query">
                                    <textarea ref="search" v-model="this.query['@keyword']" rows="1" placeholder="<?= i::__('Pesquisa por palavra-chave separados por ;') ?>" class="entity-table__search-input"></textarea>
                                    
                                    <button @click="keyword(entities)" class="entity-table__search-button">
                                        <mc-icon name="search"></mc-icon>
                                    </button>
                                </slot>
                            </div>
                            
                            <slot name="filters" :entities="entities" :filters="filters">
                            </slot>                            
                        </div>
                    </template>

                    <template v-if="advancedFilters.length > 0 || hasSlot('advanced-filters')"  #content>
                        <div class="entity-table__advanced-filters custom-scrollbar">
                            <slot name="advanced-filters" :entities="entities" :filters="filters">

                                <div class="grid-12">
                                    <div v-for="(filter, slug) in advancedFilters" class="field col-3">
                                        <label>{{filter.label}}</label>
    
                                        <div class="field__group custom-scrollbar">
                                            <label v-for="option in filter.options" :key="option" class="field__checkbox">
                                                <input type="checkbox" :checked="advancedFilterChecked(slug, optionValue(option))" @change="toggleAdvancedFilter(slug, optionValue(option))"> {{optionLabel(option)}}
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
            <div v-if="!hideSort" class="entity-table__info">
                <span v-if="entities.length === entities.metadata.count">
                    <?= i::__('Exibindo todos os {{entities.metadata.count}} registros encontrados ordenados por ') ?>
                </span>
                <span v-else>    
                    <?= i::__('Exibindo {{entities.length}} dos {{entities.metadata.count}} registros encontrados ordenados por ') ?>
                </span>
                <mc-select small v-model:default-value="entitiesOrder" :options="sortOptions" placeholder="<?= i::__('Selecione a ordem de listagem') ?>"></mc-select>
            </div>

            <div v-if="hideSort" class="entity-table__info">
                <span v-if="entities.length === entities.metadata.count">
                    <?= i::__('Exibindo todos os {{entities.metadata.count}} registros encontrados') ?>
                </span>
                <span v-else>    
                    <?= i::__('Exibindo {{entities.length}} dos {{entities.metadata.count}} registros encontrados') ?>
                </span>
            </div>
        </template>


        <template #default="{entities, refresh}">
              <!-- SÓ O HEADER -->
            <div class="entity-table__table-header-wrapper" v-show="ready" ref="headerWrapper" @scroll="scroll($event)">
                <div class="entity-table__table-header">
                    <div v-if="showIndex" class="entity-table__index sticky entity-table__show-columns" :style="{width: columnsWidth['-index'] ?? '', minHeight: headerHeight + 'px'}">
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
                                <a href="#" v-tooltip="'<?= i::__("Configurar colunas") ?>'" data-toggle="tooltip" @click.prevent="popover.toggle()">
                                    <mc-icon name="columns-edit"></mc-icon>
                                </a>
                            </template>
                        </mc-popover>
                    </div>
                    <template v-for="header in columns">
                        <div v-if="header.visible" class="table-header-cell" :class="{sticky: header.sticky || header.stickyRight}" :style="headerStyle(header, true)">{{header.text}}</div>
                    </template>
                </div>
            </div>

            <!-- <-- DIV PARA A TABELA COMPLETA + O SCROLL --> 
            <div>
                <div class="entity-table__table-content-wrapper" @scroll="scroll($event)" ref="contentWrapper">
                    <table class="entity-table__table" :style="{marginTop: (-headerHeight + 20) + 'px'}" ref="contentTable">
                        <thead class="table-thead" ref="headerTable">
                            <tr>
                                <th v-if="showIndex" ref="column--index" class="entity-table__index sticky table-line">&nbsp;</th>
                                <template v-for="header in columns">
                                    <th  v-if="header.visible" :ref="'column-' + header.slug" :class="{sticky: header.sticky || header.stickyRight}" :style="headerStyle(header)">{{header.text}}</th>
                                </template>
                            </tr>
                        </thead>
                        <tbody >
                            <tr v-for="(entity, index) in entities" :key="entity.__objectId">
                                <td v-if="showIndex" class="entity-table__index sticky table-line">{{index+1}}</td>
                                <template v-for="header in columns" :key="header.slug">
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
                <div class="entity-table__table-scroll" ref="scrollWrapper" @scroll="scroll($event)">
                    <div :style="{width}">&nbsp;</div>
                </div>
            </div>
        </template>
    </mc-entities>
</div>