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
            <mc-multiselect v-if="editable" :id="`${vid}__accessibilityMeasures`" :model="accessibilityMeasures" :items="accessibilityOptions" hide-filter hide-button></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="accessibilityMeasures" :labels="accessibilityOptions" editable></mc-tag-list>
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
            <mc-multiselect v-if="editable" :id="`${vid}__priorityAudience`" :model="priorityAudience" :items="audienceOptions" hide-filter hide-button></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="priorityAudience" :labels="audienceOptions" editable></mc-tag-list>
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

        <div class="field" v-if="opportunity.workplan_deliveryInformPaidStaffByRole && delivery.paidStaffByRole && delivery.paidStaffByRole.length > 0">
            <label><?= i::__('Pessoas remuneradas por função (previsto)') ?></label>
            <ul class="field__note">
                <li v-for="(staff, index) in delivery.paidStaffByRole" :key="index">
                    <strong>{{ staff.role === 'Outra' && staff.customRole ? staff.customRole : staff.role }}:</strong> {{ staff.count }}
                </li>
            </ul>
        </div>

        <!-- Composição da equipe por gênero (executado) -->
        <div class="field" v-if="opportunity.workplan_monitoringInformTeamComposition && (editable || hasExecutedGenderData)">
            <label><?= i::__('Composição da equipe por gênero (executado)') ?></label>
            <div v-if="delivery.teamCompositionGender" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <?= i::__('Mulher cisg.') ?>: {{ delivery.teamCompositionGender.cisgenderWoman || 0 }},
                <?= i::__('Homem cisg.') ?>: {{ delivery.teamCompositionGender.cisgenderMan || 0 }},
                <?= i::__('Mulher trans') ?>: {{ delivery.teamCompositionGender.transgenderWoman || 0 }},
                <?= i::__('Homem trans') ?>: {{ delivery.teamCompositionGender.transgenderMan || 0 }},
                <?= i::__('Não-binário') ?>: {{ delivery.teamCompositionGender.nonBinary || 0 }},
                <?= i::__('Outra') ?>: {{ delivery.teamCompositionGender.otherGenderIdentity || 0 }},
                <?= i::__('Pref. não inf.') ?>: {{ delivery.teamCompositionGender.preferNotToSay || 0 }}
            </div>
            <template v-if="editable">
                <div class="grid-12">
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Mulher cisgênero') ?></label>
                        <input v-model.number="executedTeamCompositionGender.cisgenderWoman" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Homem cisgênero') ?></label>
                        <input v-model.number="executedTeamCompositionGender.cisgenderMan" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Mulher transgênero') ?></label>
                        <input v-model.number="executedTeamCompositionGender.transgenderWoman" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Homem transgênero') ?></label>
                        <input v-model.number="executedTeamCompositionGender.transgenderMan" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Pessoa não binária') ?></label>
                        <input v-model.number="executedTeamCompositionGender.nonBinary" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Outra identidade de gênero') ?></label>
                        <input v-model.number="executedTeamCompositionGender.otherGenderIdentity" type="number" min="0">
                    </div>
                    <div class="col-3 sm:col-6 field">
                        <label><?= i::esc_attr__('Prefiro não informar') ?></label>
                        <input v-model.number="executedTeamCompositionGender.preferNotToSay" type="number" min="0">
                    </div>
                </div>
                <div class="field__note">
                    <strong><?= i::__('Total executado:') ?></strong> {{ calculateGenderTotal(executedTeamCompositionGender) }}
                </div>
            </template>
            <template v-else>
                <div>
                    <?= i::__('Mulher cisg.') ?>: {{ proxy.executedTeamCompositionGender?.cisgenderWoman || 0 }},
                    <?= i::__('Homem cisg.') ?>: {{ proxy.executedTeamCompositionGender?.cisgenderMan || 0 }},
                    <?= i::__('Mulher trans') ?>: {{ proxy.executedTeamCompositionGender?.transgenderWoman || 0 }},
                    <?= i::__('Homem trans') ?>: {{ proxy.executedTeamCompositionGender?.transgenderMan || 0 }},
                    <?= i::__('Não-binário') ?>: {{ proxy.executedTeamCompositionGender?.nonBinary || 0 }},
                    <?= i::__('Outra') ?>: {{ proxy.executedTeamCompositionGender?.otherGenderIdentity || 0 }},
                    <?= i::__('Pref. não inf.') ?>: {{ proxy.executedTeamCompositionGender?.preferNotToSay || 0 }}
                </div>
            </template>
        </div>

        <!-- Composição da equipe por raça/cor (executado) -->
        <div class="field" v-if="opportunity.workplan_monitoringInformTeamComposition && (editable || hasExecutedRaceData)">
            <label><?= i::__('Composição da equipe por raça/cor (executado)') ?></label>
            <div v-if="delivery.teamCompositionRace" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <?= i::__('Branca') ?>: {{ delivery.teamCompositionRace.white || 0 }},
                <?= i::__('Preta') ?>: {{ delivery.teamCompositionRace.black || 0 }},
                <?= i::__('Parda') ?>: {{ delivery.teamCompositionRace.brown || 0 }},
                <?= i::__('Indígena') ?>: {{ delivery.teamCompositionRace.indigenous || 0 }},
                <?= i::__('Amarela') ?>: {{ delivery.teamCompositionRace.asian || 0 }},
                <?= i::__('Não decl.') ?>: {{ delivery.teamCompositionRace.notDeclared || 0 }}
            </div>
            <template v-if="editable">
                <div class="grid-12">
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Branca') ?></label>
                        <input v-model.number="executedTeamCompositionRace.white" type="number" min="0">
                    </div>
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Preta') ?></label>
                        <input v-model.number="executedTeamCompositionRace.black" type="number" min="0">
                    </div>
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Parda') ?></label>
                        <input v-model.number="executedTeamCompositionRace.brown" type="number" min="0">
                    </div>
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Indígena') ?></label>
                        <input v-model.number="executedTeamCompositionRace.indigenous" type="number" min="0">
                    </div>
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Amarela') ?></label>
                        <input v-model.number="executedTeamCompositionRace.asian" type="number" min="0">
                    </div>
                    <div class="col-4 sm:col-6 field">
                        <label><?= i::esc_attr__('Não declarado') ?></label>
                        <input v-model.number="executedTeamCompositionRace.notDeclared" type="number" min="0">
                    </div>
                </div>
                <div class="field__note">
                    <strong><?= i::__('Total executado:') ?></strong> {{ calculateRaceTotal(executedTeamCompositionRace) }}
                </div>
            </template>
            <template v-else>
                <div>
                    <?= i::__('Branca') ?>: {{ proxy.executedTeamCompositionRace?.white || 0 }},
                    <?= i::__('Preta') ?>: {{ proxy.executedTeamCompositionRace?.black || 0 }},
                    <?= i::__('Parda') ?>: {{ proxy.executedTeamCompositionRace?.brown || 0 }},
                    <?= i::__('Indígena') ?>: {{ proxy.executedTeamCompositionRace?.indigenous || 0 }},
                    <?= i::__('Amarela') ?>: {{ proxy.executedTeamCompositionRace?.asian || 0 }},
                    <?= i::__('Não decl.') ?>: {{ proxy.executedTeamCompositionRace?.notDeclared || 0 }}
                </div>
            </template>
        </div>

        <!-- Elo das artes executado -->
        <div class="field" v-if="opportunity.workplan_monitoringInformArtChainLink && (editable || proxy.executedArtChainLink)">
            <label :for="`${vid}__executedArtChainLink`"><?= i::__('Principal elo das artes acionado (executado)') ?></label>
            <div v-if="delivery.artChainLink" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.artChainLink }}
            </div>
            <select v-if="editable" :id="`${vid}__executedArtChainLink`" v-model="proxy.executedArtChainLink">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="opt in artChainLinkOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span v-else>{{ proxy.executedArtChainLink }}</span>
        </div>

        <!-- Canais de comunicação executados -->
        <div class="field" v-if="opportunity.workplan_monitoringInformCommunicationChannels && (editable || executedCommunicationChannels.length > 0)">
            <label :for="`${vid}__executedCommunicationChannels`"><?= i::__('Canais de comunicação utilizados (executado)') ?></label>
            <div v-if="delivery.communicationChannels && delivery.communicationChannels.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.communicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedCommunicationChannels`" :model="executedCommunicationChannels" :items="communicationChannelsOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedCommunicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" @remove="toggleExecutedCommunicationChannel($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedCommunicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
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