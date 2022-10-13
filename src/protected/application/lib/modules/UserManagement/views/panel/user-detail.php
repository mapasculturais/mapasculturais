<?php

use MapasCulturais\i;

$this->activeNav = 'panel/user-management';

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
');
?>
<entity #default='{entity}'>
    <div class="panel-main background-usr">
        <div class="panel-page background-usr">
            <header class="panel-page__header">
                <div class="header-top">
                    <div class="header-top--button">
                        <mc-link route="panel/index" class="button button--icon button--primary-outline">
                            <mc-icon name="arrow-left"></mc-icon>Voltar
                        </mc-link>
                    </div>
                    <div class="help">
                        <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
                    </div>
                </div>

                <div class="panel-page__header-title-usr">
                    <div class="title-user-panel">
                        <div class="title-user-panel__icon-usr default-usr">
                            <mc-icon name="agent-1"></mc-icon>
                        </div>

                        <div class="title-user-panel__title">
                            {{entity.profile?.name}}
                        </div>
                        


                    </div>
                    <div class="header-top--actions">
                        <panel--entity-actions :entity="entity"></panel--entity-actions>

                    </div>
                       
                </div>
            </header>
        </div>
        <div class="panel-page__content-usr">
            <div class="panel-page__content-usr--left">
                <p>ID: {{entity.id}}</p>
               
                <p>
                    <?= i::__('Status') ?>:
                    <span v-if="entity.status == 1"><?= i::__('Ativo') ?></span>
                    <span v-if="entity.status == -10"><?= i::__('Excluído') ?></span>
                </p>
                <p><?= i::__('Data de criação') ?>: {{entity.createTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.createTimestamp.time()}}</p>
            </div>
            <div class="panel-page__content-usr--right">
                <p><?= i::__('Último login') ?>: {{entity.lastLoginTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.lastLoginTimestamp.time()}}</p>
            </div>
        </div>
        <div class="panel-page__seals-usr">
            <entity-seals :entity="entity.profile" :editable="entity.profile.currentUserPermissions?.createSealRelation"></entity-seals>
        </div>

        <div class="editing-email col-12">
            <div class="editing-email__content">

                <label class="editing-email__content--label"><?= i::__('Configurações da conta do usuário') ?></label>
                <p v-if="!entity.editingEmail">
                    <label class="editing-email__content--email"><?= i::__('E-mail') ?> : {{entity.email}}</label>
                    <a @click="entity.editingEmail = true" class="editing-email__content--edit">
                        <mc-icon name="edit"></mc-icon><label class="editing-email__content--edit-label"><?php i::_e('Alterar email')?></label>
                    </a>
                </p>
                <form class="grid-12" v-if="entity.editingEmail" @submit="entity.save().then(() => entity.editingEmail = false); $event.preventDefault();">
                    <div class="field col-4">
                        <entity-field :entity="entity" prop="email" hide-required>
                    </div>
                    <button class="col-2 button button-primary"><?php i::_e('Salvar') ?></button>
                    <button class="col-2 button button-secondary" @click="entity.editingEmail = false"><?php i::_e('Cancelar') ?></button>
                </form>
            </div>

        </div>

        


        <h3><?= i::__('Propriedades do usuário') ?></h3>
    </div>
    <div class="tabs-component-usr">
        <tabs class="tabs-component background-usr">
            <tab label="<?= i::esc_attr__('Agentes') ?>" slug="agents" icon='agent' >
                <user-management--ownership-tabs :user="entity" type="agent"></user-management--ownership-tabs>
            </tab>

            <tab label="<?= i::esc_attr__('Espaços') ?>" slug="spaces" icon='space'>
                <user-management--ownership-tabs :user="entity" type="space"></user-management--ownership-tabs>
            </tab>

            <tab label="<?= i::esc_attr__('Eventos') ?>" slug="events" icon='event'>
                <user-management--ownership-tabs :user="entity" type="event"></user-management--ownership-tabs>
            </tab>

            <tab label="<?= i::esc_attr__('Projetos') ?>" slug="projects" icon='project'>
                <user-management--ownership-tabs :user="entity" type="project"></user-management--ownership-tabs>
            </tab>

            <tab label="<?= i::esc_attr__('Oportunidades') ?>" slug="opportunities" icon='opportunity'>
                <user-management--ownership-tabs :user="entity" type="opportunity"></user-management--ownership-tabs>
            </tab>

            <tab label="<?= i::esc_attr__('Inscrições') ?>" slug="registrations" icon='registration'>
            </tab>
        </tabs>
    </div>
</entity>