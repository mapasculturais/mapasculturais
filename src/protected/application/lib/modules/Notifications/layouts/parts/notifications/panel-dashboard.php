<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\i;

$this->import('tab');
?>
<tab label="<?php i::esc_attr_e('Notificações') ?>" slug="notifications">
    <entities type='notification'></entities>
</tab>