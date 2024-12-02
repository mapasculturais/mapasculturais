<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div id="stepper" class="stepper" :class="{'noNavigation' : disableNavigation}" :style="{ '--steps': steps.length }">
    <button v-for="(stepped, n) in steps" type="button" class="step" :class="{'passedby' : step > n, 'active' : step == n}" :id="'step'+(n+1)" :title="stepped" :disabled="disabledSteps.includes(n)" @click="goToStep(n)">
        <div class="count"></div>
        <span v-show="!noLabels" class="label active" v-if="step == n">{{n + 1}}. {{stepped}}</span>
        <span v-show="!noLabels" class="label" v-else>{{n + 1}}</span>
    </button>
</div>