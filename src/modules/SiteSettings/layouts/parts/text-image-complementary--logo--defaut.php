<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-icon
    theme-logo
')
?>

<div class="left">
    <!-- Part 1 -->
    <div class="color-item">
        <div class="polygon1" :style="`background-color:${entity.logoColorPart1}`"></div>
        <div class="text" :style="`color:${entity.logoColorPart1}`">{{entity.logoColorPart1}}</div>
        <div class="color-field">
            <input id="colorInput1" type="color" v-model="entity.logoColorPart1">
            <label for="colorInput1">
                <mc-icon name="one-click-edit" :style="`color:${entity.logoColorPart1}`"></mc-icon>
            </label>
        </div>
    </div>

    <!-- Part 2 -->
    <div class="color-item">
        <div class="polygon2" :style="`background-color:${entity.logoColorPart2}`"></div>
        <div class="text" :style="`color:${entity.logoColorPart2}`">{{entity.logoColorPart2}}</div>
        <div class="color-field">
            <input id="colorInput2" type="color" v-model="entity.logoColorPart2">
            <label for="colorInput2">
                <mc-icon name="one-click-edit" :style="`color:${entity.logoColorPart2}`"></mc-icon>
            </label>
        </div>
    </div>

    <!-- Part 3 -->
    <div class="color-item">
        <div class="polygon3" :style="`background-color:${entity.logoColorPart3}`"></div>
        <div class="text" :style="`color:${entity.logoColorPart3}`">{{entity.logoColorPart3}}</div>
        <div class="color-field">
            <input id="colorInput3" type="color" v-model="entity.logoColorPart3">
            <label for="colorInput3">
                <mc-icon name="one-click-edit" :style="`color:${entity.logoColorPart3}`"></mc-icon>
            </label>
        </div>
    </div>

    <!-- Part 4 -->
    <div class="color-item">
        <div class="polygon4" :style="`background-color:${entity.logoColorPart4}`"></div>
        <div class="text" :style="`color:${entity.logoColorPart4}`">{{entity.logoColorPart4}}</div>
        <div class="color-field">
            <input id="colorInput4" type="color" v-model="entity.logoColorPart4">
            <label for="colorInput4">
                <mc-icon name="one-click-edit" :style="`color:${entity.logoColorPart4}`"></mc-icon>
            </label>
        </div>
    </div>
</div>

<div class="right">
    <theme-logo :title="entity.logoDefaultTitle" :subtitle="entity.logoDefaultSubTitle" :style="{'--logo-bg1': entity.logoColorPart1, '--logo-bg2': entity.logoColorPart2, '--logo-bg3': entity.logoColorPart3, '--logo-bg4': entity.logoColorPart4}"></theme-logo>
    <entity-field :entity="entity" prop="logoDefaultTitle" :maxLength="16"></entity-field>
    <entity-field :entity="entity" prop="logoDefaultSubTitle" :maxLength="30"></entity-field>


    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>