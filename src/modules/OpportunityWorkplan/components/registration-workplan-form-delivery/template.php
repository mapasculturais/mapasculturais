<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-files-list
    mc-icon
    mc-links-field
    mc-multiselect
    mc-tag-list
');
?>

<div class="registration-details-workplan__goals__deliveries">
    <div class="registration-details-workplan__header-deliveries">
        <h4 class="registration-details-workplan__goals-title">{{ deliveriesLabel }} - {{ delivery.name }}</h4>
        
        <div class="registration-details-workplan__goals-status">
            <mc-select v-if="editable" :default-value="proxy.status" aria-label="<?= i::esc_attr__('Status') ?>" @change-option="proxy.status = $event.value">
                <option v-for="(label, value) of statusOptions" :key="value" :value="value">{{ label }}</option>
            </mc-select>
            <span v-else>{{ statusOptions[proxy.status] }}</span>

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
            <?= i::__('Sim') ?>
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
            <small class="field__error" v-if="validationErrors.availabilityType">{{ validationErrors.availabilityType.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformAccessibilityMeasures && (editable || accessibilityMeasures.length > 0)">
            <label :for="`${vid}__accessibilityMeasures`"><?= i::__('Medidas de acessibilidade') ?></label>
            <mc-multiselect v-if="editable" :id="`${vid}__accessibilityMeasures`" :model="accessibilityMeasures" :items="accessibilityOptions"></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="accessibilityMeasures"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.accessibilityMeasures">{{ validationErrors.accessibilityMeasures.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringProvideTheProfileOfParticipants && (editable || proxy.participantProfile)">
            <label :for="`${vid}__participantProfile`"><?= i::__('Perfil dos participantes') ?></label>
            <input v-if="editable" :id="`${vid}__participantProfile`" type="text" v-model="proxy.participantProfile">
            <span v-else>{{ proxy.participantProfile }}</span>
            <small class="field__error" v-if="validationErrors.participantProfile">{{ validationErrors.participantProfile.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformThePriorityAudience && (editable || priorityAudience.length > 0)">
            <label :for="`${vid}__priorityAudience`"><?= i::__('Territórios prioritários') ?></label>
            <mc-multiselect v-if="editable" :id="`${vid}__priorityAudience`" :model="priorityAudience" :items="audienceOptions"></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="priorityAudience"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.priorityAudience">{{ validationErrors.priorityAudience.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_registrationReportTheNumberOfParticipants && (editable || proxy.numberOfParticipants)">
            <label :for="`${vid}__numberOfParticipants`"><?= i::__('Número de participantes') ?></label>
            <input v-if="editable" :id="`${vid}__numberOfParticipants`" type="number" v-model.number="proxy.numberOfParticipants">
            <span v-else>{{ proxy.numberOfParticipants }}</span>
            <small class="field__error" v-if="validationErrors.numberOfParticipants">{{ validationErrors.numberOfParticipants.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringReportExecutedRevenue && (editable || executedRevenue)">
            <label :for="`${vid}__executedRevenue`"><?= i::__('Receita executada') ?></label>
            <input v-if="editable" :id="`${vid}__executedRevenue`" type="number" v-model.number="executedRevenue">
            <span v-else>{{ convertToCurrency(executedRevenue) }}</span>
            <small class="field__error" v-if="validationErrors.executedRevenue">{{ validationErrors.executedRevenue.join('; ') }}</small>
        </div>

        <!-- NOVOS CAMPOS DE MONITORAMENTO (EXECUTADOS) -->
        
        <div class="field" v-if="opportunity.workplan_deliveryInformNumberOfCities && (editable || proxy.executedNumberOfCities)">
            <label :for="`${vid}__executedNumberOfCities`"><?= i::__('Municípios realizados') ?></label>
            <div v-if="delivery.numberOfCities" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.numberOfCities }}
            </div>
            <input v-if="editable" :id="`${vid}__executedNumberOfCities`" type="number" v-model.number="proxy.executedNumberOfCities" min="0">
            <span v-else>{{ proxy.executedNumberOfCities }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_deliveryInformNumberOfNeighborhoods && (editable || proxy.executedNumberOfNeighborhoods)">
            <label :for="`${vid}__executedNumberOfNeighborhoods`"><?= i::__('Bairros realizados') ?></label>
            <div v-if="delivery.numberOfNeighborhoods" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.numberOfNeighborhoods }}
            </div>
            <input v-if="editable" :id="`${vid}__executedNumberOfNeighborhoods`" type="number" v-model.number="proxy.executedNumberOfNeighborhoods" min="0">
            <span v-else>{{ proxy.executedNumberOfNeighborhoods }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_deliveryInformMediationActions && (editable || proxy.executedMediationActions)">
            <label :for="`${vid}__executedMediationActions`"><?= i::__('Ações de mediação realizadas') ?></label>
            <div v-if="delivery.mediationActions" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.mediationActions }}
            </div>
            <input v-if="editable" :id="`${vid}__executedMediationActions`" type="number" v-model.number="proxy.executedMediationActions" min="0">
            <span v-else>{{ proxy.executedMediationActions }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_deliveryInformCommercialUnits && (editable || proxy.executedCommercialUnits)">
            <label :for="`${vid}__executedCommercialUnits`"><?= i::__('Unidades comercializadas') ?></label>
            <div v-if="delivery.commercialUnits" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.commercialUnits }}
            </div>
            <input v-if="editable" :id="`${vid}__executedCommercialUnits`" type="number" v-model.number="proxy.executedCommercialUnits" min="0">
            <span v-else>{{ proxy.executedCommercialUnits }}</span>
        </div>

        <div class="field" v-if="opportunity.workplan_deliveryInformCommercialUnits && (editable || proxy.executedUnitPrice)">
            <label :for="`${vid}__executedUnitPrice`"><?= i::__('Valor unitário praticado (R$)') ?></label>
            <div v-if="delivery.unitPrice" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ convertToCurrency(delivery.unitPrice) }}
            </div>
            <input v-if="editable" :id="`${vid}__executedUnitPrice`" type="number" v-model.number="proxy.executedUnitPrice" min="0" step="0.01">
            <span v-else>{{ convertToCurrency(proxy.executedUnitPrice) }}</span>
        </div>
        
        <div class="field" v-if="editable || proxy.files.length > 0">
            <label :for="`${vid}__evidenceFiles`"><?= i::__('Evidências') ?></label>
            <entity-files-list :id="`${vid}__evidenceFiles`" :entity="dummyEntity" group="evidences" title="<?= i::esc_attr__('Arquivos de evidência') ?>" :editable="editable">
                <template #description>
                    <p v-if="editable"><?= i::__('Adicione vídeos, fotos e documentos que servirão como evidência para o seu projeto') ?></p>
                </template>
            </entity-files-list>
            <small class="field__error" v-if="validationErrors.evidenceFiles">{{ validationErrors.evidenceLinks.join('; ') }}</small>
        </div>

        <div class="field" v-if="editable || evidenceLinks.length > 0">
            <label :for="`${vid}__evidenceLinks`"><?= i::__('Links das evidências') ?></label>
            <mc-links-field v-if="editable" :id="`${vid}__evidenceLinks`" v-model="evidenceLinks"></mc-links-field>
            <ul v-else>
                <li v-for="(link, index) of evidenceLinks" :key="index">
                    <a :href="link.value">{{ link.title || link.value }}</a>
                </li>
            </ul>
            <small class="field__error" v-if="validationErrors.evidenceLinks">{{ validationErrors.evidenceLinks.join('; ') }}</small>
        </div>
    </template>
</div>