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
                       <div class="phase__title"><label class="phase__title--title"><?= i::__('Fase') ?>: </label><span class="item">{{item.name}}</span></div>
                       <div class="phase__title"><label class="phase__title--title"><?= i::__('Tipo') ?>: </label><span class="item">{{evaluationTypes[item.type]}}</span></div>

                   </div>
                    <div class="period"><label class="period__label"> <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: </label><span class="period__content">{{item.evaluationFrom.date('numeric year')}} <?= i::__('até') ?> {{item.evaluationTo.date('numeric year')}} as {{item.evaluationFrom.time('long year')}}</span></div>
                </div>
            </div>
        </template>
        <template #header-actions="{index, item}">
            <mc-link route="opportunity/opportunityEvaluations" :params="[item.id]" class="button button--primary evaluation-button"> <?= i::__('Avaliar') ?><mc-icon name="arrow-right-ios"></mc-icon></mc-link>
        </template>
    </mc-stepper-vertical>
</div>