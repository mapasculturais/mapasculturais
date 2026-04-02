<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-multiselect
    mc-map
 ')
?>

<div class="oc-georeferencing">
    <div class="geodivisions">
        <div>
            <label>
                <?= i::__('Divisões disponíveis') ?>
            </label>
            <div class="options">
                <mc-multiselect :model="geodivisions" :items="options" @selected="change($event)" @removed="change($event)">
                    <template #default="{toggleMultiselect}">
                        <button class="button button--rounded button--sm button--icon button--primary" @click="toggleMultiselect()">
                            <?php i::_e("Adicionar") ?>
                            <mc-icon name="add"></mc-icon>
                        </button>
                    </template>
                </mc-multiselect>
                <mc-tag-list :tags="geodivisions" classes="opportunity__background" @remove="change($event)" editable></mc-tag-list>
            </div>
        </div>

        <div>
            <label>
                <?= i::__('Unidades federativas utilizadas') ?>
            </label>
            <div class="options">
                <mc-multiselect :model="geoDivisionsFilters" :items="geoDivisionsFiltersList" @selected="changeFilters($event)" @removed="changeFilters($event)">
                    <template #default="{toggleMultiselect}">
                        <button class="button button--rounded button--sm button--icon button--primary" @click="toggleMultiselect()">
                            <?php i::_e("Adicionar") ?>
                            <mc-icon name="add"></mc-icon>
                        </button>
                    </template>
                </mc-multiselect>
                <mc-tag-list :tags="geoDivisionsFilters" classes="opportunity__background" @remove="changeFilters($event)" editable></mc-tag-list>
            </div>
        </div>
    </div>

    <div class="oc-map">
        <label>
            <?= i::__('Definições do mapa') ?>
        </label>
        <div class="location">
            <form @submit="$event.preventDefault(); search();">
                <div class="field">
                    <input type="text" v-model="filter" placeholder="<?= i::__('Pesquisar por Estado ou Município') ?>" />
                </div>

                <mc-loading :condition="isLoading">
                    <template #default="{ entity }">
                        <?= i::__('Pesquisando...') ?>
                    </template>
                </mc-loading>

                <button v-if="!isLoading" class="button button--primary" type="submit" @click="search()"> <?= i::__('Buscar') ?> </button>
            </form>


            <div v-if="filterResult.length > 0" class="filter-result">
                <ul>
                    <template v-for="result in filterResult">
                        <li @click="setLocation(result)">{{result.name}} {{result.addresstype != 'state' ? '- '+result.address.state : ''}}</li>
                    </template>
                </ul>
            </div>
        </div>

        <div class="mc-map">
            <l-map ref="map" :zoom="defaultZomm" :center="[centerMap.latitude, centerMap.longitude]" :maxZoom="maxZoom" :minZoom="minZoom" use-global-leaflet>
                <l-tile-layer :url="tileServer"></l-tile-layer>
            </l-map>
        </div>
        <div class="actions">
            <button class="button button--primary" :class="{'disabled' : maxZoom != 22}" @click="setMaxZoom()">
                <?= i::__('Definir zoom máximo') ?>
            </button>

            <button class="button button--primary" :class="{'disabled' : (minZoom != 0)}" @click="setMinZoom()">
                <?= i::__('Definir zoom mínimo') ?>
            </button>
            <button class="button button--text button--text-del" @click="resetZoom()">
                <?= i::__('Voltar as configurações padrões') ?>
            </button>
        </div>
    </div>
</div>