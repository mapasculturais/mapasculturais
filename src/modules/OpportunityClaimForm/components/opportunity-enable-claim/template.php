<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-link
');
?>

<div class="opportunity-enable-claim">
    <h4 class="bold opportunity-enable-claim__title"><?= i::__('Recurso') ?></h4>
    <div class="opportunity-enable-claim__input ">
        <label>
            <input type="checkbox" v-model="isActiveClaim" @click="autoSave()"/>
            <?= i::__("Habilitar Recurso") ?>
        </label>
    </div>
    <div v-if="isActiveClaim" class="opportunity-enable-claim__email">
        <div class="opportunity-enable-claim__save">
            <entity-field :entity="entity" prop="claimEmail" :autosave="1500"></entity-field>
        </div>
    </div>
</div>
<div class="config-phase__line col-12"></div>