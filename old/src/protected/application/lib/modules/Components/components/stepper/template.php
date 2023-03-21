<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;
?>

<div :class="['stepper' , {'small' : small}]">
    <div v-for="n in totalSteps" :class="['step', {'passedby' : step>=n}, {'active' : step==n}]">
        <div class="count">
            <span v-if="!small"> {{n}} </span>
            <span v-if="small && step==n"> {{n}} </span>
        </div>
        <span v-show="steps && !noLabels" :class="['label', {'active' : step==n}, {'show' : (onlyActiveLabel && (step==n || n==totalSteps)) || !onlyActiveLabel }]"> {{steps[n-1]}} </span>
    </div>
</div>