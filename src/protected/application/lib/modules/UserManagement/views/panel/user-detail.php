<?php
use MapasCulturais\i;
$this->activeNav = 'panel/user-management';

$this->import('
    confirm-button
    entity
    entity-field
    entity-seals
    mc-icon
    panel--entity-actions
    panel--entity-tabs
    tabs
    user-management--ownership-tabs
');
?>
<entity #default='{entity}'>
    
    <div class="panel-page">
        <header class="panel-page__header">
            <div class="panel-page__header-title">
                <div class="title">
                    <div class="title__icon default"> <mc-icon name="agent-1"></mc-icon> </div>
                    <div class="title__title"> {{entity.profile?.name}} </div>
                </div>
                <div class="help">
                    <a class="panel__help-link" href="#"><?=i::__('Ajuda?')?></a>
                </div>
            </div>
        </header>
    </div>

    <panel--entity-actions :entity="entity"></panel--entity-actions>

    <entity-seals :entity="entity.profile" :editable="entity.profile.currentUserPermissions?.createSealRelation"></entity-seals>

    <p>ID: {{entity.id}}</p>
    <p v-if="!entity.editingEmail">
        <?= i::__('E-mail') ?>: {{entity.email}}
        <a @click="entity.editingEmail = true">
            <mc-icon name="edit"></mc-icon>
        </a>
    </p>
    <form class="grid-12" v-if="entity.editingEmail" @submit="entity.save().then(() => entity.editingEmail = false); $event.preventDefault();">
        <div class="field col-4">
            <entity-field :entity="entity" prop="email" hide-required>
        </div>
        <button class="col-2 button button-primary"><?php i::_e('Salvar') ?></button>
        <button class="col-2 button button-secondary" @click="entity.editingEmail = false"><?php i::_e('Cancelar') ?></button>
    </form>
    <p><?= i::__('Data de criação') ?>: {{entity.createTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.createTimestamp.time()}}</p>
    <p><?= i::__('Último login') ?>: {{entity.lastLoginTimestamp.date('long year')}} <?= i::__('às') ?> {{entity.lastLoginTimestamp.time()}}</p>
    <p>
        <?= i::__('Status') ?>: 
        <span v-if="entity.status == 1"><?= i::__('Ativo') ?></span>
        <span v-if="entity.status == -10"><?= i::__('Excluído') ?></span>
    </p>


    <h3><?= i::__('Propriedades do usuário') ?></h3>
    <tabs>
        <tab label="<?= i::esc_attr__('Agentes') ?>" slug="agents" icon='agent' classes="agent__color">
            <user-management--ownership-tabs :user="entity" type="agent"></user-management--ownership-tabs>
        </tab>

        <tab label="<?= i::esc_attr__('Espaços') ?>" slug="spaces" icon='space' classes="space__color">
            <user-management--ownership-tabs :user="entity" type="space"></user-management--ownership-tabs>
        </tab>

        <tab label="<?= i::esc_attr__('Eventos') ?>" slug="events" icon='event' classes="event__color">
            <user-management--ownership-tabs :user="entity" type="event"></user-management--ownership-tabs>
        </tab>

        <tab label="<?= i::esc_attr__('Projetos') ?>" slug="projects" icon='project' classes="project__color">
            <user-management--ownership-tabs :user="entity" type="project"></user-management--ownership-tabs>
        </tab>

        <tab label="<?= i::esc_attr__('Oportunidades') ?>" slug="opportunities" icon='opportunity' classes="opportunity__color">
            <user-management--ownership-tabs :user="entity" type="opportunity"></user-management--ownership-tabs>
        </tab>

        <tab label="<?= i::esc_attr__('Inscrições') ?>" slug="registrations" icon='registration' classes="opportunity__color">
        </tab>
    </tabs>
</entity>