<?php

use MapasCulturais\i;
?>
<div class="popover">
    <slot name="button" :open="open" :close="close" :toggle="toggle" :active="active">
        <button @click="toggle()" :class="['button', buttonClasses]">{{buttonLabel || '<?= i::__('Defina a propriedade button-label do componente popover') ?>'}}</button>
    </slot>
    
    <div v-if="active" :class="['popover__content', classes, openside]">
        <slot :open="open" :close="close" :toggle="toggle" :active="active" />
    </div>
</div>