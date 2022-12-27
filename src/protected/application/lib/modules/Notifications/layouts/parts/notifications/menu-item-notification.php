<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\App;
use MapasCulturais\i;
$app = App::i();

$this->import('
    notification-modal
');
?>

<notification-modal type-style="button" media-query="<?= $media_query ?>" #default="{modal}">
    <a @click="modal.open"><?= i::__('Notificações') ?></a>
</notification-modal>