<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-avatar
    mc-icon
');
?>

<div class="entity-field-seals" v-if="seals.length > 0">
    <div class="entity-field-seal" v-for="seal of seals" :key="seal.sealRelationId" v-tooltip="formatText(seal)">
        <div class="entity-field-seal__image" @click="setSealTouch(seal)" @mouseenter="setSeal(seal)">
            <mc-avatar :entity="seal" size="small" square></mc-avatar>
        </div>
    </div>
</div>
