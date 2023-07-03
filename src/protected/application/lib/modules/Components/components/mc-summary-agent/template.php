<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon    
');
?>
<div v-if="opportunity && canSee('agentsSummary')" class="mc-summary-agent">
    <h3>Agente proponente</h3>
    
    <div class="mc-summary-agent__agent">
        <div class="mc-summary-agent__profile-img">
            <img v-if="entity.owner.files.avatar" :src="entity.owner.files.avatar?.transformations?.avatarMedium?.url" />
            <mc-icon v-if="!entity.owner.files.avatar" name="agent-1"></mc-icon>
        </div>
        <h5>{{owner.name}}</h5>
    </div>
</div>