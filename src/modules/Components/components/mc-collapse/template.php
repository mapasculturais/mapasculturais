<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="mc-collapse">
    <div class="mc-collapse__header">
        <slot name="header"></slot>
    </div>

    <div v-if="hasSlot('content')" class="mc-collapse__body">
        <div :class="['mc-collapse__toggle', {'expanded':expanded}]">
            <div class="mc-collapse__content">
                <div class="mc-collapse__toggle-close">
                    <mc-icon @click="close()" name="close"></mc-icon>
                </div>

                <slot name="content"></slot>
            </div>
        </div>

        <div class="mc-collapse__toggle-button">
            <span @click="toggle()" class="button button--icon button--sm">
                <?= i::__('Opções avançadas') ?>
                <mc-icon :name="icon"></mc-icon>
            </span>
        </div>
    </div>
</div>