<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
    mc-avatar
    mc-card
    mc-icon
');
?>
<mc-card v-if="space && canSee('spaceSummary') && opportunity.useSpaceRelationIntituicao && opportunity.useSpaceRelationIntituicao !== 'dontUse'" :class="classes">
    <template #title>
        <h3><?= i::__("Espaço Vinculado") ?></h3>
    </template>
    <template #content>
        <div class="mc-linked-entity">
            <mc-avatar :entity="space" size="xsmall"></mc-avatar>
            <h5 v-if="space">{{space?.name}}</h5>
            <h5 v-if="!space"><?= i::__("Espaço não informado") ?></h5>
        </div>
    </template>
</mc-card>