<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-icon    
');
?>
<mc-card v-if="opportunity && canSee('agentsSummary')" :class="classes">
    <template #title>
        <h3><?= i::__("Nome do projeto") ?></h3>
    </template>
    <template #content>
        <div class="mc-linked-entity">
            <div class="mc-linked-entity__img">
                <mc-icon name="project"></mc-icon>
            </div>
            <h5 v-if="projectName">{{projectName}}</h5>
            <h5 v-if="!projectName"><?= i::__("Nome do projeto não informado") ?></h5>
        </div>
    </template>
</mc-card>
    