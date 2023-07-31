<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div :class="['stepper' , {'small' : small}, {'noNavigation' : disableNavigation}]" id="stepper">
    <div v-for="(steped, n) in steps" :class="['step', {'passedby' : step>=n}, {'active' : step==n}]" :id="'step'+(n+1)" @click="!disableNavigation && goToStep(n)">
        <div class="count">
            <span v-if="!small"> {{n+1}} </span>
            <span v-if="small && step==n"> {{n+1}} </span>
        </div>
        <span v-show="steps && !noLabels" :class="['label', {'active' : step==n}, {'show' : (onlyActiveLabel && (step==n || n==totalSteps)) || !onlyActiveLabel }]"> {{steped}} </span>
    </div>
</div>