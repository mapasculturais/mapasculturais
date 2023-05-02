<?php

use MapasCulturais\i;

$this->import('
    mapas-card
    mc-icon
    
');
?>
<mapas-card v-if="space && canSee('spaceSummary')">
    <template #title>
        <div v-if="opportunity.useSpaceRelationIntituicao && opportunity.useSpaceRelationIntituicao !== 'dontUse'">
            <div>
                <h4><strong><?= i::__("Espaço Vinculado") ?> </strong></h4>
            </div>
            <div>
                <img v-if="space?.files?.avatar" :src="space?.files?.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon v-if="!space?.files?.avatar" name="space"></mc-icon>
                <span>{{space?.name}}</span>
                <span v-if="!space"><?= i::__("Espaço não informado") ?></span>
            </div>
            <div v-if="space">
                <div><small><strong><?= i::__("Nome:") ?></strong> {{space?.name}}</small></div>
            </div>
        </div>
    </template>
</mapas-card>