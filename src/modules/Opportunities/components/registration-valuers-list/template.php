<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<h3><?= i::__("Avaliadores") ?></h3>
<small><?= i::__("Marque/desmarque os avaliadores desta inscrição. Por padrão, são selecionados aqueles que avaliam de acordo com as regras de distribuição definidas.") ?></small>
<template v-for="evaluator in committee.evaluators[opportunity.id]">
    <label>
        <input type="checkbox" v-model="valuersExceptionsList[evaluator.userId]" @change="saveExceptions()">
        {{evaluator.agentName}}
        <span v-if="!evaluator.include_list && !evaluator.exclude_list" class="required">*</span>
    </label>
</template>
<small><span class="required">*</span> <?= i::__("Avaliador desta inscrição pela regra de distribuição") ?></small>