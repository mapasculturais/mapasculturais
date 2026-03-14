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
                <div class="item__content--title"><?= i::__('[Pedido]') ?> {{ req.category }}</div>
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
        <div v-if="!showForm && canOpenRequest" class="opportunity-execution-requests__open-button">
            <button v-if="!processing" class="button button--primary button--primary-outline" @click="showForm = true">
                <?= i::__('Abrir novo pedido') ?>
            </button>
        </div>

        <div v-if="showForm" class="opportunity-execution-requests__form">
            <h5 class="bold"><?= i::__('Novo pedido de alteração') ?></h5>
            <div class="col-12">
                <label class="semibold"><?= i::__('Categoria do pedido:') ?></label>
                <select v-model="selectedCategory" class="col-12">
                    <option value="" disabled><?= i::__('Selecione o tipo de pedido') ?></option>
                    <option v-for="cat in executionPhase.registrationCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
            </div>
            <div class="col-12 opportunity-execution-requests__form-actions">
                <button class="button button--secondary" @click="showForm = false; selectedCategory = ''"><?= i::__('Cancelar') ?></button>
                <button class="button button--primary" :disabled="!selectedCategory || processing" @click="createRequest()">
                    <mc-loading v-if="processing" :condition="processing"><?= i::__('enviando') ?></mc-loading>
                    <span v-else><?= i::__('Enviar pedido') ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
