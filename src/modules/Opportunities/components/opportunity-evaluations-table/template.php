<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    mc-export-spreadsheet
    mc-status
    entity-table
    mc-confirm-button
')
?>
<div :class="['opportunity-evaluations-table', 'grid-12', classes]">

    <template v-if="!isFuture()">
        <div class="col-12">
            <entity-table controller="opportunity" show-index :select="defaultSelect" :raw-processor="rawProcessor" :identifier="identifier" endpoint="findEvaluations" type="registration" :headers="headers" :phase="phase" :visible="['agent', 'number', 'result', 'status', 'evaluator', 'coletivo', 'goalStatuses']" :query="query" :limit="100" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :filtersDictComplement="filtersDictComplement"> 
                <template #title>
                    <h2 v-if="isPast()"><?= i::__("As avaliações já estão encerradas") ?></h2>
                    <h2 v-if="isHappening()"><?= i::__("As avaliações estão em andamento") ?></h2>
                    <h2 v-if="isFuture()"><?= i::__("As avaliações ainda não iniciaram") ?></h2>
                </template>

                <template #searchKeyword='{query}'>
                    <textarea ref="search" v-model="this.query['registration:@keyword']" rows="1" placeholder="<?= i::__('Pesquisa por palavra-chave separados por ;') ?>" class="entity-table__search-input"></textarea>
                    
                    <button @click="keyword(entities)" class="entity-table__search-button">
                        <mc-icon name="search"></mc-icon>
                    </button>
                </template>

                <template #actions="{entities,filters}">
                    <div class="opportunity-evaluations-table__actions">
                        <h4 class="bold"><?= i::__('Ações:') ?></h4>
                        
                        <div class="opportunity-evaluations-table__actions">
                            <div>
                                <mc-link 
                                    :entity="phase.opportunity" 
                                    route="sendEvaluations" 
                                    :class="{
                                                'button button--primary-outline': canSee('sendUserEvaluations') && user == global.auth.user.id,
                                                'button disabled button--primary-outline': !(canSee('sendUserEvaluations') && user == global.auth.user.id)
                                            }"
                                    :param="phase.opportunity.id"><?= i::__("Enviar avaliações") ?></mc-link>
                            </div>
                            <div v-if="user == 'all'">
                                <mc-export-spreadsheet :owner="phase.opportunity" endpoint="evaluations" :params="{entityType: 'registrationEvaluation', '@select': 'projectName,category,owner.{name},number,score,proponentType,range,eligible,goalStatuses,user,result,status,evaluationData', query}" group="evaluations-spreadsheets"></mc-export-spreadsheet>
                            </div>
                        </div>
                    </div>
                </template>

                <template #filters="{entities,filters}">
                    <div class="opportunity-evaluations-table__filters grid-12">

                        <div v-if="hasControl" :class="hasControl ? 'col-3' : 'col-4'">
                            <mc-select :options="evaluationsFiltersOptions" v-model:default-value="evaluatiorFilter" @change-option="filterByEvaluator($event, entities)" placeholder="<?= i::__("Avaliador") ?>" hide-filters></mc-select>
                        </div>

                        <div :class="hasControl ? 'col-3' : 'col-4'">
                            <mc-select :options="status" v-model:default-value="selectedStatus" @change-option="filterByStatus($event, entities)" placeholder="<?= i::__("Estado da avaliação") ?>" hide-filters></mc-select>
                        </div>

                        <div class="field" :class="hasControl ? 'col-3' : 'col-4'">
                            <datepicker 
                                teleport
                                v-model="firstDate" 
                                :format="dateFormat" 
                                :locale="locale" 
                                :text-input-options="{'format': 'dd/MM/yyyy'}" 
                                :weekStart="0" 
                                :enable-time-picker=false
                                text-input autoApply>
                                <template #dp-input="{ value, onBlur, onInput, onEnter, onTab, onClear }">
                                    <input type="text" data-maska="##/##/####" :value="value" maxlength="10" @input="onChange($event, onInput, entities)" @blur="onBlur" @keydown.enter="onEnter" @keydown.tab="onTab" v-maska placeholder="Data inicial">
                                </template>
                            </datepicker>
                        </div>

                        <div class="field" :class="hasControl ? 'col-3' : 'col-4'">
                            <datepicker 
                                teleport
                                v-model="lastDate" 
                                :format="dateFormat" 
                                :locale="locale" 
                                :text-input-options="{'format': 'dd/MM/yyyy'}" 
                                :weekStart="0" 
                                :enable-time-picker=false
                                text-input autoApply>
                                <template #dp-input="{ value, onBlur, onInput, onEnter, onTab, onClear }">
                                    <input type="text" data-maska="##/##/####" :value="value" maxlength="10" @input="onChange($event, onInput, entities)" @blur="onBlur" @keydown.enter="onEnter" @keydown.tab="onTab" v-maska placeholder="Data final">
                                </template>
                            </datepicker>
                        </div>
                    </div>
                </template>

                <template #number="{entity}">
                    <a :href="createUrl(entity)">{{entity.number}}</a>
                </template>

                <template #result="{entity}">
                    {{getResultString(entity.evaluation?.resultString)}}
                </template>

                <template #coletivo="{entity}">
                    <span v-if="entity.agentsData?.coletivo?.name">{{entity.agentsData?.coletivo?.name}}</span>
                    <span v-if="!entity.agentsData?.coletivo?.name"><?= i::__("Não informado") ?></span>
                </template>

                <template #status="{entity}">
                    <mc-status :status-name="getStatus(entity.evaluation?.status)"></mc-status>
                </template>

                <template #goalStatuses="{entity}">
                    <a v-if="entity.goalStatuses" :href="entity.singleUrl + '#ficha'" class="entity-table__goals">{{entity.goalStatuses['10']}}/{{entity.goalStatuses.numGoals}} <?= i::__('concluídas') ?></a>
                </template>

                <template #delete="{entity, refresh}">
                    <mc-confirm-button 
                        v-if="hasControl && entity.evaluation && (entity.evaluation.status === 0 || entity.evaluation.status === 1 || entity.evaluation.status === 2)"
                        @confirm="deleteEvaluation(entity, refresh)">
                        <template #button="modal">
                            <button 
                                @click="modal.open()"
                                class="button button--icon button--text-danger button--sm"
                                v-tooltip="'<?= i::__('Excluir avaliação') ?>'">
                                <mc-icon name="trash"></mc-icon>
                            </button>
                        </template>
                        <template #message="message">
                            <?= i::__('Tem certeza que deseja excluir esta avaliação?') ?>
                        </template>
                    </mc-confirm-button>
                </template>

                <template #icon-text="popover">
                    <button class="agent-table__button button button--icon button--primary button--sm">
                        <?= i::__('Selecionar dados') ?>
                    </button>
                </template>
            </entity-table>
        </div>
    </template>
</div>