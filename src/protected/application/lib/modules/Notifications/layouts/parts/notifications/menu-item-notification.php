<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\App;
use MapasCulturais\i;
$app = App::i();

$this->import('
    notification-modal
');
?>
<!---->
<!--<notification-modal #default="{modal}">-->
<!--    <a @click="modal.open">--><?//= i::__('Notificações') ?><!--</a>-->
<!--</notification-modal>-->
<mc-link route='panel/notifications' icon="notification"><?= i::__('Notificações') ?></mc-link>