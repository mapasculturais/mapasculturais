<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    mc-status
    entity-table
')
?>
<div :class="['grid-12', classes]">

    <template v-if="!isFuture()">
        <div class="col-12">
            <entity-table controller="opportunity" :raw-processor="rawProcessor" endpoint="findEvaluations" type="registration" :headers="headers" :phase="phase" :visible="['agent', 'number', 'result', 'status']" :query="query" :limit="100" @clear-filters="clearFilters" @remove-filter="removeFilter($event)"> 
                <template #title>
                    <h2 v-if="isPast()"><?= i::__("As avaliações já estão encerradas") ?></h2>
                    <h2 v-if="isHappening()"><?= i::__("As avaliações estão em andamento") ?></h2>
                    <h2 v-if="isFuture()"><?= i::__("As avaliações ainda não iniciaram") ?></h2>
                </template>

                <template #actions="{entities,filters}">
                    <div class="opportunity-evaluations-table__actions">
                        <h4 class="bold"><?= i::__('Ações:') ?></h4>
                        
                        <div class="opportunity-evaluations-table__actions">
                            <div v-if="canSee('sendUserEvaluations') && user == global.auth.user.id">
                                <mc-link :entity="phase.opportunity" route="sendEvaluations" class="button button--primary-outline" :param="phase.opportunity.id"><?= i::__("Enviar avaliações") ?></mc-link>
                            </div>
                            <div v-if="user == 'all'">
                                <mc-link :entity="phase.opportunity" route="reportEvaluations" class="button button--secondarylight" :param="phase.opportunity.id"><?= i::__("Baixar lista de avaliações") ?></mc-link>
                            </div>
                        </div>
                    </div>
                </template>

                <template #filters="{entities,filters}">
                    <div class="opportunity-evaluations-table__filters grid-12">
                        <div class="col-4">
                            <mc-select :options="status" :default-value="selectedStatus" @change-option="filterByStatus($event, entities)" placeholder="<?= i::__("Estado da avaliação") ?>" hide-filters></mc-select>
                        </div>

                        <div class="field col-4">
                            <datepicker 
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

                        <div class="field col-4">
                            <datepicker 
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

                <template #result="{entity}">
                    {{getResultString(entity.evaluation?.resultString)}}
                </template>

                <template #status="{entity}">
                    <mc-status :status-name="getStatus(entity.evaluation?.status)"></mc-status>
                </template>
            </entity-table>
        </div>
    </template>
</div>