<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>
<?php $this->applyTemplateHook('resend-account-validation', 'before'); ?>

<span class="user-management-resend-account-validation" v-if="supported">
    <?php $this->applyTemplateHook('resend-account-validation', 'begin'); ?>

    <span class="user-management-resend-account-validation__status user-management-resend-account-validation__status--ok" v-if="validated">
        <mc-icon name="check"></mc-icon>
        <?php i::_e('Conta validada') ?>
    </span>

    <a class="user-management-resend-account-validation__action" v-else-if="showButton" :class="{'user-management-resend-account-validation__action--sending': sending}"
       :title="sent ? text('sentHelp') : text('pendingHelp')" @click="resend">
        <mc-icon name="send"></mc-icon>
        <span v-if="sending"><?php i::_e('Enviando...') ?></span>
        <span v-else><?php i::_e('Reenviar E-mail de validação') ?></span>
    </a>

    <span class="user-management-resend-account-validation__status user-management-resend-account-validation__status--pending" v-else>
        <mc-icon name="email"></mc-icon>
        <?php i::_e('Aguardando validação') ?>
    </span>

    <?php $this->applyTemplateHook('resend-account-validation', 'end'); ?>
</span>

<?php $this->applyTemplateHook('resend-account-validation', 'after'); ?>
