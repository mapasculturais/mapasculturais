<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-icon
    mc-loading
    mc-title
');
?>

<!-- Painel: carrossel no mesmo padrão de Avaliações disponíveis -->
<div v-if="carousel && showBlock" class="panel--pending-evaluations panel--appeal-correction-assignments--carousel">
    <div class="panel--pending-evaluations__title">
        <label>{{ title || text('title-pending') }}</label>
    </div>

    <div class="panel--pending-evaluations__content">
        <carousel :settings="settings" :breakpoints="breakpoints" ref="carousel" @slide-end="resizeSlides">
            <slide v-for="assignment in filteredAssignments" :key="assignment.assignmentId">
                <div class="panel-entity-card card">
                    <div class="panel-entity-card__header">
                        <div class="left">
                            <mc-title small tag="h4" :shortLength="0" :longlength="1000" class="bold">
                                {{ assignment.opportunityName }}
                            </mc-title>
                            <h5 class="panel--pending-evaluations__phase-name opportunity__color--dark">
                                {{ assignment.phaseName }}
                            </h5>
                        </div>
                    </div>

                    <div class="panel-entity-card__main type-evaluation grid-12">
                        <div class="type-evaluation__type col-12">
                            <label class="entity-label"><?= i::__('Inscrição:') ?></label>
                            <strong class="opportunity__color entity-strong">{{ assignment.registrationNumber }}</strong>
                        </div>
                        <div v-if="assignment.targetEvaluatorName" class="type-evaluation__content col-12">
                            <label class="type-evaluation__content--label"><?= i::__('Avaliador original:') ?></label>
                            <strong class="type-evaluation__content--strong">{{ assignment.targetEvaluatorName }}</strong>
                        </div>
                        <div v-if="assignment.deadline" class="type-evaluation__date col-12">
                            <label class="entity-label">
                                <?= i::__('PRAZO: ') ?>
                                <strong class="primary__color">{{ formatDate(assignment.deadline) }}</strong>
                            </label>
                        </div>
                    </div>

                    <div class="panel-entity-card__footer">
                        <a :href="assignment.correctionUrl" class="button-evaluate button button--primary button--icon">
                            <?= i::__('Corrigir') ?>
                            <mc-icon name="arrow-right-ios"></mc-icon>
                        </a>
                    </div>
                </div>
            </slide>
            <template #addons>
                <div class="actions">
                    <navigation />
                </div>
            </template>
        </carousel>
    </div>
</div>

<!-- Minhas avaliações / #evaluations: lista completa -->
<div v-else-if="!carousel && showBlock" class="panel--appeal-correction-assignments">
    <div v-if="title" class="panel--appeal-correction-assignments__title">
        <label>{{ title }}</label>
        <a v-if="seeAllUrl" :href="seeAllUrl" class="panel--appeal-correction-assignments__see-all">
            <?= i::__('Ver todas') ?>
        </a>
    </div>

    <mc-loading v-if="loading" :condition="loading"><?= i::__('carregando...') ?></mc-loading>

    <mc-alert v-else-if="error" type="danger"><?= i::__('Não foi possível carregar as correções designadas.') ?></mc-alert>

    <p v-else-if="!filteredAssignments.length" class="panel--appeal-correction-assignments__empty">
        <?= i::__('Você não tem correções de avaliação designadas.') ?>
    </p>

    <div v-else class="panel-evaluations__cards">
        <div class="panel--appeal-correction-assignments__card" v-for="assignment in filteredAssignments" :key="assignment.assignmentId">
            <div class="panel--appeal-correction-assignments__content">
                <h3 class="bold">{{ assignment.opportunityName }}</h3>

                <div class="panel--appeal-correction-assignments__infos">
                    <span><?= i::__('FASE') ?>: <strong>{{ assignment.phaseName }}</strong></span>
                    <span><?= i::__('INSCRIÇÃO') ?>: <strong>{{ assignment.registrationNumber }}</strong></span>
                    <span v-if="assignment.targetEvaluatorName"><?= i::__('AVALIADOR ORIGINAL') ?>: <strong>{{ assignment.targetEvaluatorName }}</strong></span>
                    <span v-if="assignment.deadline"><?= i::__('PRAZO') ?>: <strong>{{ formatDate(assignment.deadline) }}</strong></span>
                    <span v-if="assignment.sentAt && assignment.status === 'sent'"><?= i::__('ENVIADA EM') ?>: <strong>{{ formatDate(assignment.sentAt) }}</strong></span>
                </div>

                <span class="panel--appeal-correction-assignments__badge" :class="'panel--appeal-correction-assignments__badge--' + assignment.status">
                    {{ statusLabel(assignment.status) }}
                </span>
            </div>

            <a v-if="assignment.available" :href="assignment.correctionUrl" class="button button--primary button--right-icon">
                <?= i::__('Corrigir avaliação') ?>
                <mc-icon name="arrow-right-ios"></mc-icon>
            </a>
            <span v-else-if="assignment.status !== 'sent'" class="panel--appeal-correction-assignments__unavailable">{{ unavailableLabel(assignment) }}</span>
        </div>
    </div>
</div>
