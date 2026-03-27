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
            <label :for="`${vid}__availabilityType`"><?= i::__('Forma de disponibilização') ?><span v-if="opportunity.workplan_monitoringRequireAvailabilityType" class="required">obrigatório*</span></label>
            <select v-if="editable" :id="`${vid}__availabilityType`" v-model="proxy.availabilityType">
                <option key="" value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(value, label) of availabilityOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ proxy.availabilityType }}</span>
            <small class="field__error" v-if="validationErrors.availabilityType">{{ validationErrors.availabilityType.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformAccessibilityMeasures && (editable || accessibilityMeasures.length > 0)">
            <label :for="`${vid}__accessibilityMeasures`"><?= i::__('Medidas de acessibilidade') ?><span v-if="opportunity.workplan_monitoringRequireAccessibilityMeasures" class="required">obrigatório*</span></label>
            <mc-multiselect v-if="editable" :id="`${vid}__accessibilityMeasures`" :model="accessibilityMeasures" :items="accessibilityOptions" hide-filter hide-button></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="accessibilityMeasures" :labels="accessibilityOptions" editable></mc-tag-list>
            <small class="field__error" v-if="validationErrors.accessibilityMeasures">{{ validationErrors.accessibilityMeasures.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringProvideTheProfileOfParticipants && (editable || proxy.participantProfile)">
            <label :for="`${vid}__participantProfile`"><?= i::__('Perfil dos participantes') ?><span v-if="opportunity.workplan_monitoringRequireParticipantProfile" class="required">obrigatório*</span></label>
            <input v-if="editable" :id="`${vid}__participantProfile`" type="text" v-model="proxy.participantProfile">
            <span v-else>{{ proxy.participantProfile }}</span>
            <small class="field__error" v-if="validationErrors.participantProfile">{{ validationErrors.participantProfile.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformThePriorityAudience && (editable || priorityAudience.length > 0)">
            <label :for="`${vid}__priorityAudience`"><?= i::__('Territórios prioritários') ?><span v-if="opportunity.workplan_monitoringRequirePriorityAudience" class="required">obrigatório*</span></label>
            <mc-multiselect v-if="editable" :id="`${vid}__priorityAudience`" :model="priorityAudience" :items="audienceOptions" hide-filter hide-button></mc-multiselect>
            <mc-tag-list classes="primary__background" :tags="priorityAudience" :labels="audienceOptions" editable></mc-tag-list>
            <small class="field__error" v-if="validationErrors.priorityAudience">{{ validationErrors.priorityAudience.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformNumberOfParticipants && (editable || proxy.numberOfParticipants)">
            <label :for="`${vid}__numberOfParticipants`"><?= i::__('Número de participantes') ?><span v-if="opportunity.workplan_monitoringRequireNumberOfParticipants" class="required">obrigatório*</span></label>
            <input v-if="editable" :id="`${vid}__numberOfParticipants`" type="number" v-model.number="proxy.numberOfParticipants">
            <span v-else>{{ proxy.numberOfParticipants }}</span>
            <small class="field__error" v-if="validationErrors.numberOfParticipants">{{ validationErrors.numberOfParticipants.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringReportExecutedRevenue && (editable || executedRevenue)">
            <label :for="`${vid}__executedRevenue`"><?= i::__('Receita executada') ?><span v-if="opportunity.workplan_monitoringRequireExecutedRevenue" class="required">obrigatório*</span></label>
            <input v-if="editable" :id="`${vid}__executedRevenue`" type="number" v-model.number="executedRevenue">
            <span v-else>{{ convertToCurrency(executedRevenue) }}</span>
            <small class="field__error" v-if="validationErrors.executedRevenue">{{ validationErrors.executedRevenue.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformRevenueType && (editable || hasExecutedRevenueType)">
            <label :for="`${vid}__executedRevenueType`"><?= i::__('Tipo de receita executada') ?><span v-if="opportunity.workplan_monitoringRequireRevenueType" class="required">obrigatório*</span></label>
            <div v-if="delivery.revenueType && delivery.revenueType.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.revenueType" :labels="revenueTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedRevenueType`" :model="executedRevenueType" :items="revenueTypeOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedRevenueType" :labels="revenueTypeOptions" classes="opportunity__background" @remove="toggleExecutedRevenueType($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedRevenueType" :labels="revenueTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.executedRevenueType">{{ validationErrors.executedRevenueType.join('; ') }}</small>
        </div>

        <!-- NOVOS CAMPOS DE MONITORAMENTO (EXECUTADOS) -->
        
        <div class="field" v-if="opportunity.workplan_monitoringInformNumberOfCities && (editable || proxy.executedNumberOfCities)">
            <label :for="`${vid}__executedNumberOfCities`"><?= i::__('Municípios realizados') ?><span v-if="opportunity.workplan_monitoringRequireNumberOfCities" class="required">obrigatório*</span></label>
            <div v-if="delivery.numberOfCities" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.numberOfCities }}
            </div>
            <input v-if="editable" :id="`${vid}__executedNumberOfCities`" type="number" v-model.number="proxy.executedNumberOfCities" min="0">
            <span v-else>{{ proxy.executedNumberOfCities }}</span>
            <small class="field__error" v-if="validationErrors.executedNumberOfCities">{{ validationErrors.executedNumberOfCities.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformNumberOfNeighborhoods && (editable || proxy.executedNumberOfNeighborhoods)">
            <label :for="`${vid}__executedNumberOfNeighborhoods`"><?= i::__('Bairros realizados') ?><span v-if="opportunity.workplan_monitoringRequireNumberOfNeighborhoods" class="required">obrigatório*</span></label>
            <div v-if="delivery.numberOfNeighborhoods" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.numberOfNeighborhoods }}
            </div>
            <input v-if="editable" :id="`${vid}__executedNumberOfNeighborhoods`" type="number" v-model.number="proxy.executedNumberOfNeighborhoods" min="0">
            <span v-else>{{ proxy.executedNumberOfNeighborhoods }}</span>
            <small class="field__error" v-if="validationErrors.executedNumberOfNeighborhoods">{{ validationErrors.executedNumberOfNeighborhoods.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformMediationActions && (editable || proxy.executedMediationActions)">
            <label :for="`${vid}__executedMediationActions`"><?= i::__('Ações de mediação realizadas') ?><span v-if="opportunity.workplan_monitoringRequireMediationActions" class="required">obrigatório*</span></label>
            <div v-if="delivery.mediationActions" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.mediationActions }}
            </div>
            <input v-if="editable" :id="`${vid}__executedMediationActions`" type="number" v-model.number="proxy.executedMediationActions" min="0">
            <span v-else>{{ proxy.executedMediationActions }}</span>
            <small class="field__error" v-if="validationErrors.executedMediationActions">{{ validationErrors.executedMediationActions.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformCommercialUnits && (editable || proxy.executedCommercialUnits)">
            <label :for="`${vid}__executedCommercialUnits`"><?= i::__('Unidades comercializadas') ?><span v-if="opportunity.workplan_monitoringRequireCommercialUnits" class="required">obrigatório*</span></label>
            <div v-if="delivery.commercialUnits" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.commercialUnits }}
            </div>
            <input v-if="editable" :id="`${vid}__executedCommercialUnits`" type="number" v-model.number="proxy.executedCommercialUnits" min="0">
            <span v-else>{{ proxy.executedCommercialUnits }}</span>
            <small class="field__error" v-if="validationErrors.executedCommercialUnits">{{ validationErrors.executedCommercialUnits.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformCommercialUnits && (editable || proxy.executedUnitPrice)">
            <label :for="`${vid}__executedUnitPrice`"><?= i::__('Valor unitário praticado (R$)') ?><span v-if="opportunity.workplan_monitoringRequireUnitPrice" class="required">obrigatório*</span></label>
            <div v-if="delivery.unitPrice !== null && delivery.unitPrice !== ''" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ convertToCurrency(delivery.unitPrice) }}
            </div>
            <input v-if="editable" :id="`${vid}__executedUnitPrice`" type="number" v-model.number="proxy.executedUnitPrice" min="0" step="0.01">
            <span v-else>{{ convertToCurrency(proxy.executedUnitPrice) }}</span>
            <small class="field__error" v-if="validationErrors.executedUnitPrice">{{ validationErrors.executedUnitPrice.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_deliveryInformPaidStaffByRole && delivery.paidStaffByRole && delivery.paidStaffByRole.length > 0">
            <label><?= i::__('Pessoas remuneradas por função (previsto)') ?></label>
            <ul class="field__note">
                <li v-for="(staff, index) in delivery.paidStaffByRole" :key="index">
                    <strong>{{ staff.role === 'Outra' && staff.customRole ? staff.customRole : staff.role }}:</strong> {{ staff.count }}
                </li>
            </ul>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformPaidStaffByRole && (editable || (executedPaidStaffByRole && executedPaidStaffByRole.length > 0))">
            <label :for="`${vid}__executedPaidStaffByRole`"><?= i::__('Pessoas remuneradas por função (executado)') ?><span v-if="opportunity.workplan_monitoringRequirePaidStaffByRole" class="required">obrigatório*</span></label>
            <div v-if="delivery.paidStaffByRole && delivery.paidStaffByRole.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <ul>
                    <li v-for="(staff, index) in delivery.paidStaffByRole" :key="index">
                        <strong>{{ staff.role === 'Outra' && staff.customRole ? staff.customRole : staff.role }}:</strong> {{ staff.count }}
                    </li>
                </ul>
            </div>
            <template v-if="editable">
                <div v-if="!executedPaidStaffByRole || !executedPaidStaffByRole.length" class="field__note">
                    <button type="button" class="button button--sm button--primary-outline" @click="addExecutedPaidStaffRole()">
                        <?= i::__('+ Adicionar função') ?>
                    </button>
                </div>
                <div v-else class="paid-staff-list">
                    <div v-for="(staff, index) in executedPaidStaffByRole" :key="index" class="paid-staff-item">
                        <div class="paid-staff-item__header">
                            <p class="paid-staff-item__title semibold">{{ index + 1 }}ª <?= i::__('Função') ?></p>
                            <button type="button" class="button button--delete button--icon button--sm" @click="removeExecutedPaidStaffRole(index)">
                                <mc-icon name="trash"></mc-icon>
                                <?= i::__('Remover') ?>
                            </button>
                        </div>
                        <div class="paid-staff-item__fields grid-12">
                            <div class="col-6 sm:col-12 field">
                                <label><?= i::esc_attr__('Função') ?></label>
                                <select v-model="staff.role">
                                    <option value=""><?= i::esc_attr__('Selecione a função') ?></option>
                                    <option v-for="roleOption in paidStaffRoleOptions" :key="roleOption" :value="roleOption">{{ roleOption }}</option>
                                </select>
                            </div>
                            <div class="col-6 sm:col-12 field">
                                <label><?= i::esc_attr__('Quantidade') ?></label>
                                <input v-model.number="staff.count" type="number" min="0" placeholder="<?= i::esc_attr__('Quantidade de pessoas') ?>">
                            </div>
                            <div v-if="staff.role === 'Outra'" class="col-12 field">
                                <label><?= i::esc_attr__('Especifique a função') ?></label>
                                <input v-model="staff.customRole" type="text" placeholder="<?= i::esc_attr__('Digite o nome da função') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="paid-staff-add">
                        <button type="button" class="button button--sm button--icon button--primary-outline" @click="addExecutedPaidStaffRole()">
                            <mc-icon name="add"></mc-icon>
                            <?= i::__('Adicionar outra função') ?>
                        </button>
                    </div>
                </div>
            </template>
            <template v-else>
                <ul v-if="executedPaidStaffByRole && executedPaidStaffByRole.length > 0">
                    <li v-for="(staff, index) in executedPaidStaffByRole" :key="index">
                        <strong>{{ staff.role === 'Outra' && staff.customRole ? staff.customRole : staff.role }}:</strong> {{ staff.count }}
                    </li>
                </ul>
            </template>
            <small class="field__error" v-if="validationErrors.executedPaidStaffByRole">{{ validationErrors.executedPaidStaffByRole.join('; ') }}</small>
        </div>

        <!-- Composição da equipe por gênero (executado) -->
        <div class="field" v-if="opportunity.workplan_monitoringInformTeamComposition && (editable || hasExecutedGenderData)">
            <label><?= i::__('Composição da equipe por gênero (executado)') ?><span v-if="opportunity.workplan_monitoringRequireTeamCompositionGender" class="required">obrigatório*</span></label>
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
            <small class="field__error" v-if="validationErrors.executedTeamCompositionGender">{{ validationErrors.executedTeamCompositionGender.join('; ') }}</small>
        </div>

        <!-- Composição da equipe por raça/cor (executado) -->
        <div class="field" v-if="opportunity.workplan_monitoringInformTeamComposition && (editable || hasExecutedRaceData)">
            <label><?= i::__('Composição da equipe por raça/cor (executado)') ?><span v-if="opportunity.workplan_monitoringRequireTeamCompositionRace" class="required">obrigatório*</span></label>
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
            <small class="field__error" v-if="validationErrors.executedTeamCompositionRace">{{ validationErrors.executedTeamCompositionRace.join('; ') }}</small>
        </div>

        <!-- Elo das artes executado -->
        <div class="field" v-if="opportunity.workplan_monitoringInformArtChainLink && (editable || proxy.executedArtChainLink)">
            <label :for="`${vid}__executedArtChainLink`"><?= i::__('Principal elo das artes acionado (executado)') ?><span v-if="opportunity.workplan_monitoringRequireArtChainLink" class="required">obrigatório*</span></label>
            <div v-if="delivery.artChainLink" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.artChainLink }}
            </div>
            <select v-if="editable" :id="`${vid}__executedArtChainLink`" v-model="proxy.executedArtChainLink">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="opt in artChainLinkOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span v-else>{{ proxy.executedArtChainLink }}</span>
            <small class="field__error" v-if="validationErrors.executedArtChainLink">{{ validationErrors.executedArtChainLink.join('; ') }}</small>
        </div>

        <!-- Canais de comunicação executados -->
        <div class="field" v-if="opportunity.workplan_monitoringInformCommunicationChannels && (editable || executedCommunicationChannels.length > 0)">
            <label :for="`${vid}__executedCommunicationChannels`"><?= i::__('Canais de comunicação utilizados (executado)') ?><span v-if="opportunity.workplan_monitoringRequireCommunicationChannels" class="required">obrigatório*</span></label>
            <div v-if="delivery.communicationChannels && delivery.communicationChannels.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.communicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedCommunicationChannels`" :model="executedCommunicationChannels" :items="communicationChannelsOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedCommunicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" @remove="toggleExecutedCommunicationChannel($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedCommunicationChannels" :labels="communicationChannelsOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.executedCommunicationChannels">{{ validationErrors.executedCommunicationChannels.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformSegmentDelivery && (editable || proxy.executedSegmentDelivery)">
            <label :for="`${vid}__executedSegmentDelivery`"><?= i::__('Segmento artístico-cultural executado') ?><span v-if="opportunity.workplan_monitoringRequireSegmentDelivery" class="required">obrigatório*</span></label>
            <div v-if="delivery.segmentDelivery" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.segmentDelivery }}
            </div>
            <select v-if="editable" :id="`${vid}__executedSegmentDelivery`" v-model="proxy.executedSegmentDelivery">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="opt in segmentDeliveryOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <span v-else>{{ proxy.executedSegmentDelivery }}</span>
            <small class="field__error" v-if="validationErrors.executedSegmentDelivery">{{ validationErrors.executedSegmentDelivery.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformCommunityCoauthors && (editable || proxy.executedHasCommunityCoauthors || proxy.executedCommunityCoauthorsDetail)">
            <label :for="`${vid}__executedHasCommunityCoauthors`"><?= i::__('Houve coautoria/coexecução com comunidades/coletivos?') ?></label>
            <div v-if="delivery.hasCommunityCoauthors" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasCommunityCoauthors] ?? delivery.hasCommunityCoauthors }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasCommunityCoauthors`" v-model="proxy.executedHasCommunityCoauthors">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasCommunityCoauthors] ?? proxy.executedHasCommunityCoauthors }}</span>
            <small class="field__error" v-if="validationErrors.executedHasCommunityCoauthors">{{ validationErrors.executedHasCommunityCoauthors.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformCommunityCoauthors && proxy.executedHasCommunityCoauthors === 'true' && (editable || proxy.executedCommunityCoauthorsDetail)">
            <label :for="`${vid}__executedCommunityCoauthorsDetail`"><?= i::__('Descreva a coautoria/coexecução realizada') ?><span v-if="opportunity.workplan_monitoringRequireCommunityCoauthorsDetail" class="required">obrigatório*</span></label>
            <div v-if="delivery.communityCoauthorsDetail" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.communityCoauthorsDetail }}
            </div>
            <textarea v-if="editable" :id="`${vid}__executedCommunityCoauthorsDetail`" v-model="proxy.executedCommunityCoauthorsDetail" rows="3"></textarea>
            <span v-else>{{ proxy.executedCommunityCoauthorsDetail }}</span>
            <small class="field__error" v-if="validationErrors.executedCommunityCoauthorsDetail">{{ validationErrors.executedCommunityCoauthorsDetail.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformTransInclusion && (editable || proxy.executedHasTransInclusionStrategy || proxy.executedTransInclusionActions)">
            <label :for="`${vid}__executedHasTransInclusionStrategy`"><?= i::__('Houve estratégias executadas de inclusão Trans e Travestis?') ?></label>
            <div v-if="delivery.hasTransInclusionStrategy" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasTransInclusionStrategy] ?? delivery.hasTransInclusionStrategy }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasTransInclusionStrategy`" v-model="proxy.executedHasTransInclusionStrategy">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasTransInclusionStrategy] ?? proxy.executedHasTransInclusionStrategy }}</span>
            <small class="field__error" v-if="validationErrors.executedHasTransInclusionStrategy">{{ validationErrors.executedHasTransInclusionStrategy.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformTransInclusion && proxy.executedHasTransInclusionStrategy === 'true' && (editable || proxy.executedTransInclusionActions)">
            <label :for="`${vid}__executedTransInclusionActions`"><?= i::__('Quais ações foram executadas?') ?><span v-if="opportunity.workplan_monitoringRequireTransInclusionActions" class="required">obrigatório*</span></label>
            <div v-if="delivery.transInclusionActions" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.transInclusionActions }}
            </div>
            <textarea v-if="editable" :id="`${vid}__executedTransInclusionActions`" v-model="proxy.executedTransInclusionActions" rows="3"></textarea>
            <span v-else>{{ proxy.executedTransInclusionActions }}</span>
            <small class="field__error" v-if="validationErrors.executedTransInclusionActions">{{ validationErrors.executedTransInclusionActions.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformAccessibilityPlan && (editable || proxy.executedHasAccessibilityPlan || hasExecutedExpectedAccessibilityMeasures)">
            <label :for="`${vid}__executedHasAccessibilityPlan`"><?= i::__('Houve medidas de acessibilidade executadas?') ?></label>
            <div v-if="delivery.hasAccessibilityPlan" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasAccessibilityPlan] ?? delivery.hasAccessibilityPlan }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasAccessibilityPlan`" v-model="proxy.executedHasAccessibilityPlan">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasAccessibilityPlan] ?? proxy.executedHasAccessibilityPlan }}</span>
            <small class="field__error" v-if="validationErrors.executedHasAccessibilityPlan">{{ validationErrors.executedHasAccessibilityPlan.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformAccessibilityPlan && proxy.executedHasAccessibilityPlan === 'true' && (editable || hasExecutedExpectedAccessibilityMeasures)">
            <label :for="`${vid}__executedExpectedAccessibilityMeasures`"><?= i::__('Quais medidas de acessibilidade foram executadas?') ?><span v-if="opportunity.workplan_monitoringRequireExpectedAccessibilityMeasures" class="required">obrigatório*</span></label>
            <div v-if="delivery.expectedAccessibilityMeasures && delivery.expectedAccessibilityMeasures.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.expectedAccessibilityMeasures" :labels="accessibilityPlanOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedExpectedAccessibilityMeasures`" :model="executedExpectedAccessibilityMeasures" :items="accessibilityPlanOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedExpectedAccessibilityMeasures" :labels="accessibilityPlanOptions" classes="opportunity__background" @remove="toggleExecutedExpectedAccessibilityMeasure($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedExpectedAccessibilityMeasures" :labels="accessibilityPlanOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.executedExpectedAccessibilityMeasures">{{ validationErrors.executedExpectedAccessibilityMeasures.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformEnvironmentalPractices && (editable || proxy.executedHasEnvironmentalPractices || proxy.executedEnvironmentalPracticesDescription)">
            <label :for="`${vid}__executedHasEnvironmentalPractices`"><?= i::__('Houve medidas ou práticas socioambientais executadas?') ?></label>
            <div v-if="delivery.hasEnvironmentalPractices" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasEnvironmentalPractices] ?? delivery.hasEnvironmentalPractices }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasEnvironmentalPractices`" v-model="proxy.executedHasEnvironmentalPractices">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasEnvironmentalPractices] ?? proxy.executedHasEnvironmentalPractices }}</span>
            <small class="field__error" v-if="validationErrors.executedHasEnvironmentalPractices">{{ validationErrors.executedHasEnvironmentalPractices.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformEnvironmentalPractices && proxy.executedHasEnvironmentalPractices === 'true' && (editable || proxy.executedEnvironmentalPracticesDescription)">
            <label :for="`${vid}__executedEnvironmentalPracticesDescription`"><?= i::__('Quais práticas socioambientais foram executadas?') ?><span v-if="opportunity.workplan_monitoringRequireEnvironmentalPracticesDescription" class="required">obrigatório*</span></label>
            <div v-if="delivery.environmentalPracticesDescription" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ delivery.environmentalPracticesDescription }}
            </div>
            <textarea v-if="editable" :id="`${vid}__executedEnvironmentalPracticesDescription`" v-model="proxy.executedEnvironmentalPracticesDescription" rows="3"></textarea>
            <span v-else>{{ proxy.executedEnvironmentalPracticesDescription }}</span>
            <small class="field__error" v-if="validationErrors.executedEnvironmentalPracticesDescription">{{ validationErrors.executedEnvironmentalPracticesDescription.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformPressStrategy && (editable || proxy.executedHasPressStrategy)">
            <label :for="`${vid}__executedHasPressStrategy`"><?= i::__('Houve estratégia de relacionamento com a imprensa?') ?></label>
            <div v-if="delivery.hasPressStrategy" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasPressStrategy] ?? delivery.hasPressStrategy }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasPressStrategy`" v-model="proxy.executedHasPressStrategy">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasPressStrategy] ?? proxy.executedHasPressStrategy }}</span>
            <small class="field__error" v-if="validationErrors.executedHasPressStrategy">{{ validationErrors.executedHasPressStrategy.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformInnovation && (editable || proxy.executedHasInnovationAction || hasExecutedInnovationTypes)">
            <label :for="`${vid}__executedHasInnovationAction`"><?= i::__('Houve ação de experimentação/inovação executada?') ?></label>
            <div v-if="delivery.hasInnovationAction" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong> {{ booleanOptions[delivery.hasInnovationAction] ?? delivery.hasInnovationAction }}
            </div>
            <select v-if="editable" :id="`${vid}__executedHasInnovationAction`" v-model="proxy.executedHasInnovationAction">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="(label, value) in booleanOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <span v-else>{{ booleanOptions[proxy.executedHasInnovationAction] ?? proxy.executedHasInnovationAction }}</span>
            <small class="field__error" v-if="validationErrors.executedHasInnovationAction">{{ validationErrors.executedHasInnovationAction.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformInnovation && proxy.executedHasInnovationAction === 'true' && (editable || hasExecutedInnovationTypes)">
            <label :for="`${vid}__executedInnovationTypes`"><?= i::__('Quais tipos de experimentação/inovação foram executados?') ?><span v-if="opportunity.workplan_monitoringRequireInnovationTypes" class="required">obrigatório*</span></label>
            <div v-if="delivery.innovationTypes && delivery.innovationTypes.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.innovationTypes" :labels="innovationTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedInnovationTypes`" :model="executedInnovationTypes" :items="innovationTypeOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedInnovationTypes" :labels="innovationTypeOptions" classes="opportunity__background" @remove="toggleExecutedInnovationType($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedInnovationTypes" :labels="innovationTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.executedInnovationTypes">{{ validationErrors.executedInnovationTypes.join('; ') }}</small>
        </div>

        <div class="field" v-if="opportunity.workplan_monitoringInformDocumentationTypes && (editable || hasExecutedDocumentationTypes)">
            <label :for="`${vid}__executedDocumentationTypes`"><?= i::__('Tipos de documentação produzida') ?><span v-if="opportunity.workplan_monitoringRequireDocumentationTypes" class="required">obrigatório*</span></label>
            <div v-if="delivery.documentationTypes && delivery.documentationTypes.length > 0" class="field__note">
                <strong><?= i::__('Previsto:') ?></strong>
                <mc-tag-list :tags="delivery.documentationTypes" :labels="documentationTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            </div>
            <template v-if="editable">
                <mc-multiselect :id="`${vid}__executedDocumentationTypes`" :model="executedDocumentationTypes" :items="documentationTypeOptions" hide-filter hide-button></mc-multiselect>
                <mc-tag-list :tags="executedDocumentationTypes" :labels="documentationTypeOptions" classes="opportunity__background" @remove="toggleExecutedDocumentationType($event)" editable></mc-tag-list>
            </template>
            <mc-tag-list v-else :tags="executedDocumentationTypes" :labels="documentationTypeOptions" classes="opportunity__background" :editable="false"></mc-tag-list>
            <small class="field__error" v-if="validationErrors.executedDocumentationTypes">{{ validationErrors.executedDocumentationTypes.join('; ') }}</small>
        </div>

        <div class="field" v-if="editable || proxy.files.length > 0">
            <label :for="`${vid}__evidenceFiles`"><?= i::__('Evidências') ?></label>
            <entity-files-list :id="`${vid}__evidenceFiles`" :entity="dummyEntity" group="evidences" title="<?= i::esc_attr__('Arquivos de evidência') ?>" :editable="editable">
                <template #description>
                    <p v-if="editable"><?= i::__('Adicione vídeos, fotos e documentos que servirão como evidência para o seu projeto') ?></p>
                </template>
            </entity-files-list>
            <small class="field__error" v-if="validationErrors.evidenceLinks">{{ validationErrors.evidenceLinks.join('; ') }}</small>
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
