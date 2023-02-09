<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;
?>

<?php
/**
 * @todo totalSteps deve receber lista com as labels de cada step
 */
?>

<div :class="['stepper' , {'small' : small}]">
    <div v-for="n in totalSteps" :class="['step', {'passedby' : step>=n}, {'active' : step==n}]">
        <div class="count">
            <span v-if="!small"> {{n}} </span>
            <span v-if="small && step==n"> {{n}} </span>
        </div>
        <span v-if="actualLabel && step==n && n!==totalSteps" :class="['label', {'active' : step==n}]"> {{actualLabel}} {{n}} </span>
        <span v-if="lastLabel && n==totalSteps" :class="['label', {'active' : step==n}]"> {{lastLabel}} </span>
    </div>
</div>

<!-- for debugging purposes -->
<div v-if="showButtons" class="buttons">
    <button @click="previousStep()">anterior</button>
    <button @click="nextStep()">proximo</button>
</div>
