<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tag-list
    mc-icon
    mc-modal
');
?>
<div class="col-12 opportunity-enable-workplan" v-if="entity.isFirstPhase">
    <div class="col-12 disabled-workplan" v-if="entity.enableWorkplan">
        <mc-confirm-button @confirm="actionDisabledWorkplan()">
            <template #button="{open}">
                <button class="button button--delete button--icon button--sm" @click="open()">
                    <mc-icon class="icon-workplandisabled" name="trash"></mc-icon> 
                </button>
            </template>
            <template #message="message">
                <h3>{{ `Você deseja desativar o ${getWorkplanLabelDefault}?` }}</h3><br>
                <p>
                    {{ `Todas as configurações e alterações realizadas serão perdidas definitivamente.` }}
                </p>
            </template>
        </mc-confirm-button>
    </div>
    <h4 class="bold opportunity-enable-workplan__title">
        {{ getWorkplanLabelDefault }}
   
        <mc-modal title="Editar rótulo" v-if="entity.enableWorkplan">
            <h4>Rótulo: plano de metas</h4>
            <div class="field col-12">
                <div class="field__group">
                    <label class="field__group">
                        {{ `Escreva um rótulo personalizado para "${getWorkplanLabelDefault}"` }}
                    </label>
                    <input type="text" v-model="entity.workplanLabelDefault">
                </div>
            </div>

            <template #actions="modal">
                <button class="button" @click="modal.close()">Cancelar</button>
                <button class="button button--primary" @click="changeLabels(modal)">Alterar</button>
            </template>

            <template #button="modal">
                <a  href="#"  @click="modal.open()"><mc-icon class="edit" name="edit"></mc-icon></a>
            </template>
        </mc-modal>
        
    </h4>
    <h6>{{ `Configurar parâmetros do ${getWorkplanLabelDefault}` }}</h6>
    <div class="opportunity-enable-workplan__content">
        <div class="col-12" v-if="!entity.enableWorkplan">
            <button class="button button--primary button--icon" @click="actionEnabledWorkplan()">
                <mc-icon name="add"></mc-icon><label>{{ `Configurar ${getWorkplanLabelDefault}` }}</label>
            </button>
        </div>

        <div v-if="entity.enableWorkplan">
            <div id="data-project" class="opportunity-enable-workplan__block col-12">
                <h4 class="bold opportunity-enable-workplan__title"><?= i::__('Duração do projeto') ?></h4>
                <div class="field col-12">
                    <div class="field__group">
                        <label class="field__checkbox">
                            <input type="checkbox" v-model="entity.workplan_dataProjectlimitMaximumDurationOfProjects" @click="autoSave()" /><?= i::__("Limitar a duração máxima dos projetos") ?>
                        </label>
                    </div>

                    <div class="field__group">
                        <label class="field__group">
                            <?php i::_e('Duração máxima (meses):') ?>
                        </label>
                        <input type="number" class="field__limits" min="1" :disabled="!entity.workplan_dataProjectlimitMaximumDurationOfProjects" v-model="entity.workplan_dataProjectmaximumDurationInMonths" @change="autoSave()">
                    </div>
                </div>
            </div>
            <div id="data-metas" class="opportunity-enable-workplan__block col-12">
                <h4 class="bold opportunity-enable-workplan__title">
                    {{ getGoalLabelDefault }}  
                    <mc-modal title="Editar rótulo">
                        <h4>Rótulo: Meta</h4>
                        <div class="field col-12">
                            <div class="field__group">
                                <label class="field__group">
                                    {{ `Escreva um rótulo personalizado para "${getGoalLabelDefault}"` }}
                                </label>
                                <input type="text" v-model="entity.goalLabelDefault">
                            </div>
                        </div>

                        <template #actions="modal">
                            <button class="button" @click="modal.close()">Cancelar</button>
                            <button class="button button--primary" @click="changeLabels(modal)">Alterar</button>
                        </template>

                        <template #button="modal">
                            <a href="#" @click="modal.open()"><mc-icon class="edit" name="edit"></mc-icon></a>
                        </template>
                    </mc-modal>  
                </h4>
                <h6>
                    {{ `As ${getGoalLabelDefault} são constituídas por uma ou mais ${getDeliveryLabelDefault}` }}
                </h6>
                <div class="field col-12">
                    <div class="field__group">
                        <label class="field__checkbox">
                            <input type="checkbox" v-model="entity.workplan_metaInformTheStageOfCulturalMaking" @click="autoSave()" /><?= i::__("Informar a etapa do fazer cultural") ?>
                        </label>
                    </div>

                    <div class="field__group">
                        <label class="field__checkbox">
                            <input type="checkbox" v-model="entity.workplan_metaLimitNumberOfGoals" @click="autoSave()" />
                            {{ `Limitar número de ${getGoalLabelDefault}` }}
                        </label>
                    </div>

                    <div class="field__group">
                        <label>
                            {{ `Limitar número de ${getGoalLabelDefault}:` }}
                        </label>
                        <input type="number" class="field__limits" min="1" :disabled="!entity.workplan_metaLimitNumberOfGoals" v-model="entity.workplan_metaMaximumNumberOfGoals" @change="autoSave()">
                    </div>
                </div>
            </div>
            <div id="data-delivery" class="opportunity-enable-workplan__block  col-12">
                <h4 class="bold opportunity-enable-workplan__title">
                    {{ `${getDeliveryLabelDefault}` }}
                    <mc-modal title="Editar rótulo">
                        <h4>Rótulo: Entrega</h4>
                        <div class="field col-12">
                            <div class="field__group">
                                <label class="field__group">
                                    {{ `Escreva um rótulo personalizado para "${getDeliveryLabelDefault}"` }}
                                </label>
                                <input type="text" v-model="entity.deliveryLabelDefault">
                            </div>
                        </div>

                        <template #actions="modal">
                            <button class="button" @click="modal.close()">Cancelar</button>
                            <button class="button button--primary" @click="changeLabels(modal)">Alterar</button>
                        </template>

                        <template #button="modal">
                            <a href="#" @click="modal.open()"><mc-icon class="edit" name="edit"></mc-icon></a>
                        </template>
                    </mc-modal>  
                </h4>
                <h6>
                    {{ `Entregas são os produtos, serviços ou atividades culturais resultantes do projeto fomentado.` }}
                </h6>
                <div class="field col-12">
                    <div class="field__group">
                        <label class="field__checkbox">
                            <input type="checkbox" v-model="entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals" @click="autoSave()" />
                            {{ `Informar as ${getDeliveryLabelDefault} vinculadas as ${getGoalLabelDefault}` }}
                        </label>
                    </div>

                    <div v-if="entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals">                    
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_deliveryLimitNumberOfDeliveries" @click="autoSave()" />
                                {{ `Limitar número de ${getDeliveryLabelDefault}` }}
                            </label>
                        </div>

                        <div class="field__group mt">
                            <label class="field__group">
                                {{ `Número máximo de ${getDeliveryLabelDefault}` }}
                            </label>
                            <input type="number" class="field__limits" min="1" :disabled="!entity.workplan_deliveryLimitNumberOfDeliveries" v-model="entity.workplan_deliveryMaximumNumberOfDeliveries" @change="autoSave()">
                        </div>
                    </div>
                </div>

                <div v-if="entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals" id="data-registration" class="opportunity-enable-workplan__block  col-12">
                    <h4 class="bold opportunity-enable-workplan__title"><?= i::__('Inscrição') ?></h4>
                    <h6><?= $this->text('header-description', i::__('As informações que forem marcadas abaixo serão exigidas dos agentes no momento de inscrição na oportunidade.')) ?></h6>
                    <div class="field col-12">
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_registrationReportTheNumberOfParticipants" @click="autoSave()" /><?= i::__("Informar a quantidade estimada de público") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_registrationInformCulturalArtisticSegment" @click="autoSave()" /><?= i::__("Informar segmento artístico-cultural") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_registrationReportExpectedRenevue" @click="autoSave()" /><?= i::__("Informar receita prevista") ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div v-if="entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals" id="data-monitoring" class="opportunity-enable-workplan__block  col-12">
                    <h4 class="bold opportunity-enable-workplan__title"><?= i::__('Monitoramento') ?></h4>
                    <h6><?= $this->text('header-description', i::__('As informações marcadas abaixo serão obrigatórias no monitoramento da oportunidade.')) ?></h6>
                    <div class="field col-12">
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformTheFormOfAvailability" @click="autoSave()" /><?= i::__("Informar forma de disponibilização") ?>
                            </label>
                        </div>

                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformAccessibilityMeasures" @click="autoSave()" /><?= i::__("Informar as medidas de acessibilidade") ?>
                            </label>
                        </div>

                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringProvideTheProfileOfParticipants" @click="autoSave()" /><?= i::__("Informar o perfil do público") ?>
                            </label>
                        </div>

                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformThePriorityAudience" @click="autoSave()" /><?= i::__("Informar o público prioritário") ?>
                            </label>
                        </div>

                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringReportExecutedRevenue" @click="autoSave()" /><?= i::__("Informar receita executada") ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="config-phase__line col-12"></div>