<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    mc-card
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
                <h5><?= i::__("Visualize a lista de pessoas inscritas neste edital. E acompanhe os projetos criados para os Agentes Culturais aceitos.") ?></h5>
            </div>
        <div class="col-12"> 
            <v1-embed-tool route="registrationmanager" :id="phase.id" min-height="600px"></v1-embed-tool>
        </div>
    </template>
</div>