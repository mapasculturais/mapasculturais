<?php
use MapasCulturais\i;
?>
<span v-if="condition || entity?.__processing" class="loading">
    <img src="<?php $this->asset('img/spinner.gif') ?>"> {{entity?.__processing || '<?php i::_e('carregando...') ?>'}}
</span>