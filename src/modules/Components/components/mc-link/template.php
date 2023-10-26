<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-icon
');
?>
<a :href="url" :class="classes" :id="id">
    <template v-if="!rightIcon">
    <mc-icon v-if="typeof icon != 'string' && icon && entity" :entity="entity"></mc-icon>
    <mc-icon v-else-if="icon && !entity" :name="icon"></mc-icon>
    <mc-icon v-else-if="typeof icon == 'string'" :name="icon"></mc-icon>
    </template>
    <slot>
        <template v-if="label">{{label}}</template>
        <template v-if="!label && entity">{{entity.name}}</template>
    </slot>
    <template v-if="rightIcon">
    <mc-icon v-if="typeof icon != 'string' && icon && entity" :entity="entity"></mc-icon>
    <mc-icon v-else-if="icon && !entity" :name="icon"></mc-icon>
    <mc-icon v-else-if="typeof icon == 'string'" :name="icon"></mc-icon>
    </template>
</a>