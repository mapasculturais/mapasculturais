<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\App;
use MapasCulturais\i;
$app = App::i();

$this->import('
    notification-modal
');

?>

<?php $this->applyTemplateHook('header-notification', 'before') ?>
<?php if (!$app->user->is('guest')): ?>
    <notification-modal #default="{modal}">
        <a @click="modal.open"><?php i::esc_attr_e('Notificações') ?></a>
    </notification-modal>
<?php endif; ?>
<?php $this->applyTemplateHook('header-notification', 'after') ?>