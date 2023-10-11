<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="stepper-evaluations">
    <div class="stepper-evaluations__title"><label><?= i::__('Avaliações abertas e disponíveis') ?></label></div>
    <div class="line"></div>
    <div v-for="item in openEvaluations" class="card-list">
        <div class="card-evaluation">
            <div class="card_evaluation__content">
                <h3 class="card-evaluation__content--title">{{item.name}}</h3>
                <div class="card-evaluation__content--items">
                    <div class="phase">
                        <div class="phase__title"><label class="phase__title--title"><?= i::__('Tipo') ?>: </label><span class="item">{{evaluationTypes[item.type]}}</span></div>
                    </div>
                    <div class="period"><label class="period__label"> <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: </label><span class="period__content">{{item.evaluationFrom.date('numeric year')}} <?= i::__('até') ?> {{item.evaluationTo.date('numeric year')}} as {{item.evaluationTo.time('long year')}}</span></div>
                </div>
            </div>
            <div class="btn">
                <mc-link route="opportunity/userEvaluations" :params="[item.opportunity.id]" class="button button--primary evaluation-button"> <?= i::__('Avaliar') ?><mc-icon name="arrow-right-ios"></mc-icon></mc-link>
            </div>
        </div>
    </div>
    <div v-if="openEvaluations.length === 0" class="out-evalution"><?= i::__('Você não tem avaliações abertas ou disponíveis.') ?></div>

    <h3 class="stepper-evaluations__title secondTitle"><?= i::__('Avaliações Encerradas') ?></h3>
    <div class="line"></div>
    <div v-if="openEvaluations.length>0 && closedEvaluations===0" class="out-evalution"><?= i::__('Você ainda não tem avaliações encerradas.') ?></div>
    <div v-for="item in closedEvaluations" class="card-list">
        <div class="card-evaluation">
            <div class="card_evaluation__content">
                <h3 class="card-evaluation__content--title">{{item.name}}</h3>
                <div class="card-evaluation__content--items">
                    <div class="phase">
                        <div class="phase__title"><label class="phase__title--title"><?= i::__('Tipo') ?>: </label><span class="item">{{evaluationTypes[item.type]}}</span></div>
                    </div>
                    <div class="period"><label class="period__label"> <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: </label><span class="period__content">{{item.evaluationFrom.date('numeric year')}} <?= i::__('até') ?> {{item.evaluationTo.date('numeric year')}} as {{item.evaluationTo.time('long year')}}</span></div>
                </div>
            </div>
            <div class="btn">
                <mc-link route="opportunity/userEvaluations" :params="[item.opportunity.id]" class="button button--primary evaluation-button"> <?= i::__('Avaliar') ?><mc-icon name="arrow-right-ios"></mc-icon></mc-link>
            </div>
        </div>
    </div>
</div>