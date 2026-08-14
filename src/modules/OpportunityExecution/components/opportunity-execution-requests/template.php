<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-loading
    registration-results
');
?>

<div v-if="executionPhase">
    <div v-if="canOpenRequest && executionPhase.registrationFrom && !executionPhase.registrationTo?.isPast()" class="opportunity-phases-timeline__box">
        <label class="semibold opportunity-phases-timeline__label"><?= i::__('Fase de Execução:') ?></label>
        <div class="opportunity-phases-timeline__buttons">
            <button v-if="!processing" class="button button--primary button--sm button--large" @click="createRequest()"><?= i::__('Abrir novo pedido') ?></button>
            <div v-if="processing" class="col-12">
                <mc-loading :condition="processing"><?= i::__('abrindo') ?></mc-loading>
            </div>
        </div>
    </div>

    <div v-for="req in requests" :key="req.id" class="opportunity-phases-timeline__request-appeal__box">
        <div class="item__dot-appeal-phase"><span class="dot"></span></div>
        <div class="item__content">
            <div class="item__content--title"><?= i::__('[Pedido]') ?> {{ req.id }}</div>
            <div class="opportunity-phases-timeline__box">
                <div>
                    <label v-if="req.status > 0" class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado do pedido:') ?></label>
                    <div class="opportunity-phases-timeline__status">
                        <mc-icon name="circle" :class="statusColor(req.status)"></mc-icon>
                        <p>{{ statusLabel(req.status) }}</p>
                    </div>
                </div>
                <div class="opportunity-phases-timeline__buttons">
                    <button v-if="req.status > 0" class="button button--primary button--sm button--large" @click="fillForm(req)"><?= i::__('Visualizar pedido') ?></button>
                    <button v-else class="button button--primary button--sm button--large" @click="fillForm(req)"><?= i::__('Preencher formulário') ?></button>
                </div>
            </div>
            <registration-results
                v-if="req.status > 0 && executionEvaluationPhase"
                :registration="req"
                :phase="executionEvaluationPhase">
            </registration-results>
        </div>
    </div>
</div>
