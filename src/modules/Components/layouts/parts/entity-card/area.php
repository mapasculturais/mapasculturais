<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use \MapasCulturais\i;
?>

<div v-if="areas" class="entity-card__content--terms-area">
    <label v-if="entity.__objectType === 'opportunity'" class="area__title">
        <?php i::_e('Áreas de interesse:') ?> ({{entity.terms.area.length}}):
    </label>
    <label v-if="entity.__objectType === 'agent' || entity.__objectType === 'space'" class="area__title">
        <?php i::_e('Áreas de atuação:') ?> ({{entity.terms.area.length}}):
    </label>
    <p :class="['terms', entity.__objectType+'__color']"> {{areas}} </p>
</div>
