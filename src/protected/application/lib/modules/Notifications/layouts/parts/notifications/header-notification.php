<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\App;
$app = App::i();

$this->import('
    view-notification
');

?>

<?php $this->applyTemplateHook('header-notification', 'before') ?>
<?php if (!$app->user->is('guest')): ?>
    <view-notification #default="{modal}">
        <a @click="modal.open">Notificações</a>
    </view-notification>
<?php endif; ?>
<?php $this->applyTemplateHook('header-notification', 'after') ?>