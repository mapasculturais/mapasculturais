<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-map
');

?>

<div class="subsite-config-map">
    <h3> <?= i::__('Configurações do mapa') ?> </h3>

    <div class="subsite-config-map__content">
        <ul class="subsite-config-map__instructions">
            <li> <?= i::__('Centralize o mapa no seu município/estado (você pode pesquisar no campo de busca abaixo)') ?> </li>
            <li> <?= i::__('Utilizando os botões de zoom [+] [-], deixe o mapa exibindo o município/estado inteiro e clique no botão "definir zoom padrão"') ?> </li>
            <li> <?= i::__('Utilizando os botões de zoom [+] [-], aproxime o zoom até onde der pra ver o maior nível de detalhes desejado (por exemplo, ler os nomes das ruas) e clique no botão "definir zoom máximo"') ?> </li>
            <li> <?= i::__('Utilizando os botões de zoom [+] [-], afaste o zoom até onde der de ver a região em torno do município/estado e clique no botão "definir zoom mínimo"') ?> </li>
        </ul>
        
        <form class="subsite-config-map__filter" @submit="$event.preventDefault(); search();">
            <div class="field">
                <label><?= i::__('Pesquisar por Estado ou Município') ?></label>
                <input type="text" v-model="filter" />
            </div>

            <button class="button button--primary" type="submit" @click="search()"> <?= i::__('Buscar') ?> </button>
        </form>

        <div v-if="searchResult.length > 0" class="subsite-config-map__filter-results">
            <p class="subsite-config-map__filter-result" v-for="result in searchResult" @click="setLocation(result)"> {{result.name}} {{result.addresstype != 'state' ? '- '+result.address.state : ''}} </p>
        </div>

        <div class="mc-map">
            <l-map ref="map" :zoom="5" :center="centerMap" use-global-leaflet>
                <l-tile-layer :url="tileServer"></l-tile-layer>
            </l-map>
        </div>

        <div class="subsite-config-map__buttons">
            <button class="button button--primary" @click="setDefaultZoom()">
                <?= i::__('Definir zoom padrão') ?>
            </button>

            <button class="button button--primary" @click="setMaxZoom()">
                <?= i::__('Definir zoom máximo') ?>
            </button>

            <button class="button button--primary" @click="setMinZoom()">
                <?= i::__('Definir zoom mínimo') ?>
            </button>
        </div>
    </div>
</div>