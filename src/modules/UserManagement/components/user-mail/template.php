<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-icon
    user-management--resend-account-validation
');
?>
<?php $this->applyTemplateHook('user-mail', 'before'); ?>

<div class="user-mail">
    <?php $this->applyTemplateHook('user-mail', 'begin'); ?>

    <label class="user-mail__title"><?= i::__('Configurações da conta do usuário') ?></label>

    <div class="user-mail__config" v-if="!entity.editingEmail">
        <div class="user-mail__config-title">
            <?= i::__('E-mail') ?> :
        </div>
        <div class="user-mail__config-content">
            <b>{{entity.email}}</b>
            <a @click="entity.editingEmail = true" class="user-mail__config-edit">
                <mc-icon name="edit"></mc-icon>
                <?php i::_e('Alterar email') ?>
            </a>
            <user-management--resend-account-validation :entity="entity"></user-management--resend-account-validation>
        </div>
    </div>

    <form class="grid-12 user-mail__form" v-if="entity.editingEmail" @submit="entity.save().then(() => entity.editingEmail = false); $event.preventDefault();">
        <div class="col-6">
            <entity-field :entity="entity" prop="email" label="<?= i::esc_attr__('E-mail') ?>" hide-required></entity-field>
        </div>
        <div class="col-6 mail-buttons">
            <button type="submit" class="button button--primary button--md"><?php i::_e('Salvar') ?></button>
            <button type="button" class="button button--text button--text-del" @click="entity.editingEmail = false"><?php i::_e('Cancelar') ?></button>
        </div>
    </form>
    <?php $this->applyTemplateHook('user-mail', 'end'); ?>
</div>
<?php $this->applyTemplateHook('user-mail', 'after'); ?>