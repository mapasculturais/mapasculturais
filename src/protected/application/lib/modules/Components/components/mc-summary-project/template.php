<?php

use MapasCulturais\i;

$this->import('
    mc-card
    mc-icon
    
');
?>
<mc-card v-if="projectName && canSee('projectName')">
    <template #:title>
        <div>
            <div>
                <h4><strong><?= i::__("Nome do projeto") ?> </strong></h4>
            </div>
            <div v-if="projectName !== 0">
                <mc-icon name="project"></mc-icon>
                <span v-if="projectName">{{projectName}}</span>
                <span v-if="!projectName"><?= i::__("Nome do projeto não informado") ?></span>
            </div>
        </div>
    </template>
</mc-card>