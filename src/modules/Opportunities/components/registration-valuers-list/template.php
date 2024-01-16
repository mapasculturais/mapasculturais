<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>


<template v-for="evaluator in committee.evaluators[opportunity.id]">
    <label>
        <input type="checkbox" v-model="valuersExceptionsList[evaluator.userId]">
        {{evaluator.agentName}}
    </label>
</template>