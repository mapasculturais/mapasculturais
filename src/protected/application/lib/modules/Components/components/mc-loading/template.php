<?php
use MapasCulturais\i;
?>
<span v-if="condition || entity?.__processing" class="mc-loading">
    <mc-icon name="loading"></mc-icon> <slot>{{entity?.__processing || '<?php i::_e('carregando...') ?>'}}</slot>
</span>