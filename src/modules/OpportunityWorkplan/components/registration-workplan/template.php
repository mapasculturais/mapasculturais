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
    mc-confirm-button
    mc-currency-input
');
?>
<mc-card class="registration-workplan" v-if="registration.opportunity.enableWorkplan">
    <template #title>
        <h3 class="card__title">
            {{ getWorkplanLabelDefault }}
        </h3>
        <p>
            {{ `Descrição do ${getWorkplanLabelDefault}` }}
        </p>
    </template>
    <template #content>
        <div class="field">
            <label><?= i::esc_attr__('Duração do projeto (meses)') ?><span class="required">obrigatório*</span></label>
            <select v-model="workplan.projectDuration" @blur="save_(false)">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="n in optionsProjectDurationData()" :key="n" :value="n">{{ n }}</option>
            </select>
        </div>

        <div class="field">
            <label><?= i::esc_attr__('Segmento artistico cultural') ?><span class="required">obrigatório*</span></label>
            <select v-model="workplan.culturalArtisticSegment" @blur="save_(false)">
                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                <option v-for="n in workplanFields.culturalArtisticSegment.options" :key="n" :value="n">{{ n }}</option>
            </select>
        </div>

        <!-- Metas -->
        <div v-for="(goal, index) in workplan.goals" :key="index" class="registration-workplan__goals">
            <div class="registration-workplan__header-goals">
                <h4 class="registration-workplan__goals-title" @click="toggle(index)">
                    {{ goal.title }}
                    <mc-icon v-if="isExpanded(index)" name="arrowPoint-up"></mc-icon>
                    <mc-icon v-if="!isExpanded(index)" name="arrowPoint-down"></mc-icon>
                </h4>

                <div v-if="goal.id" class="registration-workplan__delete-goal">
                    <mc-confirm-button @confirm="deleteGoal(goal.id)">
                        <template #button="{open}">
                            <button class="button button--delete button--icon button--sm" @click="open()">
                                <mc-icon name="trash"></mc-icon>
                                {{ `Excluir ${getGoalLabelDefault}` }}
                            </button>
                        </template>
                        <template #message="message">
                            <h3>{{ `Excluir ${getGoalLabelDefault}` }}</h3><br>
                            <p>
                                {{ `Deseja excluir a ${getGoalLabelDefault} selecionada, todas as suas configurações e as respectivas ${getDeliveryLabelDefault} associadas a ela?` }}
                            </p>
                        </template>
                    </mc-confirm-button>
                </div>
            </div>
            <h6> {{ getGoalLabelDefault }} {{ index + 1 }}</h6>
            <div v-if="isExpanded(index)" class="collapse-content">
                <div class="registration-workplan__goals-period">
                    <p>
                        {{ `Duração da ${getGoalLabelDefault}` }}
                    </p>
                    <div class="registration-workplan__goals-months">
                        <div class="field">
                            <label><?= i::esc_attr__('Mês inicial') ?><span class="required">obrigatório*</span></label>
                            <select v-model="goal.monthInitial" id="mes-inicial">
                                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                <option v-for="n in parseInt(workplan.projectDuration)" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="mes-final"><?= i::esc_attr__('Mês final') ?><span class="required">obrigatório*</span></label>
                            <select v-model="goal.monthEnd" id="mes-final">
                                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                <option v-for="n in range(parseInt(goal.monthInitial), parseInt(workplan.projectDuration)) " :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Título da meta -->
                <div class="field">
                    <label>
                        {{ `Título da ${getGoalLabelDefault}` }}<span class="required">obrigatório*</span></label>
                    <input v-model="goal.title" type="text">
                </div>

                <!-- Descrição -->
                <div class="field">
                    <label><?= i::esc_attr__('Descrição') ?><span class="required">obrigatório*</span></label>
                    <textarea v-model="goal.description"></textarea>
                </div>

                <!-- Etapa do fazer cultural -->
                <div v-if="opportunity.workplan_metaInformTheStageOfCulturalMaking" class="field">
                    <label><?= i::esc_attr__('Etapa do fazer cultural') ?><span class="required">obrigatório*</span></label>
                    <select v-model="goal.culturalMakingStage">
                        <option value=""><?= i::esc_attr__('Selecione') ?></option>
                        <option v-for="n in workplanFields.goal.culturalMakingStage.options" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>

                <!-- Valor da meta -->
                <div v-if="opportunity.workplan_metaInformTheValueGoals" class="field">
                    <label>
                        {{ `Valor da ${getGoalLabelDefault} (R$)` }}
                        <span class="required">obrigatório*</span></label>
                    <mc-currency-input v-model="goal.amount"></mc-currency-input>
                </div>

                <div v-for="(delivery, index_) in goal.deliveries" :key="delivery.id" class="registration-workplan__goals__deliveries">
                    <div class="registration-workplan__header-deliveries">
                        <h4 class="registration-workplan__goals-title">{{ delivery.name }}</h4>
                        <div v-if="delivery.id" class="registration-workplan__delete-delivery">
                            <mc-confirm-button @confirm="deleteDelivery(delivery.id)">
                                <template #button="{open}">
                                    <button class="button button--delete button--icon button--sm" @click="open()">
                                        <mc-icon name="trash"></mc-icon> 
                                        {{ `Excluir ${getDeliveryLabelDefault}` }}
                                    </button>
                                </template>
                                <template #message="message">
                                    <h3>{{ `Excluir ${getDeliveryLabelDefault}` }}</h3><br>
                                    <p>
                                        {{ `Deseja excluir a ${getDeliveryLabelDefault} selecionada e todas as suas respectivas configurações?` }}
                                    </p>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                    <h6>{{ getDeliveryLabelDefault }} {{ index_ + 1 }}</h6>
                    <div class="field">
                        <label>{{ `Nome da ${getDeliveryLabelDefault}` }}<span class="required">obrigatório*</span></label>
                        <input v-model="delivery.name" type="text">
                    </div>

                    <div class="field">
                        <label><?= i::esc_attr__('Descrição') ?><span class="required">obrigatório*</span></label>
                        <textarea v-model="delivery.description"></textarea>
                    </div>

                    <div class="field">
                        <label>
                            {{ `Tipo ${getDeliveryLabelDefault}` }}<span class="required">obrigatório*</span></label>
                        <select v-model="delivery.type">
                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                            <option v-for="type in opportunity.workplan_monitoringInformDeliveryType" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>

                    <div v-if="opportunity.workplan_registrationInformCulturalArtisticSegment" class="field">
                        <label>
                            {{ `Segmento artístico cultural da ${getDeliveryLabelDefault}` }}
                            <span class="required">obrigatório*</span></label>
                        <select v-model="delivery.segmentDelivery">
                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                            <option v-for="n in workplanFields.goal.delivery.segmentDelivery.options" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <div v-if="opportunity.workplan_registrationInformActionPAAR" class="field">
                        <label><?= i::esc_attr__('Ação orçamentária') ?><span class="required">obrigatório*</span></label>
                        <select v-model="delivery.budgetAction">
                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                            <option v-for="n in workplanFields.goal.delivery.budgetAction.options" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <div v-if="opportunity.workplan_registrationReportTheNumberOfParticipants" class="field">
                        <label><?= i::esc_attr__('Número previsto de pessoas') ?><span class="required">obrigatório*</span></label>
                        <input v-model="delivery.expectedNumberPeople" type="number">
                    </div>

                    <div v-if="opportunity.workplan_registrationReportExpectedRenevue">
                        <div class="field">
                            <label>
                                {{ `A ${getDeliveryLabelDefault} irá gerar receita?` }}
                                <span class="required">obrigatório*</span></label>
                            <select v-model="delivery.generaterRevenue">
                                <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                <option v-for="(n, i) in workplanFields.goal.delivery.generaterRevenue.options" :key="i" :value="i">{{ n }}</option>
                            </select>
                        </div>

                        <div v-if="delivery.generaterRevenue == 'true'" class="grid-12">
                            <div class="field col-4 sm:col-12">
                                <label><?= i::esc_attr__('Quantidade') ?><span class="required">obrigatório*</span></label>
                                <input v-model="delivery.renevueQtd" type="number">
                            </div>

                            <div class="field col-4 sm:col-12">
                                <label><?= i::esc_attr__('Previsão de valor unitário') ?><span class="required">obrigatório*</span></label>
                                <mc-currency-input v-model="delivery.unitValueForecast"></mc-currency-input>
                            </div>

                            <div class="field col-4 sm:col-12">
                                <label><?= i::esc_attr__(text: 'Previsão de valor total') ?><span class="required">obrigatório*</span></label>
                                <input readonly :model="delivery.totalValueForecast" :value="totalValueForecastToCurrency(delivery, delivery.renevueQtd, delivery.unitValueForecast)">
                            </div>
                        </div>
                    </div>


                </div>

                <div v-if="enableNewDelivery(goal)" class="registration-workplan__new-delivery">
                    <button class="button button--primary-outline" @click="newDelivery(goal)">
                        + {{ getDeliveryLabelDefault }}
                    </button>
                </div>

                <div class="registration-workplan__save-goal">
                    <button class="button button--primary" @click="save_">
                        {{ `Salvar ${getGoalLabelDefault}` }}
                    </button>
                </div>

            </div>
        </div>

        <div v-if="enableNewGoal(workplan)" class="registration-workplan__new-goal">
            <button class="button button--primary-outline" @click="newGoal">
                + {{ getGoalLabelDefault }}
            </button>
        </div>
    </template>
</mc-card>