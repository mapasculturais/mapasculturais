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
            <div class="opportunity-registration-table__filter">
                <div class="opportunity-registration-table__search-key">
                    <input type="text" placeholder="<?= i::__('Busque pelo número de inscrição, status, parecer técnico?') ?>" class="opportunity-registration-table__search-input" />
                    <button @click="search()" class="opportunity-registration-table__search-button">
                        <mc-icon name="search"></mc-icon>
                    </button>
                </div>
                <div class="opportunity-registration-table__search-fields">
                    <h4 class="bold"><?= i::__('Filtrar:')?></h4>
                    <div class="field"><input type="number"/></div>
                    <div class="field">
                        <select>
                            <option value=""><span><?= i::__('Operador:')?></span></option>
                        </select>
                    </div>
                    <div class="field"><input type="number"></div>
                    <div class="field">
                        <select>
                            <option value=""><span><?= i::__('Status de inscrição:')?></span></option>
                        </select>
                    </div>
                    <div class="field">
                        <select>
                            <option value=""><span><?= i::__('Exequibilidade (R$)')?></span></option>
                        </select>
                    </div>
                </div>
                <!-- <mc-tag-list class="opportunity-registration-table__taglists"></mc-tag-list> -->
                <div class="field opportunity-registration-table__select-tag">
                    <select>
                        <option value=""><span><?= i::__('Colunas habilitadas na tabela:')?></span></option>
                    </select>
                </div>
            </div>
            <entity-table :entity="phase" :headers="headers" :items="items"></entity-table>
            <v1-embed-tool route="registrationmanager" :id="phase.id" min-height="600px"></v1-embed-tool>
        </div>
    </template>
</div>