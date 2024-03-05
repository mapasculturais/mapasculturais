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
    mc-collapse
');
?>
<div class="entity-table">
    <mc-entities :select="select" :type="type" :query="query" :order="order" :limit="limit" :endpoint="endpoint">

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
                            <div class="entity-table__search">
                                <div class="entity-table__search-title">
                                    <h4 class="bold"><?= i::__('Filtrar:') ?></h4>
                                </div>
                                <div class="entity-table__search-field">
                                    <input v-model="searchText" @input="keyword(entities)" type="text" placeholder="<?= i::__('Pesquise por palavra-chave') ?>" class="entity-table__search-input" />
                                    <button @click="keyword(entities)" class="entity-table__search-button">
                                        <mc-icon name="search"></mc-icon>
                                    </button>
                                </div>
                            </div>
                            
                            <slot name="filters" :entities="entities" :filters="filters"></slot>
                        </div>
                    </template>

                    <template #content>
                        <div class="entity-table__advanced-filters">
                            <div class="field">
                                <label><?= i::__('Exibir colunas')?></label>

                                <div class="field__group">
                                    <label class="field__checkbox">
                                        <input ref="allHeaders" type="checkbox" @click="showAllHeaders()"> <?= i::__('Todas as colunas') ?>
                                    </label>
                                    <label v-for="column in columns" class="field__checkbox">
                                        <input v-if="column.text" :checked="column.visible" type="checkbox" :value="column.slug" @click="toggleHeaders($event)"> {{column.text}} 
                                    </label>
                                </div>
                            </div>

                            <slot name="advanced-filters" :entities="entities" :filters="filters"></slot>
                        </div>
                    </template>
                </mc-collapse>

                <div class="entity-table__clear-filters">
                    <button class="button button--text button--icon" @click="clearFilters(entities)"> <?= i::__("Limpar filtros") ?> <mc-icon name="trash"></mc-icon> </button>
                </div>
            </div>
        </template>
        <template #default="{entities, refresh}">
        <?= i::__('Exibindo {{entities.length}} dos {{entities.metadata.count}} registros encontrados') ?>
            <table class="entity-table__table">
                <thead>
                    <tr>
                        <th v-if="showIndex" class="entity-table__index">&nbsp;</th>
                        <template v-for="header in columns">
                            <th v-if="header.visible">{{header.text}}</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(entity, index) in entities" :key="entity.__objectId">
                        <td v-if="showIndex" class="entity-table__index">{{index+1}}</td>
                        <template v-for="header in columns">
                            <td v-if="header.visible">
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