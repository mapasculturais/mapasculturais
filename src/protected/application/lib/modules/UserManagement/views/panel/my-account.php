<?php

use MapasCulturais\i;

$this->activeNav = 'panel/my-account';
$this->import('
    confirm-button
    entity
    entity-field
    entity-seals
    mc-icon
    mc-link
    panel--entity-actions
    panel--entity-tabs
    tabs
    user-management--ownership-tabs
    user-accepted-terms
');
?>
<entity #default='{entity}'>

    <div class="p-user-detail">
        <div class="panel-main">
            <header class="p-user-detail__header">

                <div class="p-user-detail__header-top">
                    <div class="account__left">
                        <div class="title">
                            <div class="title__background">
                                <mc-icon class="title__background-icon" name="account"></mc-icon>
                            </div>
                        </div>
                        <label class="account__left-privacy"><?= i::__('Conta e privacidade') ?></label>
                    </div>
                    <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
                </div>
                <div class="p-user-detail__header-content">
                    <div class="management-icon">
                        <mc-icon name="agent-1"></mc-icon>
                    </div>
                    <div class="management-content ">
                        <div class="management-content__label">
                            <label class="management-content__label--name">{{entity.profile?.name}}</label>
                            <div class="management-content__label--delete">
                                <panel--entity-actions :entity="entity"></panel--entity-actions>
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
            <entity-seals :entity="entity.profile" :editable="entity.profile.currentUserPermissions?.createSealRelation"></entity-seals>

            <div class="p-user-detail__account-config">

                <label class="p-user-detail__account-config-label"><?= i::__('Configurações da conta do usuário') ?></label>
                <p v-if="!entity.editingEmail">
                    <label class="p-user-detail__account-config-email"><?= i::__('E-mail') ?> : {{entity.email}}</label>
                    <a @click="entity.editingEmail = true" class="p-user-detail__account-config-edit">
                        <mc-icon name="edit"></mc-icon><label class="p-user-detail__account-config-edit-label"><?php i::_e('Alterar email') ?></label>
                    </a>
                </p>
                <form class="grid-12 p-user-detail__account-config-form" v-if="entity.editingEmail" @submit="entity.save().then(() => entity.editingEmail = false); $event.preventDefault();">
                    <div class="col-4">
                        <entity-field :entity="entity" prop="email" hide-required>
                    </div>
                    <button class="col-2 button button--primary button--md"><?php i::_e('Salvar') ?></button>
                    <button class="col-2 button button--secondary button--md" @click="entity.editingEmail = false"><?php i::_e('Cancelar') ?></button>
                </form>
            </div>

            <user-accepted-terms :user="entity"></user-accepted-terms>

            <div class="user-function">
                <label class="user-function__label"><?= i::__('Funções do Usuário') ?></label>
                <div class="user-function__box">
                    <label class="user-function__box--label"><?= i::__('Título de Função de usuário em Subsite') ?> </label>
                    <div class="user-function__box--content">
                        <label class="user-function__box--content-text">texto qualquer do subsite</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-user-detail__property-label">
            <h3><?= i::__('Minhas propriedades por entidades') ?></h3>
        </div>
        <div class="p-user-detail__property-content">
            <div class="tabs-component">
                <!-- tabs component p-user-detail__content-footer tabs-component--user -->
                <tabs class="tabs-component__entities">
                    <tab label="<?= i::esc_attr__('Agentes') ?>" slug="agents" icon='agent' classes="tabs-component-button--active-agent">
                        <user-management--ownership-tabs :user="entity" type="agent" classes="tabs-component__header footer-content-tabs"></user-management--ownership-tabs>
                    </tab>

                    <tab label="<?= i::esc_attr__('Espaços') ?>" slug="spaces" icon='space' classes="tabs-component-button--active-space">
                        <user-management--ownership-tabs :user="entity" type="space"></user-management--ownership-tabs>
                    </tab>

                    <tab label="<?= i::esc_attr__('Eventos') ?>" slug="events" icon='event' classes="tabs-component-button--active-event">
                        <user-management--ownership-tabs :user="entity" type="event"></user-management--ownership-tabs>
                    </tab>

                    <tab label="<?= i::esc_attr__('Projetos') ?>" slug="projects" icon='project' classes="tabs-component-button--active-project">
                        <user-management--ownership-tabs :user="entity" type="project"></user-management--ownership-tabs>
                    </tab>

                    <tab label="<?= i::esc_attr__('Oportunidades') ?>" slug="opportunities" icon='opportunity' classes="tabs-component-button--active-opportunity">
                        <user-management--ownership-tabs :user="entity" type="opportunity"></user-management--ownership-tabs>
                    </tab>

                    <tab label="<?= i::esc_attr__('Inscrições') ?>" slug="registrations" icon='opportunity' classes="tabs-component-button--active-registration">
                        <user-management--ownership-tabs :user="entity" type="registration"></user-management--ownership-tabs>
                    </tab>
                </tabs>
            </div>
        </div>
    </div>
</entity>