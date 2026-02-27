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
    mc-tag-list
');
?>

<div id="registration-workplan">
<mc-card class="registration-workplan" v-if="registration.opportunity.enableWorkplan && enableWorkplanInStep">
    <template #title>
        <h3 class="card__title">
            {{ getWorkplanLabelDefault }}
            <?php $this->info('inscricao -> preenchimento -> plano-de-metas') ?>
        </h3>
        <p>
            {{ `Descrição do ${getWorkplanLabelDefault}` }}
        </p>
        <br>
        <div class="registration-actions__alert">
            <div class="registration-actions__alert-header">
                <mc-icon name="exclamation"></mc-icon>
                <span class="bold"><?= i::__('Atenção - Preenchimento do plano de metas') ?></span>
            </div>
            <div class="registration-actions__alert-content">
                <span><?= i::__('Para registrar as metas e entregas do plano de metas, preencha os campos obrigatórios e clique no botão "Salvar Meta"') ?></span>
            </div>
        </div>
        <br>
        <!-- Botão para ativar tutorial -->
        <div v-if="isTutorialDisabled()" >
            <button class="button button--primary button--primary-outline button--sm" @click="enableTutorial(); startTutorialWorkplan()">
                <mc-icon name="help"></mc-icon>
                <?= i::__('Reativar assistente de configuração') ?>
            </button>
        </div>
    </template>
    <template #content>
        <!-- BLOCO 1 — Sobre o projeto -->
        <div class="form-section">
            <h4 class="form-section__title">1. <?= i::__('Sobre o projeto') ?></h4>
            <p class="form-section__description"><?= i::__('Informações gerais que ajudam a contextualizar o projeto antes do detalhamento das metas.') ?></p>

            <div class="field" id="projectDuration">
                <label><?= i::esc_attr__('Duração do projeto (meses)') ?><span class="required">obrigatório*</span></label>
                <select class="field__limits" v-model="workplan.projectDuration" @change="save_(false)">
                    <option value=""><?= i::esc_attr__('Selecione') ?></option>
                    <option v-for="n in optionsProjectDurationData()" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>

            <div v-if="opportunity.workplan_dataProjectInformCulturalArtisticSegment" class="field" id="culturalArtisticSegment">
                <label><?= i::esc_attr__('Segmento artistico-cultural') ?><span class="required">obrigatório*</span></label>
                <select v-model="workplan.culturalArtisticSegment" @change="save_(false)">
                    <option value=""><?= i::esc_attr__('Selecione') ?></option>
                    <option v-for="n in workplanFields.culturalArtisticSegment.options" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>
        </div>

        <hr class="form-section__divider">

        <!-- Metas -->
        <div id="container_goals">
            <div v-for="(goal, index) in workplan.goals" :key="index" class="registration-workplan__goals">
                <div class="registration-workplan__header-goals">
                    <h4 class="registration-workplan__goals-title" @click="toggle(index)">
                        {{ goal.title || (getGoalLabelDefault + ' ' + (index + 1)) }}
                        <mc-icon v-if="isExpanded(index)" name="arrowPoint-up"></mc-icon>
                        <mc-icon v-if="!isExpanded(index)" name="arrowPoint-down"></mc-icon>
                    </h4>

                    <div id="registration-workplan__delete-goal" class="registration-workplan__delete-goal">
                        <mc-confirm-button @confirm="deleteGoal(goal)">
                            <template #button="{open}">
                                <button class="button button--delete button--icon button--sm" @click="open()">
                                    <mc-icon name="trash"></mc-icon>
                                    {{ `Excluir ${getGoalLabelDefault}`  }}
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
                
                <div v-if="isExpanded(index)" class="collapse-content">
                    <!-- BLOCO 2 — Plano de metas -->
                    <div class="form-section">
                        <h4 class="form-section__title">2. <?= i::__('Plano de metas') ?></h4>
                        <p class="form-section__description"><?= i::__('Descreva o que o projeto pretende realizar. Foque nos objetivos, não na execução.') ?></p>
                        
                        <div class="registration-workplan__goals-period">
                            <p>
                                {{ `Especificação da ${getGoalLabelDefault}` }}
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
                                <option v-for="n in workplanFields.goal?.culturalMakingStage?.options" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                    </div>

                    <div id="container_deliveries">
                        <div v-for="(delivery, index_) in goal.deliveries" :key="delivery.id" class="registration-workplan__goals__deliveries">
                            <div class="registration-workplan__header-deliveries">
                                <h4 class="registration-workplan__goals-title">{{ delivery.name || (getDeliveryLabelDefault + ' ' + (index_ + 1)) }}</h4>
                                <div id="registration-workplan__delete-delivery"  class="registration-workplan__delete-delivery">
                                    <mc-confirm-button @confirm="deleteDelivery(delivery)">
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

                            <!-- BLOCO 3 — Entregas, execução e receita -->
                            <div class="form-section">
                                <h4 class="form-section__title">3. <?= i::__('Entregas, execução e receita') ?></h4>
                                <p class="form-section__description"><?= i::__('Detalhe como cada meta será executada: tipo de entrega, público esperado, localização, orçamento e equipe remunerada.') ?></p>

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
                                        {{ `Tipo de ${getDeliveryLabelDefault}` }}<span class="required">obrigatório*</span></label>
                                    <select v-model="delivery.typeDelivery">
                                        <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                        <option v-for="n in workplanFields.goal.delivery.typeDelivery.options" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </div>

                                <div v-if="opportunity.workplan_registrationReportTheNumberOfParticipants" class="field">
                                    <label><?= i::esc_attr__('Número previsto de pessoas') ?><span v-if="opportunity.workplan_deliveryRequireExpectedNumberPeople" class="required">obrigatório*</span></label>
                                    <input class="field__limits" v-model="delivery.expectedNumberPeople" min="0" type="number">
                                </div>
                            </div>
                            <div v-if="opportunity.workplan_deliveryInformArtChainLink" class="field">
                                <label><?= i::esc_attr__('Principal elo das artes acionado') ?><span v-if="opportunity.workplan_deliveryRequireArtChainLink" class="required">obrigatório*</span></label>
                                <select v-model="delivery.artChainLink">
                                    <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                    <option v-for="n in workplanFields.goal.delivery.artChainLink.options" :key="n" :value="n">{{ n }}</option>
                                </select>
                            </div>

                            <div v-if="opportunity.workplan_deliveryInformTotalBudget" class="field">
                                <label><?= i::esc_attr__('Qual o orçamento total da atividade?') ?><span v-if="opportunity.workplan_deliveryRequireTotalBudget" class="required">obrigatório*</span></label>
                                <mc-currency-input v-model="delivery.totalBudget"></mc-currency-input>
                            </div>

                            <div v-if="opportunity.workplan_deliveryInformNumberOfCities" class="field">
                                <label><?= i::esc_attr__('Em quantos municípios a atividade vai ser realizada?') ?><span v-if="opportunity.workplan_deliveryRequireNumberOfCities" class="required">obrigatório*</span></label>
                                <input class="field__limits" v-model.number="delivery.numberOfCities" type="number" min="0">
                            </div>

                            <div v-if="opportunity.workplan_deliveryInformNumberOfNeighborhoods" class="field">
                                <label><?= i::esc_attr__('Em quantos bairros a atividade vai ser realizada?') ?><span v-if="opportunity.workplan_deliveryRequireNumberOfNeighborhoods" class="required">obrigatório*</span></label>
                                <input class="field__limits" v-model.number="delivery.numberOfNeighborhoods" type="number" min="0">
                            </div>

                            <div v-if="opportunity.workplan_deliveryInformMediationActions" class="field">
                                <label><?= i::esc_attr__('Quantas ações de mediação/formação de público estão previstas?') ?><span v-if="opportunity.workplan_deliveryRequireMediationActions" class="required">obrigatório*</span></label>
                                <input class="field__limits" v-model.number="delivery.mediationActions" type="number" min="0">
                            </div>

                            <!-- Pessoas remuneradas por função -->
                            <div v-if="opportunity.workplan_deliveryInformPaidStaffByRole" class="field">
                                <label><?= i::esc_attr__('Quantas pessoas serão remuneradas, por função?') ?><span v-if="opportunity.workplan_deliveryRequirePaidStaffByRole" class="required">obrigatório*</span></label>
                                <div v-if="!delivery.paidStaffByRole || !delivery.paidStaffByRole.length" class="field__note">
                                    <button type="button" class="button button--sm button--primary-outline" @click="addPaidStaffRole(delivery)">
                                        <?= i::__('+ Adicionar função') ?>
                                    </button>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformArtChainLink" class="field">
                                    <label><?= i::esc_attr__('Principal elo das artes acionado pela atividade') ?><span v-if="opportunity.workplan_deliveryRequireArtChainLink" class="required">obrigatório*</span></label>
                                    <select v-model="delivery.artChainLink">
                                        <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                        <option v-for="n in workplanFields.goal.delivery.artChainLink.options" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformTotalBudget" class="field">
                                    <label><?= i::esc_attr__('Qual o orçamento total da atividade?') ?><span v-if="opportunity.workplan_deliveryRequireTotalBudget" class="required">obrigatório*</span></label>
                                    <mc-currency-input v-model="delivery.totalBudget"></mc-currency-input>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformNumberOfCities" class="field">
                                    <label><?= i::esc_attr__('Em quantos municípios a atividade vai ser realizada?') ?><span v-if="opportunity.workplan_deliveryRequireNumberOfCities" class="required">obrigatório*</span></label>
                                    <input v-model.number="delivery.numberOfCities" type="number" min="0">
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformNumberOfNeighborhoods" class="field">
                                    <label><?= i::esc_attr__('Em quantos bairros a atividade vai ser realizada?') ?><span v-if="opportunity.workplan_deliveryRequireNumberOfNeighborhoods" class="required">obrigatório*</span></label>
                                    <input v-model.number="delivery.numberOfNeighborhoods" type="number" min="0">
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformMediationActions" class="field">
                                    <label><?= i::esc_attr__('Quantas ações de mediação/formação de público estão previstas?') ?><span v-if="opportunity.workplan_deliveryRequireMediationActions" class="required">obrigatório*</span></label>
                                    <input v-model.number="delivery.mediationActions" type="number" min="0">
                                </div>

                                <!-- Pessoas remuneradas por função -->
                                <div v-if="opportunity.workplan_deliveryInformPaidStaffByRole" class="field">
                                    <label><?= i::esc_attr__('Quantas pessoas serão remuneradas, por função?') ?><span v-if="opportunity.workplan_deliveryRequirePaidStaffByRole" class="required">obrigatório*</span></label>
                                    <div v-if="!delivery.paidStaffByRole || !delivery.paidStaffByRole.length" class="field__note">
                                        <button type="button" class="button button--sm button--primary-outline" @click="addPaidStaffRole(delivery)">
                                            <?= i::__('+ Adicionar função') ?>
                                        </button>
                                    </div>
                                    <div v-else class="paid-staff-list">
                                        <div v-for="(staff, index) in delivery.paidStaffByRole" :key="index" class="paid-staff-item">
                                            <div class="paid-staff-item__header">
                                                <p class="paid-staff-item__title semibold">{{ index + 1 }}ª <?= i::__('Função') ?></p>
                                                <button type="button" class="button button--delete button--icon button--sm" @click="removePaidStaffRole(delivery, index)">
                                                    <mc-icon name="trash"></mc-icon>
                                                    <?= i::__('Remover') ?>
                                                </button>
                                            </div>
                                            <div class="paid-staff-item__fields grid-12">
                                                <div class="col-6 sm:col-12 field">
                                                    <label><?= i::esc_attr__('Função') ?></label>
                                                    <select v-model="staff.role">
                                                        <option value=""><?= i::esc_attr__('Selecione a função') ?></option>
                                                        <option v-for="roleOption in workplanFields.goal.delivery.paidStaffByRole.options" :key="roleOption" :value="roleOption">{{ roleOption }}</option>
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
                                            <button type="button" class="button button--sm button--icon button--primary-outline" @click="addPaidStaffRole(delivery)">
                                                <mc-icon name="add"></mc-icon>
                                                <?= i::__('Adicionar outra função') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Receita -->
                                <div v-if="opportunity.workplan_registrationReportExpectedRenevue">
                                    <div class="field">
                                        <label>
                                            {{ `A ${getDeliveryLabelDefault} irá gerar receita?` }}
                                            <span class="required">obrigatório*</span></label>
                                        <select class="field__limits" v-model="delivery.generaterRevenue">
                                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                            <option v-for="(n, i) in workplanFields.goal.delivery.generaterRevenue.options" :key="i" :value="i">{{ n }}</option>
                                        </select>
                                    </div>

                                    <div v-if="delivery.generaterRevenue == 'true'" class="grid-12">
                                        <div class="field col-4 sm:col-12">
                                            <label><?= i::esc_attr__('Previsão Quantidade') ?><span class="required">obrigatório*</span></label>
                                            <input v-model="delivery.renevueQtd" type="number" min="0">
                                        </div>

                                        <div class="field col-4 sm:col-12">
                                            <label><?= i::esc_attr__('Previsão de valor unitário') ?><span class="required">obrigatório*</span></label>
                                            <mc-currency-input v-model="delivery.unitValueForecast"></mc-currency-input>
                                        </div>

                                        <div class="field col-4 sm:col-12">
                                            <label><?= i::esc_attr__('Previsão de valor total') ?><span class="required">obrigatório*</span></label>
                                            <input readonly :model="delivery.totalValueForecast" :value="totalValueForecastToCurrency(delivery, delivery.renevueQtd, delivery.unitValueForecast)">
                                        </div>
                                    </div>

                                    <div v-if="opportunity.workplan_deliveryInformRevenueType" class="field">
                                        <label><?= i::esc_attr__('Qual o tipo de receita previsto?') ?><span v-if="opportunity.workplan_deliveryRequireRevenueType" class="required">obrigatório*</span></label>
                                        <mc-multiselect :model="delivery.revenueType" :items="workplanFields.goal.delivery.revenueType.options" hide-filter hide-button></mc-multiselect>
                                        <mc-tag-list :tags="delivery.revenueType" :labels="workplanFields.goal.delivery.revenueType.options" classes="opportunity__background" @remove="toggleRevenueType(delivery, $event)" editable></mc-tag-list>
                                    </div>

                                    <div v-if="opportunity.workplan_deliveryInformCommercialUnits">
                                        <div class="field">
                                            <label><?= i::esc_attr__('Quantidade de unidades previstas para comercialização') ?><span v-if="opportunity.workplan_deliveryRequireCommercialUnits" class="required">obrigatório*</span></label>
                                            <input v-model.number="delivery.commercialUnits" type="number" min="0">
                                        </div>
                                        <div class="field">
                                            <label><?= i::esc_attr__('Valor unitário previsto (R$)') ?><span v-if="opportunity.workplan_deliveryRequireUnitPrice" class="required">obrigatório*</span></label>
                                            <mc-currency-input v-model="delivery.unitPrice"></mc-currency-input>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BLOCO 4 — Equipe, diversidade e acesso -->
                            <div class="form-section">
                                <h4 class="form-section__title">4. <?= i::__('Equipe, diversidade e acesso') ?></h4>
                                <p class="form-section__description"><?= i::__('Composição da equipe por gênero e raça, estratégias de inclusão, acessibilidade e práticas socioambientais.') ?></p>

                                <!-- Composição da equipe por gênero -->
                                <div v-if="opportunity.workplan_deliveryInformTeamComposition" class="field">
                                    <label><?= i::esc_attr__('Composição prevista da equipe por gênero') ?><span v-if="opportunity.workplan_deliveryRequireTeamCompositionGender" class="required">obrigatório*</span></label>
                                    <div class="grid-12">
                                        <div class="col-3 sm:col-6 field">
                                            <label><?= i::esc_attr__('Masculino') ?></label>
                                            <input v-model.number="delivery.teamCompositionGender.masculine" type="number" min="0">
                                        </div>
                                        <div class="col-3 sm:col-6 field">
                                            <label><?= i::esc_attr__('Feminino') ?></label>
                                            <input v-model.number="delivery.teamCompositionGender.feminine" type="number" min="0">
                                        </div>
                                        <div class="col-3 sm:col-6 field">
                                            <label><?= i::esc_attr__('Não-binário') ?></label>
                                            <input v-model.number="delivery.teamCompositionGender.nonBinary" type="number" min="0">
                                        </div>
                                        <div class="col-3 sm:col-6 field">
                                            <label><?= i::esc_attr__('Não declarado') ?></label>
                                            <input v-model.number="delivery.teamCompositionGender.notDeclared" type="number" min="0">
                                        </div>
                                    </div>
                                    <div class="field__note">
                                        <strong><?= i::__('Total:') ?></strong> {{ calculateGenderTotal(delivery.teamCompositionGender) }}
                                    </div>
                                </div>

                                <!-- Composição da equipe por raça/cor -->
                                <div v-if="opportunity.workplan_deliveryInformTeamComposition" class="field">
                                    <label><?= i::esc_attr__('Composição prevista da equipe por raça/cor (autodeclaração)') ?><span v-if="opportunity.workplan_deliveryRequireTeamCompositionRace" class="required">obrigatório*</span></label>
                                    <div class="grid-12">
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Branca') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.white" type="number" min="0">
                                        </div>
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Preta') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.black" type="number" min="0">
                                        </div>
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Parda') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.brown" type="number" min="0">
                                        </div>
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Indígena') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.indigenous" type="number" min="0">
                                        </div>
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Amarela') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.asian" type="number" min="0">
                                        </div>
                                        <div class="col-4 sm:col-6 field">
                                            <label><?= i::esc_attr__('Não declarado') ?></label>
                                            <input v-model.number="delivery.teamCompositionRace.notDeclared" type="number" min="0">
                                        </div>
                                    </div>
                                    <div class="field__note">
                                        <strong><?= i::__('Total:') ?></strong> {{ calculateRaceTotal(delivery.teamCompositionRace) }}
                                    </div>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformCommunityCoauthors" class="field">
                                    <label><?= i::esc_attr__('A atividade prevê envolvimento de comunidades/coletivos como coautores/coexecutores?') ?><span v-if="opportunity.workplan_deliveryRequireCommunityCoauthorsDetail" class="required">obrigatório*</span></label>
                                    <select v-model="delivery.hasCommunityCoauthors">
                                        <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                        <option v-for="(n, i) in workplanFields.goal.delivery.hasCommunityCoauthors.options" :key="i" :value="i">{{ n }}</option>
                                    </select>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformTransInclusion">
                                    <div class="field">
                                        <label><?= i::esc_attr__('A atividade prevê estratégias voltadas à promoção do acesso de pessoas Trans e Travestis?') ?></label>
                                        <select v-model="delivery.hasTransInclusionStrategy">
                                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                            <option v-for="(n, i) in workplanFields.goal.delivery.hasTransInclusionStrategy.options" :key="i" :value="i">{{ n }}</option>
                                        </select>
                                    </div>
                                    <div v-if="delivery.hasTransInclusionStrategy === 'true'" class="field">
                                        <label><?= i::esc_attr__('Quais ações foram previstas?') ?><span v-if="opportunity.workplan_deliveryRequireTransInclusionActions" class="required">obrigatório*</span></label>
                                        <textarea v-model="delivery.transInclusionActions" rows="3"></textarea>
                                    </div>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformAccessibilityPlan">
                                    <div class="field">
                                        <label><?= i::esc_attr__('A atividade prevê medidas de acessibilidade?') ?></label>
                                        <select v-model="delivery.hasAccessibilityPlan">
                                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                            <option v-for="(n, i) in workplanFields.goal.delivery.hasAccessibilityPlan.options" :key="i" :value="i">{{ n }}</option>
                                        </select>
                                    </div>
                                    <div v-if="delivery.hasAccessibilityPlan === 'true'" class="field">
                                        <label><?= i::esc_attr__('Quais medidas de acessibilidade estão previstas?') ?><span v-if="opportunity.workplan_deliveryRequireExpectedAccessibilityMeasures" class="required">obrigatório*</span></label>
                                        <mc-multiselect :model="delivery.expectedAccessibilityMeasures" :items="workplanFields.goal.delivery.expectedAccessibilityMeasures.options" hide-filter hide-button></mc-multiselect>
                                        <mc-tag-list :tags="delivery.expectedAccessibilityMeasures" :labels="workplanFields.goal.delivery.expectedAccessibilityMeasures.options" classes="opportunity__background" @remove="toggleAccessibilityMeasures(delivery, $event)" editable></mc-tag-list>
                                    </div>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformEnvironmentalPractices">
                                    <div class="field">
                                        <label><?= i::esc_attr__('A atividade prevê medidas ou práticas socioambientais?') ?></label>
                                        <select v-model="delivery.hasEnvironmentalPractices">
                                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                            <option v-for="(n, i) in workplanFields.goal.delivery.hasEnvironmentalPractices.options" :key="i" :value="i">{{ n }}</option>
                                        </select>
                                    </div>
                                    <div v-if="delivery.hasEnvironmentalPractices === 'true'" class="field">
                                        <label><?= i::esc_attr__('Quais medidas e práticas socioambientais estão previstas?') ?><span v-if="opportunity.workplan_deliveryRequireEnvironmentalPracticesDescription" class="required">obrigatório*</span></label>
                                        <textarea v-model="delivery.environmentalPracticesDescription" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- BLOCO 5 — Comunicação e registro -->
                            <div class="form-section">
                                <h4 class="form-section__title">5. <?= i::__('Comunicação e registro') ?></h4>
                                <p class="form-section__description"><?= i::__('Estratégias de divulgação, canais de comunicação, ações de inovação e tipos de documentação previstos.') ?></p>

                                <div v-if="opportunity.workplan_deliveryInformPressStrategy" class="field">
                                    <label><?= i::esc_attr__('A atividade contará com uma estratégia de relacionamento com a imprensa?') ?></label>
                                    <select v-model="delivery.hasPressStrategy">
                                        <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                        <option v-for="(n, i) in workplanFields.goal.delivery.hasPressStrategy.options" :key="i" :value="i">{{ n }}</option>
                                    </select>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformCommunicationChannels" class="field">
                                    <label><?= i::esc_attr__('Quais canais de comunicação estão previstos para difusão da atividade?') ?><span v-if="opportunity.workplan_deliveryRequireCommunicationChannels" class="required">obrigatório*</span></label>
                                    <mc-multiselect :model="delivery.communicationChannels" :items="workplanFields.goal.delivery.communicationChannels.options" hide-filter hide-button></mc-multiselect>
                                    <mc-tag-list :tags="delivery.communicationChannels" :labels="workplanFields.goal.delivery.communicationChannels.options" classes="opportunity__background" @remove="toggleCommunicationChannels(delivery, $event)" editable></mc-tag-list>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformInnovation">
                                    <div class="field">
                                        <label><?= i::esc_attr__('A atividade prevê ao menos uma ação de experimentação/inovação?') ?></label>
                                        <select v-model="delivery.hasInnovationAction">
                                            <option value=""><?= i::esc_attr__('Selecione') ?></option>
                                            <option v-for="(n, i) in workplanFields.goal.delivery.hasInnovationAction.options" :key="i" :value="i">{{ n }}</option>
                                        </select>
                                    </div>
                                    <div v-if="delivery.hasInnovationAction === 'true'" class="field">
                                        <label><?= i::esc_attr__('Quais tipos de experimentação/inovação previstos?') ?><span v-if="opportunity.workplan_deliveryRequireInnovationTypes" class="required">obrigatório*</span></label>
                                        <mc-multiselect :model="delivery.innovationTypes" :items="workplanFields.goal.delivery.innovationTypes.options" hide-filter hide-button></mc-multiselect>
                                        <mc-tag-list :tags="delivery.innovationTypes" :labels="workplanFields.goal.delivery.innovationTypes.options" classes="opportunity__background" @remove="toggleInnovationTypes(delivery, $event)" editable></mc-tag-list>
                                    </div>
                                </div>

                                <div v-if="opportunity.workplan_deliveryInformDocumentationTypes" class="field">
                                    <label><?= i::esc_attr__('Tipo de documentação que será produzida') ?><span v-if="opportunity.workplan_deliveryRequireDocumentationTypes" class="required">obrigatório*</span></label>
                                    <mc-multiselect :model="delivery.documentationTypes" :items="workplanFields.goal.delivery.documentationTypes.options" hide-filter hide-button></mc-multiselect>
                                    <mc-tag-list :tags="delivery.documentationTypes" :labels="workplanFields.goal.delivery.documentationTypes.options" classes="opportunity__background" @remove="toggleDocumentationTypes(delivery, $event)" editable></mc-tag-list>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="enableNewDelivery(goal)"  class="registration-workplan__new-delivery">
                        <button class="button button--primary-outline" id="button-registration-workplan__new-delivery" @click="newDelivery(goal)">
                            + {{ getDeliveryLabelDefault }}
                        </button>
                    </div>

                    <div class="registration-workplan__save-goal" id="registration-workplan__save-goal">
                        <button class="button button--primary" id="button-registration-workplan__save-goal" @click="save_">
                            {{ `Salvar ${getGoalLabelDefault}` }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="enableButtonNewGoal && enableNewGoal(workplan)" id="registration-workplan__new-goal" class="registration-workplan__new-goal">
            <button class="button button--primary-outline" @click="newGoal">
                + {{ getGoalLabelDefault }}
            </button>
        </div>
    </template>
</mc-card>
</div>
