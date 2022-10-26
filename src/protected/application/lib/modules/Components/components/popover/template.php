<?php
use MapasCulturais\i;
?>

<VDropdown>
    <slot name="button">
        <button :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
    </slot>
    <template #popper>
        <div class="popover__content">
            <slot></slot>
        </div>
    </template>
</VDropdown>