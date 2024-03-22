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
            <li> <?= i::__('centralize o mapa no centro do ceu município / estado') ?> </li>
            <li> <?= i::__('utilizando os botões de zoom ( + - ), deixe o mapas exibindo o município/estado inteiro e clique no botão "definir zoom padrão"') ?> </li>
            <li> <?= i::__('utilizando os botões de zoom ( + - ) aproxime o zoom até onde der pra ver o maior nível de detalhes desejado (por exemplo ler os nomes das ruas) e clique no botão "definir zoom máximo"') ?> </li>
            <li> <?= i::__('utilizando os botões de zoom ( + - ) afaste o zoom até onde der de ver a região em torno do município / estado e clique no botão "definir zoom mínimo"') ?> </li>
        </ul>

        <div class="subsite-config-map__map" style="width: 100%;">
            <l-map ref="map" :zoom="5" use-global-leaflet>
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