<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
');
?>
<div ref="content">
    <VDropdown v-if="!$media('max-width: 500px')" :triggers="[]" :shown="active" :autoHide="false" :popperClass="classes" @apply-show="focus()">
        <slot name="button" :open="open" :close="close" :toggle="toggle" :active="active">
            <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
        </slot>
        <template #popper>
            <div ref="content" class="popover__content">
                <slot :open="open" :close="close" :toggle="toggle" :active="active"></slot>
            </div>
        </template>
    </VDropdown>

    <mc-modal :title="title" teleport="body" :classes="['popover-modal', classes]" v-if="$media('max-width: 500px')">
        <template #default="modal">
            <div ref="content" class="popover__content--modal popover-form">
                <slot  :open="modal.open" :close="modal.close" :toggle="modal.toggle" :active="modal.isOpen"></slot>
            </div>
        </template>

        <template #button="modal">
            <slot name="button" :open="modal.open" :close="modal.close" :toggle="modal.toggle" :active="modal.isOpen">
                <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
            </slot>
        </template>
    </mc-modal>
</div>