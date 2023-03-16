<?php

use MapasCulturais\i;
?>

<div ref="content">

    <VDropdown v-if="!isMobile()" :triggers="[]" :shown="active" :autoHide="false" @apply-show="focus()">
        <slot name="button" :open="open" :close="close" :toggle="toggle" :active="active">
            <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
        </slot>
        <template #popper>
            <div ref="content" class="popover__content">
                <slot :open="open" :close="close" :toggle="toggle" :active="active"></slot>
            </div>
        </template>
    </VDropdown>


    <modal >
        <template #default>
            <div ref="content" class="popover__content">
                <slot :open="modal.open" :close="modal.close" :toggle="modal.toggle" :active="modalOpen"></slot>
            </div>
        </template>

        <template #button="modal">
            <slot name="button" :open="modal.open" :close="modal.close" :toggle="modal.toggle" :active="modalOpen">
                <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
            </slot>
        </template>

        <template #actions="modal">
            <a class="button button--secondary" @click="modal.close()"><?php i::_e('Fechar') ?></a>
        </template>
    </modal>

</div>