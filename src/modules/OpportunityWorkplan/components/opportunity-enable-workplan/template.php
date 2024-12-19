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
<div class="opportunity-enable-workplan">
    <h4 class="bold opportunity-enable-workplan__title">
        {{ getWorkplanLabelDefault }}
   
        <mc-modal title="Editar rótulo">
            <h4>Rótulo: plano de trabalho</h4>
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
                <a href="#" @click="modal.open()"><mc-icon class="edit" name="edit"></mc-icon></a>
            </template>
        </mc-modal>
        
    </h4>
    <h6>{{ `Configurar parâmetros do ${getWorkplanLabelDefault}` }}</h6>
    <div class="opportunity-enable-workplan__content">
        <div class="field col-12">
            <div class="field__group">
                <label class="field__checkbox">
                    <input type="checkbox" v-model="entity.enableWorkplan" @click="autoSave()" />
                    {{ `Habilitar ${getWorkplanLabelDefault}` }}
                </label>
            </div>
        </div>

        <div v-if="entity.enableWorkplan">
            <div id="data-project" class="opportunity-enable-workplan__block col-12">
                <h4 class="bold opportunity-enable-workplan__title"><?= i::__('Dados do projeto') ?></h4>
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
                        <input type="number" :disabled="!entity.workplan_dataProjectlimitMaximumDurationOfProjects" v-model="entity.workplan_dataProjectmaximumDurationInMonths" @change="autoSave()">
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
                            <input type="checkbox" v-model="entity.workplan_metaInformTheValueGoals" @click="autoSave()" />
                            {{ `Informar o valor da ${getGoalLabelDefault}` }}
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
                        <input type="number" :disabled="!entity.workplan_metaLimitNumberOfGoals" v-model="entity.workplan_metaMaximumNumberOfGoals" @change="autoSave()">
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
                    {{ `As ${getDeliveryLabelDefault} são evidências (arquivos ou links) que comprovam a conclusão das ${getGoalLabelDefault}.` }}
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

                        <div class="field__group">
                            <label>
                                {{ `Número máximo de ${getDeliveryLabelDefault}` }}
                            </label>
                            <input type="number" :disabled="!entity.workplan_deliveryLimitNumberOfDeliveries" v-model="entity.workplan_deliveryMaximumNumberOfDeliveries" @change="autoSave()">
                        </div>

                        <div class="field">
                            <label> 
                                {{ `Informar tipo de ${getDeliveryLabelDefault}` }}
                            </label>
                            <mc-multiselect :model="entity.workplan_monitoringInformDeliveryType" title="<?php i::_e('Selecione as áreas de atuação') ?>" :items="workplan_monitoringInformDeliveryTypeList" hide-filter hide-button>
                                <template #default="{setFilter, popover}">
                                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="Selecione as opções">
                                </template>
                            </mc-multiselect>
                            <mc-tag-list editable :tags="entity.workplan_monitoringInformDeliveryType" classes="opportunity__background opportunity__color"></mc-tag-list>
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
                                <input type="checkbox" v-model="entity.workplan_registrationInformCulturalArtisticSegment" @click="autoSave()" /><?= i::__("Informar segmento artístico cultural") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_registrationReportExpectedRenevue" @click="autoSave()" /><?= i::__("Informar receita prevista") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_registrationInformActionPAAR" @click="autoSave()" /><?= i::__("Informar a ação orçamentária (PAAR)") ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div v-if="entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals" id="data-monitoring" class="opportunity-enable-workplan__block  col-12">
                    <h4 class="bold opportunity-enable-workplan__title"><?= i::__('Monitoramento') ?></h4>
                    <h6><?= $this->text('header-description', i::__('As informações que forem marcadas abaixo serão exigidas dos agentes no momento de monitoramento da oportunidade.')) ?></h6>
                    <div class="field col-12">
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformTheFormOfAvailability" @click="autoSave()" /><?= i::__("Informar forma de disponibilização") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringEnterDeliverySubtype" @click="autoSave()" />
                                {{ `Informar subtipo de ${getDeliveryLabelDefault}` }}
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformAccessibilityMeasures" @click="autoSave()" /><?= i::__("Informar as medidas de acessibilidade") ?>
                            </label>
                        </div>
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.workplan_monitoringInformThePriorityTerritories" @click="autoSave()" /><?= i::__("Informar os territórios prioritários") ?>
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