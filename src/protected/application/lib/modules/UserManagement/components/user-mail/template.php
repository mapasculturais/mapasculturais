<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-icon
');
?>
<?php $this->applyTemplateHook('user-mail', 'before'); ?>

<div class="user-mail__account-config">
    <?php $this->applyTemplateHook('user-mail', 'begin'); ?>

    <label class="user-mail__account-config-label"><?= i::__('Configurações da conta do usuário') ?></label>
    <p v-if="!entity.editingEmail">
        <label class="user-mail__account-config-email"><?= i::__('E-mail') ?> : {{entity.email}}</label>
        <a @click="entity.editingEmail = true" class="user-mail__account-config-edit">
            <mc-icon name="edit"></mc-icon><label class="user-mail__account-config-edit-label"><?php i::_e('Alterar email') ?></label>
        </a>
    </p>
    <form class="grid-12 user-mail__account-config-form" v-if="entity.editingEmail" @submit="entity.save().then(() => entity.editingEmail = false); $event.preventDefault();">
        <div class="col-4">
            <entity-field :entity="entity" prop="email" hide-required></entity-field>
        </div>
        <div class="mail-buttons">
            <button class="col-2 button button--primary button--md"><?php i::_e('Salvar') ?></button>
            <button class="col-2 button button--secondary button--md" @click="entity.editingEmail = false"><?php i::_e('Cancelar') ?></button>
        </div>
    </form>
    <?php $this->applyTemplateHook('user-mail', 'end'); ?>
</div>
<?php $this->applyTemplateHook('user-mail', 'after'); ?>