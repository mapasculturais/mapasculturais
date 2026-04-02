<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    oc-tabs
');
?>

<div class="steps">
    <ul :class="{'has-active' : hasActive()}">
        <template v-for="step in steps">
            <li @click="changeStep(step.ref)">
                <div class="item" :ref="step.ref" :class="{'active' : step.isActive}">
                    <div class="icon" :class="{'two-icons' : step.icons.length > 1}">
                        <mc-icon v-for="icon in step.icons" :name="icon"></mc-icon>
                    </div>
                    <div class="label">
                        {{step.label}}
                    </div>
                </div>
            </li>
        </template>
    </ul>
</div>

