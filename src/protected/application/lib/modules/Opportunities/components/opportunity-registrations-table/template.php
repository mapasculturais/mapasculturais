<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    mapas-card
    v1-embed-tool
')
?>
<div class="grid-12">
    <div class="col-6">
        <h2 v-if="phase.publishedRegistrations"><?= i::__("Os resultados já foram publicados") ?></h2>
        <h2 v-if="!phase.publishedRegistrations && isPast()"><?= i::__("As inscrições já estão encerradas") ?></h2>
        <h2 v-if="isHappening()"><?= i::__("As inscrições estão em andamento") ?></h2>
        <h2 v-if="isFuture()"><?= i::__("As inscrições ainda não iniciaram") ?></h2>
    </div>
    <template v-if="!isFuture()">
        <div class="col-3 text-right">
            <mc-link :entity="phase" route="reportDrafts" class="button button--secondarylight"><?= i::__("Baixar rascunho") ?></mc-link>
        </div>
        <div class="col-3">
            <mc-link :entity="phase" route="report" class="button button--secondarylight"><?= i::__("Baixar lista de inscrições") ?></mc-link>
        </div>
        <div class="col-12">
            <h5><?= i::__("Visualize a lista de pessoas inscritas neste edital. E acompanhe os projetos criados para os Agentes Culturais aceitos.") ?></h5>
        </div>
        <div class="col-12">
            <v1-embed-tool route="registrationmanager" :id="phase.id"></v1-embed-tool>
        </div>
    </template>
</div>