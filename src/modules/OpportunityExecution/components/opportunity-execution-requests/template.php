<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-loading
');
?>

<div v-if="executionPhase" class="opportunity-execution-requests col-12">
    <div class="opportunity-execution-requests__header">
        <h5 class="bold opportunity-phases-timeline__label"><?= i::__('Fase de Execução') ?></h5>
        <p class="description"><?= i::__('Você pode abrir pedidos de alteração enquanto o projeto estiver em execução. Cada pedido é analisado individualmente.') ?></p>
    </div>

    <div v-if="requests.length > 0" class="opportunity-execution-requests__list">
        <div v-for="req in requests" :key="req.id" class="opportunity-execution-requests__item item__dot-appeal-phase">
            <div class="item__dot-appeal-phase"><span class="dot"></span></div>
            <div class="item__content">
                <div class="item__content--title"><?= i::__('[Pedido]') ?> {{ req.number }}</div>
                <div class="opportunity-phases-timeline__box">
                    <label class="semibold opportunity-phases-timeline__label"><?= i::__('Situação:') ?></label>
                    <div class="opportunity-phases-timeline__status">
                        <mc-icon name="circle" :class="statusColor(req.status)"></mc-icon>
                        <p>{{ statusLabel(req.status) }}</p>
                    </div>
                </div>
                <div v-if="req.status == 0" class="opportunity-phases-timeline__request-appeal">
                    <button class="button button--primary button--sm" @click="fillForm(req)"><?= i::__('Preencher formulário') ?></button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="executionPhase.registrationFrom && !executionPhase.registrationTo?.isPast()" class="opportunity-execution-requests__new">
        <div v-if="canOpenRequest" class="opportunity-execution-requests__open-button">
            <button :disabled="processing" class="button button--primary button--primary-outline" @click="createRequest()">
                <mc-loading v-if="processing" :condition="processing"><?= i::__('abrindo') ?></mc-loading>
                <span v-else><?= i::__('Abrir novo pedido') ?></span>
            </button>
        </div>
    </div>
</div>
