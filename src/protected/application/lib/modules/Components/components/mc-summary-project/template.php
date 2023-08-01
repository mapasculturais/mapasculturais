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
<mc-card v-if="projectName && canSee('projectName')">
    
    <div class="mc-summary-project">
        <h4><strong><?= i::__("Nome do projeto") ?> </strong></h4>
        <div v-if="projectName !== 0">
            <mc-icon name="project"></mc-icon>
            <span v-if="projectName">{{projectName}}</span>
            <span v-if="!projectName"><?= i::__("Nome do projeto não informado") ?></span>
        </div>
    </div>
</mc-card>