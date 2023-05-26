<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    v1-embed-tool
')
?>
<div class="grid-12">
    <div class="col-6">
        <h2 v-if="isPast()"><?= i::__("As avaliações já estão encerradas") ?></h2>
        <h2 v-if="isHappening()"><?= i::__("As avaliações estão em andamento") ?></h2>
        <h2 v-if="isFuture()"><?= i::__("As avaliações ainda não iniciaram") ?></h2>
    </div>

    <template v-if="!isFuture()">
        <div class="col-3">
            <mc-link :entity="phase.opportunity" route="sendEvaluations" class="button button--primary-outline" :param="phase.opportunity.id"><?= i::__("Enviar avaliações") ?></mc-link>
        </div>
        <div class="col-3">
            <mc-link v-if="isAdmi()" :entity="phase.opportunity" route="reportEvaluations" class="button button--secondarylight" :param="phase.opportunity.id"><?= i::__("Baixar lista de avaliações") ?></mc-link>
        </div>

        <div class="col-12">
            <v1-embed-tool route="evaluationlist" :id="phase.opportunity.id"></v1-embed-tool>
        </div>
    </template>
</div>