<?php

use MapasCulturais\i;

$this->import('
    mapas-card
    mc-icon
    
');
?>
<mapas-card v-if="canSee('spaceSummary')">
    <template v-slot:title>
        <div>
            <div>
                <h4><strong><?= i::__("Espaço Vinculado") ?> </strong></h4>
            </div>
            <div>
                <img v-if="space.files?.avatar" :src="space.files?.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon v-if="!space.files?.avatar" name="space"></mc-icon>
                <span>{{space.name}}</span>
            </div>
            <div v-if="space">
                <div><small><strong><?= i::__("Nome:") ?></strong> {{space?.name}}</small></div>
            </div>
        </div>
    </template>
</mapas-card>