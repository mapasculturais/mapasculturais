<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-table
    mc-card
    mc-tag-list
    mc-icon
    v1-embed-tool
');

$entity = $this->controller->requestedEntity;
?>
<div class="opportunity-registration-table grid-12">
    <div class="col-12">
        <h2 v-if="phase.publishedRegistrations"><?= i::__("Os resultados já foram publicados") ?></h2>
        <h2 v-if="!phase.publishedRegistrations && isPast()"><?= i::__("As inscrições já estão encerradas") ?></h2>
        <h2 v-if="isHappening()"><?= i::__("As inscrições estão em andamento") ?></h2>
        <h2 v-if="isFuture()"><?= i::__("As inscrições ainda não iniciaram") ?></h2>
    </div>
    <template v-if="!isFuture()">
        <?php $this->applyTemplateHook('registration-list-actions', 'before', ['entity' => $entity]); ?>
            <div class="col-12 opportunity-registration-table__buttons">
                <?php $this->applyTemplateHook('registration-list-actions', 'begin', ['entity' => $entity]); ?>
                <div class="col-4 text-right">
                    <mc-link :entity="phase" route="reportDrafts" class="button button--secondarylight button--md"><label class="down-draft"><?= i::__("Baixar rascunho") ?></label></mc-link>
                </div>
                <div class="col-4">
                    <mc-link :entity="phase" route="report" class="button button--secondarylight button--md"><label class="down-list"><?= i::__("Baixar lista de inscrições") ?></label></mc-link>
                </div>
                <?php $this->applyTemplateHook('registration-list-actions', 'end', ['entity' => $entity]); ?>
            </div>
            <?php $this->applyTemplateHook('registration-list-actions', 'after', ['entity' => $entity]); ?>
            <div class="col-12">
                <h5>
                    <strong><?= i::__("Você pode mudar o status individualmente de acordo com as notas dos participantes, ou basta selecionar várias inscrições para alterar o status em mais de uma inscrição") ?></strong>
                    <?= i::__("Utilize o filtro numérico para visualizar inscrições com notas dentro de um intervalo específico..") ?>
                </h5>
            </div>
        <div class="col-12"> 
            <entity-table type="registration" :query="query" :select="select" :headers="headers" phase:="phase" required="number,options" visible="agent,status,category,consolidatedResult" @clear-filters="clearFilters">
                <template #filters="{entities,filters}">
                    <mc-select :default-value="selectedAvaliation" @change-option="filterAvaliation($event,entities)">
                        <option v-for="(item,index) in statusEvaluationResult" :value="index">{{item}}</option>
                    </mc-select>
                    <mc-select :default-value="selectedStatus" @change-option="filterByStatus($event,entities)">
                        <option v-for="item in statusDict" :value="item.value">{{item.label}}</option>
                    </mc-select>
                    <mc-select :default-value="selectedCategory" @change-option="filterByCategory($event,entities)">
                        <option v-for="item in statusCategory" :value="item">{{item}}</option>
                    </mc-select>
                </template>
                <template #status="{entity}">
                  <select v-model="entity.status" @change="alterStatus(entity)">
                        <template v-for="item in statusDict">
                            <option :value="item.value">{{item.label}}</option>
                        </template>
                  </select>
                </template>
                <template #consolidatedResult="{entity}">
                    {{consolidatedResultToString(entity)}}
                </template>
                <template #number="{entity}">
                    <a :href="entity.singleUrl">{{entity.number}}</a>
                </template>
                <template #options="{entity}">
                    <a :href="entity.singleUrl" class="button button--primary"><?= i::__("Conferir inscrição")?></a>
                </template>
            </entity-table>
        </div>
    </template>
</div>