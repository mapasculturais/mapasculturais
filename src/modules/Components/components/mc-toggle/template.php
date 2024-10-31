<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="mc-toggle">
    <label class="mc-toggle__switch">
        <input type="checkbox" :checked="modelValue" @change="toggleSwitch">
        <span class="mc-toggle__slider"></span>
        <span class="label-text">{{ label }}</span>
    </label>
</div>
