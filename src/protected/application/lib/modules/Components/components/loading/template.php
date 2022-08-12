<?php
use MapasCulturais\i;
?>
<span v-if="condition || entity?.__processing" class="loading">
    <mc-icon name="loading"></mc-icon> {{entity?.__processing || '<?php i::_e('carregando...') ?>'}}
</span>