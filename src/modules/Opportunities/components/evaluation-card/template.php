<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
');
?>

<div class="evaluation-card">
    <div class="evaluation-card__content">
        <mc-avatar :entity="entity" size="medium"></mc-avatar>

        <div class="evaluation-card__group">
            <mc-link route="opportunity/userEvaluations" :params="[entity.id]"> 
                <mc-title tag="h3" size="medium" :short-length="100" :long-length="130" class="evaluation-card__title">{{entity.parent?.name || entity.name}}</mc-title>
            </mc-link>
            <div class="evaluation-card__infos">
                <div class="evaluation-card__info"> 
                    <span v-if="entity.parent"><?= i::__('FASE') ?>: <strong> {{entity.name}} </strong></span>
                    <span v-if="!entity.parent"><?= i::__('FASE') ?>: <strong> <?= i::__('Avaliação') ?> </strong></span>
                    <span v-if="!entity.isContinuousFlow || (entity.isContinuousFlow && entity.hasEndDate)">
                        <?= i::__('PERÍODO DE AVALIAÇÃO') ?>: 
                        <strong>{{dateFrom.date('numeric year')}} <?= i::__('até') ?> {{dateTo.date('numeric year')}} <?= i::__('as') ?> {{dateTo.time('long')}} </strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <mc-link route="opportunity/userEvaluations" :params="[entity.id]" class="button button--primary button--right-icon evaluation-card__button"> {{buttonLabel}} <mc-icon name="arrow-right-ios"></mc-icon> </mc-link>
</div>