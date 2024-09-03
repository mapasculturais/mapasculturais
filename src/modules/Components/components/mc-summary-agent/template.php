<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon    
');
?>
<div v-if="opportunity && canSee('agentsSummary')" class="mc-summary-agent" :class="classes">
    <h3><?= i::__('Agente proponente')?></h3>
    
    <div class="mc-summary-agent__agent">
        <mc-avatar :entity="entity.owner" size="small"></mc-avatar>
        <!-- <h5>{{owner.name}}</h5> -->
    </div>
</div>