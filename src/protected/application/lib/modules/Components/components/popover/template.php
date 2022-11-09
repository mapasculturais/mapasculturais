<?php
use MapasCulturais\i;
?>

<div ref="content">
    <VDropdown :triggers="[]" :shown="active" :autoHide="false" @apply-show="focus()">
        <slot name="button" :open="open" :close="close" :toggle="toggle" :active="active">
            <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
        </slot>
        <template #popper>
            <div ref="content" class="popover__content">
                <slot :open="open" :close="close" :toggle="toggle" :active="active"></slot>
            </div>
        </template>
    </VDropdown>
</div>
