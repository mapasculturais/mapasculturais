<?php

use MapasCulturais\i;

$this->activeNav = 'panel/my-account';
$this->import('
    entity-field
    entity-seals
    mc-entity
    mc-icon
    mc-link
    panel--entity-actions
    panel--entity-tabs
    user-mail
    user-management--ownership-tabs
    user-accepted-terms
    user-management--delete
');
?>
<mc-entity #default='{entity}'>

    <div class="p-user-detail account-privacy">
        <header class="p-user-detail__header account-privacy__header">

            <div class="p-user-detail__header-top account-privacy__header">
                <div class="left">
                    <div class="left-icon">
                        <mc-icon class="icon" name="account"></mc-icon>
                    </div>
                    <label class="left-title"><?= i::__('Conta e privacidade') ?></label>
                </div>
            </div>

            <div class="p-user-detail__header-content">
                <div class="management-icon">
                    <mc-icon name="agent-1"></mc-icon>
                </div>
                <div class="management-content ">
                    <div class="management-content__label">
                        <label class="management-content__label--name">{{entity.profile?.name}}</label>
                        <div class="management-content__label--delete">
                        </div>
                    </div>
                    <div class="management-content__info">
                        <p>ID: {{entity.id}}</p>
                        <p><?= i::__('Último login') ?>: {{entity.lastLoginTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.lastLoginTimestamp.time()}}</p>
                        <p>
                            <?= i::__('Status') ?>:
                            <span v-if="entity.status == 1"><?= i::__('Ativo') ?></span>
                            <span v-if="entity.status == -10"><?= i::__('Excluído') ?></span>
                        </p>
                        <p><?= i::__('Data de criação') ?>: {{entity.createTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.createTimestamp.time()}}</p>
                    </div>
                </div>
            </div>
        </header>
        
        <?php $this->applyTemplateHook('seals', 'before') ?>
        <div class="p-user-detail__seals">
            <?php $this->applyTemplateHook('seals', 'begin') ?>
            <entity-seals :entity="entity.profile" :editable="entity.currentUserPermissions?.createSealRelation" title="<?= i::__('Verificações da pessoa usuária') ?>" show-name></entity-seals>
            <?php $this->applyTemplateHook('seals', 'end') ?>
        </div>
        <?php $this->applyTemplateHook('seals', 'after') ?>

        <?php $this->applyTemplateHook('config', 'before') ?>
        <div class="account-config">
            <?php $this->applyTemplateHook('config', 'begin') ?>
            <user-mail :entity="entity"></user-mail>
            <?php $this->applyTemplateHook('config', 'after') ?>
        </div>
        <?php $this->applyTemplateHook('config', 'after') ?>

        <user-accepted-terms :user="entity"></user-accepted-terms>
    </div>
</mc-entity>