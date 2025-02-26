<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>

<div class="registration-details-workplan__goals__deliveries">
    <div class="registration-details-workplan__header-deliveries">
        <h4 class="registration-details-workplan__goals-title">{{ deliveriesLabel }} - {{ delivery.name }}</h4>
        
        <div class="registration-details-workplan__goals-status">
            <mc-select aria-label="Status" @change-option="proxy.status = $event.value">
                <option v-for="(label, value) of statusOptions" :key="value" :value="value">{{ label }}</option>
            </mc-select>

            <button class="button" type="button" :aria-label="expanded ? '<?php i::esc_attr_e('Ocultar') ?>' : '<?php i::esc_attr_e('Exibir') ?>'" @click="expanded = !expanded">
                <mc-icon :name="expanded ? 'up' : 'down'"></mc-icon>
            </button>
        </div>
    </div>

    <template v-if="expanded">
        <div v-if="delivery.name" class="field">
            <label><?= i::esc_attr__('Nome') ?></label>
            {{ delivery.name }}
        </div>

        <div v-if="delivery.description" class="field">
            <label><?= i::esc_attr__('Descrição') ?></label>
            {{ delivery.description }}
        </div>

        <div v-if="delivery.typeDelivery" class="field">
            <label><?= i::esc_attr__('Tipo') ?></label>
            {{ delivery.typeDelivery }}
        </div>

        <div v-if="delivery.segmentDelivery" class="field">
            <label><?= i::esc_attr__('Segmento artístico cultural') ?></label>
            {{ delivery.segmentDelivery }}
        </div>

        <div v-if="delivery.expectedNumberPeople" class="field">
            <label><?= i::esc_attr__('Número previsto de pessoas') ?></label>
            {{ delivery.expectedNumberPeople }}
        </div>

        <div v-if="delivery.generaterRevenue" class="field">
            <label><?= i::esc_attr__('Irá gerar receita?') ?></label>
            {{ delivery.generaterRevenue }}
        </div>

        <div v-if="delivery.renevueQtd" class="field">
            <label><?= i::esc_attr__('Quantidade') ?></label>
            {{ delivery.renevueQtd }}
        </div>

        <div v-if="delivery.unitValueForecast" class="field">
            <label><?= i::esc_attr__('Previsão de valor unitário') ?></label>
            {{ convertToCurrency(delivery.unitValueForecast) }}
        </div>

        <div v-if="delivery.totalValueForecast" class="field">
            <label><?= i::esc_attr__(text: 'Previsão de valor total') ?></label>
            {{ convertToCurrency(delivery.totalValueForecast) }}
        </div>
    </template>
</div>