<?php
/**
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-card
    mc-avatar
    mc-loading
    mc-title
');
?>
<mc-loading :condition="loading"></mc-loading>
<div v-if="entities.length > 0" class="evaluation-card">
    <div v-for="entity in entities" :key="entity.id" class="evaluation-card__content">
        <mc-avatar :entity="entity" size="medium"></mc-avatar>

        <panel--entity-card :key="entity.id" :entity="entity" class="evaluation-card__group">
            <mc-title tag="h3" size="medium" :short-length="100" :long-length="130" class="evaluation-card__title">{{entity.parent?.name || entity.name}}</mc-title>

            <div class="evaluation-card__infos">
                <div class="evaluation-card__info">
                    <span class="entity-label"><?php i::_e('FASE:') ?> <strong>{{entity.phaseName}}</strong></span>

                    <span class="entity-label"><?php i::_e('TIPO:') ?> <strong>{{entity.type.name}}</strong></span>

                    <span class="entity-label"><?php i::_e('PERÍODO DE AVALIAÇÃO: ') ?><strong>{{evaluationFrom(entity).date('2-digit year')}} até {{evaluationTo(entity).date('2-digit year')}}</strong></span>
                </div>
            </div>

        </panel--entity-card>
        <div style="display: flex; justify-content: flex-end;min-width: 65rem">
            <mc-link :entity="entity" route="userEvaluations" class="button button--primary button--right-icon evaluation-card__button"> <?= i::__('Avaliar')?> <mc-icon name="arrow-right-ios"></mc-icon> </mc-link>
        </div>
    </div>
</div>
