<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div :class="classes">
    <mc-icon :name="icon"></mc-icon>
    <p>
        <slot name="default">
            {{ msg }}
        </slot>
    </p>
</div>