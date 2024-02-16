<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
');
?>
<div :class="classes" class="mc-avatar">
    <img v-if="image" :src="image" alt="">
    <mc-icon v-if="!image" :entity="entity"></mc-icon>
</div>