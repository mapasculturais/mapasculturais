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
    <div class="p-user-detail">

        <div class="panel-main">
            <header class="p-user-detail__header">
                <div class="header-top grid-12">
                    <div class="header-top--button ">
                        <mc-link route="panel/index" class="button button--icon button--primary-outline">
                            <mc-icon name="arrow-left"></mc-icon>Voltar
                        </mc-link>
                    </div>
                    <div class="help ">
                        <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
                    </div>


                </div>

                <div class="p-user-detail__header--title ">
                    <div class="title-header ">
                        <div class="title-header__icon ">
                            <mc-icon name="agent-1"></mc-icon>
                        </div>

                        <div class="title-header__label ">
                            <div class="title-header__label--content">

                                <label>{{entity.profile?.name}}</label>
                                <div class="title-header__label--edit">
                                    <panel--entity-actions :entity="entity"></panel--entity-actions>
                                </div>
                            </div>


                            <div class="content-user">
                                <div class="content-user__left">
                                    <p>ID: {{entity.id}}</p>

                                    <p>
                                        <?= i::__('Status') ?>:
                                        <span v-if="entity.status == 1"><?= i::__('Ativo') ?></span>
                                        <span v-if="entity.status == -10"><?= i::__('Excluído') ?></span>
                                    </p>
                                    <p><?= i::__('Data de criação') ?>: {{entity.createTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.createTimestamp.time()}}</p>
                                </div>
                                <div class="content-user__right">
                                    <p><?= i::__('Último login') ?>: {{entity.lastLoginTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.lastLoginTimestamp.time()}}</p>
                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </header>

            <div class="content-footer">
                <entity-seals :entity="entity.profile" :editable="entity.profile.currentUserPermissions?.createSealRelation"></entity-seals>
            </div>

            <div class="editing-email col-12">
                <div class="editing-email__content">

                    <label class="editing-email__content--label"><?= i::__('Configurações da conta do usuário') ?></label>
                    <p v-if="!entity.editingEmail">
                        <label class="editing-email__content--email"><?= i::__('E-mail') ?> : {{entity.email}}</label>
                        <a @click="entity.editingEmail = true" class="editing-email__content--edit">
                            <mc-icon name="edit"></mc-icon><label class="editing-email__content--edit-label"><?php i::_e('Alterar email') ?></label>
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
        <div class="tabs-component">

            <tabs class="tabs-component tabs-component--user">

                <tab label="<?= i::esc_attr__('Agentes') ?>"  slug="agents" icon='agent' classes="tabs-component-button--active-agent">
                    <user-management--ownership-tabs :user="entity"  type="agent"></user-management--ownership-tabs>
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

                <tab label="<?= i::esc_attr__('Inscrições') ?>" slug="registrations" icon='registration'>
                </tab>
            </tabs>
        </div>
    </div>

</entity>