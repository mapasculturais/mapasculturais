<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-stepper-vertical');
?>

<div class="stepper-evaluations">
    <mc-stepper-vertical :items="phases" allow-multiple>
        <template #header-title="{index, item}">
            <div class="card-evaluation">
                <img v-if="item.opportunity.files?.avatar" :src="item.opportunity.files?.avatar?.transformations.avatarSmall.url" class="img" />
                <div v-if="!item.opportunity.files?.avatar" class="img-fake">
                    <mc-icon name="opportunity"></mc-icon>
                </div>
                <h3 class="card-evaluation__title">{{item.opportunity.name}}</h3>
                <div class="card-evaluation__items">
                   <div class="phase">
                       <div class="phase__title"> <?= i::__('Fase') ?>: <span class="item">{{item.name}}</span></div>
                       <div class="phase__title"> <?= i::__('Tipo') ?>: <span class="item">{{evaluationTypes[item.type]}}</span></div>

                   </div>
                    <div class="period"> <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: <strong>{{item.evaluationFrom.date('numeric year')}} <?= i::__('até') ?> {{item.evaluationTo.date('numeric year')}} as {{item.evaluationFrom.time('long year')}}</strong></div>
                </div>
            </div>
        </template>
        <template #header-actions="{index, item}">
            <mc-link route="opportunity/opportunityEvaluations" :params="[item.id]" class="button button--primary"> <?= i::__('Avaliar') ?> </mc-link>
        </template>
    </mc-stepper-vertical>
</div>