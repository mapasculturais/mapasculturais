<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-links-field
');
?>

<div class="registration-details-workplan__goals__deliveries">
    <div class="registration-details-workplan__header-deliveries">
        <h4 class="registration-details-workplan__goals-title">{{ deliveriesLabel }} - {{ delivery.name }}</h4>
        
        <div class="registration-details-workplan__goals-status">
            <mc-select v-if="editable" aria-label="Status" @change-option="proxy.status = $event.value">
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

        <div class="field" v-if="opportunity.workplan_monitoringInformTheFormOfAvailability && (editable || proxy.availabilityType)">
            <label :for="`${vid}__availabilityType`"><?= i::__('Forma de disponibilização') ?></label>
            <select v-if="editable" :id="`${vid}__availabilityType`" v-model="proxy.availabilityType">
                <option key="" value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(value, label) of availabilityOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ proxy.availabilityType }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformAccessibilityMeasures && (editable || proxy.accessibilityMeasures)">
            <label :for="`${vid}__accessibilityMeasures`"><?= i::__('Medidas de acessibilidade') ?></label>
            <mc-multiselect v-if="editable" :id="`${vid}__accessibilityMeasures`" :model="proxy.accessibilityMeasures" :items="accessibilityOptions"></mc-multiselect>
            <span v-else>{{ proxy.accessibilityMeasures }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringProvideTheProfileOfParticipants && (editable || proxy.participantProfile)">
            <label :for="`${vid}__participantProfile`"><?= i::__('Perfil dos participantes') ?></label>
            <input v-if="editable" :id="`${vid}__participantProfile`" type="text" v-model="proxy.participantProfile">
            <span v-else>{{ proxy.participantProfile }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformThePriorityAudience && (editable || proxy.priorityAudience)">
            <label :for="`${vid}__priorityAudience`"><?= i::__('Territórios prioritários') ?></label>
            <mc-multiselect v-if="editable" :id="`${vid}__priorityAudience`" :model="proxy.priorityAudience" :items="audienceOptions"></mc-multiselect>
            <span v-else>{{ proxy.priorityAudience }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_registrationReportTheNumberOfParticipants && (editable || proxy.numberOfParticipants)">
            <label :for="`${vid}__numberOfParticipants`"><?= i::__('Número de participantes') ?></label>
            <input v-if="editable" :id="`${vid}__numberOfParticipants`" type="number" v-model="proxy.numberOfParticipants" :items="[]">
            <span v-else>{{ proxy.numberOfParticipants }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringReportExecutedRevenue && (editable || proxy.executedRevenue)">
            <label :for="`${vid}__executedRevenue`"><?= i::__('Receita executada') ?></label>
            <input v-if="editable" :id="`${vid}__executedRevenue`" type="number" v-model="proxy.executedRevenue" :items="[]">
            <span v-else>{{ proxy.executedRevenue }}</span>
        </div>

        <div class="field" v-if="editable || proxy.evidenceLinks?.length > 0">
            <label :for="`${vid}__evidenceLinks`"><?= i::__('Receita executada') ?></label>
            <mc-links-field v-if="editable" :id="`${vid}__evidenceLinks`" v-model="proxy.evidenceLinks"></mc-links-field>
            <ul v-else>
                <li v-for="(link, index) of proxy.evidenceLinks" :key="index">
                    <a :href="link.value">{{ link.title || link.value }}</a>
                </li>
            </ul>
        </div>
    </template>
</div>