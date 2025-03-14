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
    <div class="entity-field-seal" v-for="seal of seals" :key="seal.sealRelationId" :title="seal.name">
        <div class="entity-field-seal__image" @click="setSealTouch(seal)" @mouseenter="setSeal(seal)">
            <mc-avatar v-if="seal.files?.avatar" :entity="seal" size="small" square></mc-avatar>
            <mc-icon v-else name="seal"></mc-icon>
        </div>
    </div>
</div>
