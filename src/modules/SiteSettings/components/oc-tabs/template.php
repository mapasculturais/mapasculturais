<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tabs
');
?>

<div class="oc-tabs">
    <template v-for="tab in activeTab">
        <div @click="changeOption(tab.ref)" class="item" :class="{'active' : tab.isActive}" :ref="tab.ref">
            {{tab.label}}
        </div>
    </template>
</div>



<template v-for="tab in activeTab">
    <div v-if="tab.isActive" class="content">
        <div class="stage">
            <slot :name="tab.ref" :tab="tab" :entity="entity">
                <h1>Conteúdo aba {{tab.label}}</h1>
            </slot>
        </div>
    </div>
</template>