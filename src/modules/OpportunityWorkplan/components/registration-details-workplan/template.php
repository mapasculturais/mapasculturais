<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-card
    mc-icon
    mc-currency-input
');
?>
<mc-card class="registration-details-workplan" v-if="registration.opportunity.enableWorkplan">
    <template #title>
        <h3 class="card__title">{{ getWorkplanLabelDefault }}</h3>
        <p><?= i::esc_attr__('Dados da ação cultural.') ?></p>
    </template>
    <template #content>
        <div v-if="workplan.projectDuration" class="field">
            <label><?= i::esc_attr__('Duração do projeto (meses)') ?></label>
            {{ workplan.projectDuration }}
        </div>

        <div v-if="workplan.culturalArtisticSegment" class="field">
            <label><?= i::esc_attr__('Segmento artistico-cultural') ?></label>
            {{ workplan.culturalArtisticSegment }}
        </div>

        <div v-for="(goal, index) in workplan.goals" :key="index" class="registration-details-workplan__goals">
            <div class="registration-details-workplan__header-goals">
                <h4 class="registration-details-workplan__goals-title">
                    {{ getGoalLabelDefault }} - {{ goal.title }}</h4>
            </div>

            <div v-if="goal.monthInitial" class="field">
                <label><?= i::esc_attr__('Mês inicial') ?></label>
                {{ goal.monthInitial }}
            </div>
            <div v-if="goal.monthEnd" class="field">
                <label for="mes-final"><?= i::esc_attr__('Mês final') ?></label>
                {{ goal.monthEnd }}
            </div>
            <div v-if="goal.title" class="field">
                <label>{{ `Titulo da ${getGoalLabelDefault}` }}</label>
                {{ goal.title }}
            </div>

            <div v-if="goal.description" class="field">
                <label><?= i::esc_attr__('Descrição') ?></label>
                {{ goal.description }}
            </div>

            <!-- Etapa do fazer cultural -->
            <div v-if="goal.culturalMakingStage" class="field">
                <label><?= i::esc_attr__('Etapa do fazer cultural') ?></label>
                {{ goal.culturalMakingStage }}
            </div>

            <div v-for="(delivery, index_) in goal.deliveries" :key="delivery.id" class="registration-details-workplan__goals__deliveries">
                <div class="registration-details-workplan__header-deliveries">
                    <h4 class="registration-details-workplan__goals-title">
                        {{ getDeliveryLabelDefault }} - {{ delivery.name }}</h4>
                </div>
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
                <template v-if="opportunity.workplan_deliveryInformDeliveryPeriod">
                    <div v-if="delivery.monthInitial" class="field">
                        <label><?= i::esc_attr__('Mês inicial da entrega') ?></label>
                        {{ delivery.monthInitial }}
                    </div>
                    <div v-if="delivery.monthEnd" class="field">
                        <label><?= i::esc_attr__('Mês final da entrega') ?></label>
                        {{ delivery.monthEnd }}
                    </div>
                </template>

                <div v-if="opportunity.workplan_deliveryInformArtChainLink && delivery.artChainLink" class="field">
                    <label><?= i::esc_attr__('Principal elo das artes') ?></label>
                    {{ delivery.artChainLink }}
                </div>

                <div v-if="opportunity.workplan_deliveryInformTotalBudget && delivery.totalBudget !== null && delivery.totalBudget !== ''" class="field">
                    <label><?= i::esc_attr__('Orçamento total') ?></label>
                    {{ convertToCurrency(delivery.totalBudget) }}
                </div>

                <div v-if="opportunity.workplan_deliveryInformNumberOfCities && delivery.numberOfCities" class="field">
                    <label><?= i::esc_attr__('Número de municípios') ?></label>
                    {{ delivery.numberOfCities }}
                </div>

                <div v-if="opportunity.workplan_deliveryInformNumberOfNeighborhoods && delivery.numberOfNeighborhoods" class="field">
                    <label><?= i::esc_attr__('Número de bairros') ?></label>
                    {{ delivery.numberOfNeighborhoods }}
                </div>

                <div v-if="opportunity.workplan_deliveryInformMediationActions && delivery.mediationActions" class="field">
                    <label><?= i::esc_attr__('Ações de mediação previstas') ?></label>
                    {{ delivery.mediationActions }}
                </div>

                <template v-if="opportunity.workplan_deliveryInformRevenueType && parseJson(delivery.revenueType)?.length">
                    <div class="field">
                        <label><?= i::esc_attr__('Tipo de receita') ?></label>
                        <ul>
                            <li v-for="(item, i) in parseJson(delivery.revenueType)" :key="i">{{ item }}</li>
                        </ul>
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformCommercialUnits">
                    <div v-if="delivery.commercialUnits" class="field">
                        <label><?= i::esc_attr__('Unidades para comercialização') ?></label>
                        {{ delivery.commercialUnits }}
                    </div>
                    <div v-if="delivery.unitPrice !== null && delivery.unitPrice !== ''" class="field">
                        <label><?= i::esc_attr__('Valor unitário previsto') ?></label>
                        {{ convertToCurrency(delivery.unitPrice) }}
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformPaidStaffByRole && parseJson(delivery.paidStaffByRole)?.length">
                    <div class="field">
                        <label><?= i::esc_attr__('Pessoas remuneradas por função') ?></label>
                        <ul>
                            <li v-for="(staff, i) in parseJson(delivery.paidStaffByRole)" :key="i">
                                {{ staff.role === 'Outra' && staff.customRole ? staff.customRole : staff.role }}: {{ staff.count }}
                            </li>
                        </ul>
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformTeamComposition">
                    <div v-if="parseJson(delivery.teamCompositionGender)" class="field">
                        <label><?= i::esc_attr__('Composição da equipe por gênero') ?></label>
                        <?= i::__('Mulher cisg.') ?>: {{ parseJson(delivery.teamCompositionGender)?.cisgenderWoman || 0 }},
                        <?= i::__('Homem cisg.') ?>: {{ parseJson(delivery.teamCompositionGender)?.cisgenderMan || 0 }},
                        <?= i::__('Mulher trans') ?>: {{ parseJson(delivery.teamCompositionGender)?.transgenderWoman || 0 }},
                        <?= i::__('Homem trans') ?>: {{ parseJson(delivery.teamCompositionGender)?.transgenderMan || 0 }},
                        <?= i::__('Não-binário') ?>: {{ parseJson(delivery.teamCompositionGender)?.nonBinary || 0 }},
                        <?= i::__('Outra') ?>: {{ parseJson(delivery.teamCompositionGender)?.otherGenderIdentity || 0 }},
                        <?= i::__('Pref. não inf.') ?>: {{ parseJson(delivery.teamCompositionGender)?.preferNotToSay || 0 }}
                    </div>
                    <div v-if="parseJson(delivery.teamCompositionRace)" class="field">
                        <label><?= i::esc_attr__('Composição da equipe por raça/cor') ?></label>
                        <?= i::__('Branca') ?>: {{ parseJson(delivery.teamCompositionRace)?.white || 0 }},
                        <?= i::__('Preta') ?>: {{ parseJson(delivery.teamCompositionRace)?.black || 0 }},
                        <?= i::__('Parda') ?>: {{ parseJson(delivery.teamCompositionRace)?.brown || 0 }},
                        <?= i::__('Indígena') ?>: {{ parseJson(delivery.teamCompositionRace)?.indigenous || 0 }},
                        <?= i::__('Amarela') ?>: {{ parseJson(delivery.teamCompositionRace)?.asian || 0 }},
                        <?= i::__('Não decl.') ?>: {{ parseJson(delivery.teamCompositionRace)?.notDeclared || 0 }}
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformCommunityCoauthors">
                    <div v-if="delivery.hasCommunityCoauthors" class="field">
                        <label><?= i::esc_attr__('Envolve comunidades/coletivos como coautores?') ?></label>
                        {{ delivery.hasCommunityCoauthors === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                    </div>
                    <div v-if="delivery.communityCoauthorsDetail" class="field">
                        <label><?= i::esc_attr__('Detalhamento de coautoria') ?></label>
                        {{ delivery.communityCoauthorsDetail }}
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformTransInclusion">
                    <div v-if="delivery.hasTransInclusionStrategy" class="field">
                        <label><?= i::esc_attr__('Prevê estratégias para pessoas Trans/Travestis?') ?></label>
                        {{ delivery.hasTransInclusionStrategy === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                    </div>
                    <div v-if="delivery.transInclusionActions" class="field">
                        <label><?= i::esc_attr__('Ações de inclusão Trans/Travestis') ?></label>
                        {{ delivery.transInclusionActions }}
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformAccessibilityPlan">
                    <div v-if="delivery.hasAccessibilityPlan" class="field">
                        <label><?= i::esc_attr__('Prevê medidas de acessibilidade?') ?></label>
                        {{ delivery.hasAccessibilityPlan === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                    </div>
                    <div v-if="parseJson(delivery.expectedAccessibilityMeasures)?.length" class="field">
                        <label><?= i::esc_attr__('Medidas de acessibilidade previstas') ?></label>
                        <ul>
                            <li v-for="(item, i) in parseJson(delivery.expectedAccessibilityMeasures)" :key="i">{{ item }}</li>
                        </ul>
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformEnvironmentalPractices">
                    <div v-if="delivery.hasEnvironmentalPractices" class="field">
                        <label><?= i::esc_attr__('Prevê práticas socioambientais?') ?></label>
                        {{ delivery.hasEnvironmentalPractices === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                    </div>
                    <div v-if="delivery.environmentalPracticesDescription" class="field">
                        <label><?= i::esc_attr__('Descrição das práticas socioambientais') ?></label>
                        {{ delivery.environmentalPracticesDescription }}
                    </div>
                </template>

                <div v-if="opportunity.workplan_deliveryInformPressStrategy && delivery.hasPressStrategy" class="field">
                    <label><?= i::esc_attr__('Estratégia de imprensa') ?></label>
                    {{ delivery.hasPressStrategy === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                </div>

                <template v-if="opportunity.workplan_deliveryInformCommunicationChannels && parseJson(delivery.communicationChannels)?.length">
                    <div class="field">
                        <label><?= i::esc_attr__('Canais de comunicação') ?></label>
                        <ul>
                            <li v-for="(item, i) in parseJson(delivery.communicationChannels)" :key="i">{{ item }}</li>
                        </ul>
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformInnovation">
                    <div v-if="delivery.hasInnovationAction" class="field">
                        <label><?= i::esc_attr__('Prevê experimentação/inovação?') ?></label>
                        {{ delivery.hasInnovationAction === 'true' ? '<?= i::__('Sim') ?>' : '<?= i::__('Não') ?>' }}
                    </div>
                    <div v-if="parseJson(delivery.innovationTypes)?.length" class="field">
                        <label><?= i::esc_attr__('Tipos de experimentação/inovação') ?></label>
                        <ul>
                            <li v-for="(item, i) in parseJson(delivery.innovationTypes)" :key="i">{{ item }}</li>
                        </ul>
                    </div>
                </template>

                <template v-if="opportunity.workplan_deliveryInformDocumentationTypes && parseJson(delivery.documentationTypes)?.length">
                    <div class="field">
                        <label><?= i::esc_attr__('Tipos de documentação') ?></label>
                        <ul>
                            <li v-for="(item, i) in parseJson(delivery.documentationTypes)" :key="i">{{ item }}</li>
                        </ul>
                    </div>
                </template>
            </div>

        </div>

    </template>
</mc-card>
