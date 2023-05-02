<?php

use MapasCulturais\i;

$this->import('
    mapas-card
    mc-icon
    
');
?>
<mapas-card v-if="canSee('projectName')">
    <template v-slot:title>
        <div>
            <div>
                <h4><strong><?= i::__("Nome do projeto") ?> </strong></h4>
            </div>
            <div>
                <mc-icon name="project"></mc-icon>
                <span v-if="projectName">{{projectName}}</span>
                <span v-if="!projectName && opportunity.useAgentRelationInstituicao"><?= i::__("Nomedo projeto não informado") ?></span>
            </div>
        </div>
    </template>
</mapas-card>