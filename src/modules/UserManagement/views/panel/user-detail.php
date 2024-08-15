<?php
use MapasCulturais\i;
$this->activeNav = 'panel/user-management';
$this->import('
    mc-entity
    entity-field
    entity-seals
    mc-icon
    mc-link
    mc-tab
    mc-tabs
    panel--entity-actions
    panel--entity-tabs
    user-mail
    user-management--ownership-tabs
');
?>
<mc-entity #default='{entity}'>
    <?php $this->applyTemplateHook('management', 'before') ?>
    <div class="user-management">
        <?php $this->applyTemplateHook('management', 'begin') ?>
        
        <div class="user-management__return">
            <mc-link route="panel/index" class="button button--icon button--primary-outline">
                <mc-icon name="arrow-left"></mc-icon><?= i::__('Voltar') ?>
            </mc-link>
        </div>

        <header class="user-management__header">
            <div class="user-management__header-icon">
                <mc-icon name="agent-1"></mc-icon>
            </div>

            <div class="user-management__header-content">
                <div class="user-management__user">
                    <h3>{{entity.profile?.name}}</h3>
                    
                    <div class="user-management__user-delete">
                        <panel--entity-actions :entity="entity"></panel--entity-actions>
                    </div>
                </div>

                <div class="user-management__user-info">
                    <p v-if="global.showIds[entity.__objectType]" >ID: {{entity.id}}</p>
                    <p><?= i::__('Último login') ?>: {{entity.lastLoginTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.lastLoginTimestamp.time()}}</p>
                    <p>
                        <?= i::__('Status') ?>:
                        <span v-if="entity.status == 1"><?= i::__('Ativo') ?></span>
                        <span v-if="entity.status == -10"><?= i::__('Excluído') ?></span>
                    </p>
                    <p><?= i::__('Data de criação') ?>: {{entity.createTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.createTimestamp.time()}}</p>
                </div>
            </div>
        </header>
        
        <?php $this->applyTemplateHook('seals', 'before') ?>
        <div class="user-management__user-seals">
            <?php $this->applyTemplateHook('seals', 'begin') ?>
            <entity-seals :entity="entity.profile" :editable="entity.profile.currentUserPermissions?.createSealRelation"></entity-seals>
            <?php $this->applyTemplateHook('seals', 'after') ?>
        </div>
        <?php $this->applyTemplateHook('seals', 'after') ?>

        <?php $this->applyTemplateHook('config', 'before') ?>
        <div class="user-management__user-config">
            <?php $this->applyTemplateHook('config', 'begin') ?>
            <user-mail :entity="entity"></user-mail>
            <?php $this->applyTemplateHook('config', 'end') ?>
        </div>
        <?php $this->applyTemplateHook('config', 'after') ?>

        <?php $this->applyTemplateHook('management-properties', 'before') ?>
        <div class="user-management__properties">
            <?php $this->applyTemplateHook('management-properties', 'begin') ?>
            <h3 class="user-management__properties-label">
                <?= i::__('Propriedades do usuário') ?>
            </h3>

            <div class="user-management__properties-content">
                <?php $this->applyTemplateHook('user-detail--tabs', 'before') ?>
                <mc-tabs class="user-management__properties-tabs">
                    <?php $this->applyTemplateHook('user-detail--tabs', 'begin') ?>
                    <mc-tab label="<?= i::esc_attr__('Agentes') ?>" slug="agents" icon='agent' classes="tabs-component-button--active-agent">
                        <user-management--ownership-tabs :user="entity" type="agent" classes="tabs-component__header footer-content-tabs"></user-management--ownership-tabs>
                    </mc-tab>
        
                    <mc-tab label="<?= i::esc_attr__('Espaços') ?>" slug="spaces" icon='space' classes="tabs-component-button--active-space">
                        <user-management--ownership-tabs :user="entity" type="space"></user-management--ownership-tabs>
                    </mc-tab>
        
                    <mc-tab label="<?= i::esc_attr__('Eventos') ?>" slug="events" icon='event' classes="tabs-component-button--active-event">
                        <user-management--ownership-tabs :user="entity" type="event"></user-management--ownership-tabs>
                    </mc-tab>
        
                    <mc-tab label="<?= i::esc_attr__('Projetos') ?>" slug="projects" icon='project' classes="tabs-component-button--active-project">
                        <user-management--ownership-tabs :user="entity" type="project"></user-management--ownership-tabs>
                    </mc-tab>
        
                    <mc-tab label="<?= i::esc_attr__('Oportunidades') ?>" slug="opportunities" icon='opportunity' classes="tabs-component-button--active-opportunity">
                        <user-management--ownership-tabs :user="entity" type="opportunity"></user-management--ownership-tabs>
                    </mc-tab>
        
                    <mc-tab label="<?= i::esc_attr__('Inscrições') ?>" slug="registrations" icon='opportunity' classes="tabs-component-button--active-registration">
                        <user-management--ownership-tabs :user="entity" type="registration"></user-management--ownership-tabs>
                    </mc-tab>
                    <?php $this->applyTemplateHook('user-detail--tabs', 'end') ?>
                </mc-tabs>
                <?php $this->applyTemplateHook('user-detail--tabs', 'after') ?>
            </div>
            <?php $this->applyTemplateHook('management-properties', 'end') ?>
        </div>
        <?php $this->applyTemplateHook('management-properties', 'after') ?>

        <?php $this->applyTemplateHook('management', 'end') ?>
    </div>
    <?php $this->applyTemplateHook('management', 'after') ?>
</mc-entity>