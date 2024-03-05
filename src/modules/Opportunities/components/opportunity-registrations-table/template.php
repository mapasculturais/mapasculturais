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
    mc-icon
    mc-multiselect
    mc-select
    mc-status
    mc-tag-list
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
            
            <?php $this->applyTemplateHook('registration-list-actions', 'end', ['entity' => $entity]); ?>
        </div>
        <?php $this->applyTemplateHook('registration-list-actions', 'after', ['entity' => $entity]); ?>

        <div class="col-12"> 

            <entity-table type="opportunity" endpoint="findRegistrations" :query="query" :order="order" :select="select" :headers="headers" phase:="phase" required="number,options" visible="agent,status,category,consolidatedResult,attachments" @clear-filters="clearFilters" show-index>

                <template #title>
                    <h5>
                        <strong><?= i::__("Clique no número de uma inscrição para conferir todas as avaliações realizadas.") ?></strong>
                        <?= i::__("Após conferir, você pode alterar os status das inscrições de maneira coletiva ou individual e aplicar os resultados das avaliações.") ?>
                    </h5>
                </template>
                
                <?php $this->applyTemplateHook('registration-list-actions-entity-table', 'before', ['entity' => $entity]); ?>
                <template #actions="{entities,filters}">
                    <h4 class="bold"><?= i::__('Ações:') ?></h4>
                    <div class="opportunity-payment-table__actions">
                        <div class="opportunity-payment-table__actions grid-12">
                            <?php $this->applyTemplateHook('registration-list-actions-entity-table', 'begin', ['entity' => $entity]); ?>
                                <mc-link :entity="phase" route="reportDrafts" class="button button--primarylight button--icon button--large col-4"><?= i::__("Baixar rascunho") ?> <mc-icon name="download"></mc-icon></mc-link>
                                <mc-link :entity="phase" route="report" class="button button--primarylight button--icon button--large col-4"><?= i::__("Baixar lista de inscrições") ?> <mc-icon name="download"></mc-icon></mc-link>
                            <?php $this->applyTemplateHook('registration-list-actions-entity-table', 'end', ['entity' => $entity]); ?>
                        </div>
                    </div>
                </template>
                <?php $this->applyTemplateHook('registration-list-actions-entity-table', 'after', ['entity' => $entity]); ?>

                <template #filters="{entities,filters}">
                    <div class="grid-12">
                        <mc-select v-if="statusEvaluationResult" class="col-5" :default-value="selectedAvaliation" @change-option="filterAvaliation($event,entities)" placeholder="<?= i::__("Resultado de avaliação") ?>">
                            <template #empetyOption>
                                <?= i::__("Resultado de avaliação") ?>
                            </template>
                            <option v-for="(item,index) in statusEvaluationResult" :value="index">{{item}}</option>
                        </mc-select>
                        <mc-select class="col-4" :default-value="selectedStatus" @change-option="filterByStatus($event,entities)" placeholder="<?= i::__("Status de inscrição") ?>">
                            <option v-for="item in statusDict" :value="item.value">{{item.label}}</option>
                        </mc-select>
                        <mc-select v-if="statusCategory.length > 0" class="col-3" :default-value="selectedCategory" @change-option="filterByCategory($event,entities)" placeholder="<?= i::__("Categoria") ?>">
                            <option v-for="item in statusCategory" :value="item">{{item}}</option>
                        </mc-select>
                    </div>
                    
                    <select v-model="order" @change="entities.refresh()">
                        <option value="status DESC,consolidatedResult AS FLOAT DESC"><?= i::__('Por status descendente') ?></option>
                        <option value="status ASC,consolidatedResult AS FLOAT ASC"><?= i::__('Por status ascendente') ?></option>
                        <option value="consolidatedResult AS FLOAT DESC"><?= i::__('Por resultado das avaliações') ?></option>
                        <option value="@quota"><?= i::__('Por resultado das avaliações CONSIDERANDO COTAS') ?></option>
                        <option value="createTimestamp ASC"><?= i::__('Mais antigas primeiro') ?></option>
                        <option value="createTimestamp DESC"><?= i::__('Mais recentes primeiro') ?></option>
                        <option value="sentTimestamp ASC"><?= i::__('Enviadas a mais tempo primeiro') ?></option>
                        <option value="sentTimestamp DESC"><?= i::__('Enviadas a menos tempo primeiro') ?></option>
                    </select>
                </template>

                <template #attachments={entity}>
                    <a v-if="entity.files?.zipArchive?.url" :href="entity.files?.zipArchive?.url">Anexo</a>
                </template>

                <template #status="{entity}">
                    <mc-select :default-value="entity.status" @change-option="setStatus($event, entity)">
                        <mc-status v-for="item in statusDict" :value="item.value" :status-name="item.label"></mc-status>
                    </mc-select>
                </template>

                <template #consolidatedResult="{entity}"> 
                    {{consolidatedResultToString(entity)}}
                </template>

                <template #number="{entity}">
                    <a :href="entity.singleUrl">{{entity.number}}</a>
                </template>

            </entity-table>
        </div>
    </template>
</div>