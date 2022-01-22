<?php 
use MapasCulturais\i;
?>
<span v-if="condition || entity?.__processing">
    <img src="<?php $this->asset('img/spinner.gif') ?>"> {{entity?.__processing || '<?php i::_e('carredando...') ?>'}}
</span>