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
            <entity-table controller="opportunity" :raw-processor="rawProcessor" endpoint="findEvaluations" type="registration" :headers="headers" :phase="phase" :visible="['agent', 'number', 'result', 'status']" :query="query" :limit="100" hide-filters> 
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