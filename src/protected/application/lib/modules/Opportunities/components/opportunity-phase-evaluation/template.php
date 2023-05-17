<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-stepper-vertical');
?>

<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div>
            <div>
                <img v-if="item.opportunity.files?.avatar" :src="item.opportunity.files?.avatar?.transformations.avatarSmall.url" class="img" />
                <div v-if="!item.opportunity.files?.avatar" class="img-fake">
                    <mc-icon name="opportunity"></mc-icon>
                </div>
                <h2>{{item.opportunity.name}}</h2>
                <div>
                    <div> <?= i::__('Fase') ?>: <strong>{{item.name}}</strong></div>
                    <div> <?= i::__('Tipo') ?>: <strong>{{evaluationTypes[item.type]}}</strong></div>
                    <div> <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: <strong>{{item.evaluationFrom.date('numeric year')}} <?= i::__('até') ?> {{item.evaluationTo.date('numeric year')}} as {{item.evaluationFrom.time('long year')}}</strong></div>
                </div>
            </div>
        </div>
    </template>
    <template #header-actions="{index, item}">
        <mc-link route="opportunity/opportunityEvaluations" :params="[item.id]" class="button button--primary"> <?= i::__('Avaliar') ?> </mc-link>
    </template>
</mc-stepper-vertical>