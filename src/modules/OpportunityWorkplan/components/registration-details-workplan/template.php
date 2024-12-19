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
        <div v-if="workplan.id" class="field">
            <label><?= i::esc_attr__('ID:') ?></label>
            #{{ workplan.id }}
        </div>
        <div v-if="workplan.projectDuration" class="field">
            <label><?= i::esc_attr__('Duração do projeto (meses)') ?></label>
            {{ workplan.projectDuration }}
        </div>

        <div v-if="workplan.culturalArtisticSegment" class="field">
            <label><?= i::esc_attr__('Segmento artistico cultural') ?></label>
            {{ workplan.culturalArtisticSegment }}
        </div>

        <div v-for="(goal, index) in workplan.goals" :key="index" class="registration-details-workplan__goals">
            <div class="registration-details-workplan__header-goals">
                <h4 class="registration-details-workplan__goals-title">
                    {{ getGoalLabelDefault }} #{{ goal.id }} - {{ goal.title }}</h4>
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

            <!-- Valor da meta -->
            <div v-if="goal.amount" class="field">
                <label>
                    {{ `Valor da ${getGoalLabelDefault} (R$)` }}
                </label>
                {{ convertToCurrency(goal.amount) }}
            </div>


            <div v-for="(delivery, index_) in goal.deliveries" :key="delivery.id" class="registration-details-workplan__goals__deliveries">
                
                <div class="registration-details-workplan__header-deliveries">
                    <h4 class="registration-details-workplan__goals-title">
                        {{ getDeliveryLabelDefault }} #{{ delivery.id }} - {{ delivery.name }}</h4>
                </div>
                <div v-if="delivery.name" class="field">
                    <label><?= i::esc_attr__('Nome') ?></label>
                    {{ delivery.name }}
                </div>

                <div v-if="delivery.description" class="field">
                    <label><?= i::esc_attr__('Descrição') ?></label>
                    {{ delivery.description }}
                </div>

                <div v-if="delivery.type" class="field">
                    <label><?= i::esc_attr__('Tipo') ?></label>
                    {{ delivery.type }}
                </div>

                <div v-if="delivery.segmentDelivery" class="field">
                    <label><?= i::esc_attr__('Segmento artístico cultural') ?></label>
                    {{ delivery.segmentDelivery }}
                </div>

                <div v-if="delivery.budgetAction" class="field">
                    <label><?= i::esc_attr__('Ação orçamentária') ?></label>
                    {{ delivery.budgetAction }}
                </div>

                <div v-if="delivery.expectedNumberPeople" class="field">
                    <label><?= i::esc_attr__('Número previsto de pessoas') ?></label>
                    {{ delivery.expectedNumberPeople }}
                </div>


                <div v-if="delivery.generaterRevenu" class="field">
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
            </div>

        </div>

    </template>
</mc-card>