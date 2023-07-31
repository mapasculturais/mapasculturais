<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<div :class="classes">
    <mc-icon :name="icon"></mc-icon>
    <p>
        <slot name="default">
            {{ msg }}
        </slot>
    </p>
</div>