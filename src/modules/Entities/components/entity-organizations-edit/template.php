<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    create-agent
    mc-avatar
    mc-confirm-button
    mc-entities
    mc-icon
    mc-link
    mc-tab
    mc-tabs
');
?>
<div class="edit-1__admin edit-1__orgs edit-1__inner-tabs">
    <mc-tabs class="tabs" sync-hash>
        <template #header="{ tab }">
            <span>{{ tab.label }}</span>
            <span
                v-if="tab.meta?.count > 0"
                class="edit-1__admin-count"
                :class="{ 'edit-1__admin-count--danger': tab.meta?.danger }">
                {{ tab.meta.count }}
            </span>
        </template>

        <mc-tab
            label="<?= i::esc_attr_e('Sou proprietário(a)') ?>"
            slug="orgs-owner"
            :meta="{ count: ownerCount }">
            <div class="edit-1__admin-card">
                <div class="edit-1__admin-alert">
                    <mc-icon name="exclamation"></mc-icon>
                    <p>
                        <?php i::_e('Essas são organizações criadas por você e cuja propriedade permanece em seu nome.') ?>
                        <strong><?php i::_e('Atenção:') ?></strong>
                        <?php i::_e('as organizações cuja propriedade tenha sido transferida para outro agente não serão encontradas aqui.') ?>
                    </p>
                </div>

                <mc-entities
                    type="agent"
                    name="edit-orgs-owner"
                    :select="selectFields"
                    :query="ownerQuery"
                    watch-query
                    @fetch="onOwnerLoaded">
                    <template #default="{entities}">
                        <ul v-if="entities.length" class="entity-admins-edit__items">
                            <li v-for="org in entities" :key="org.id" class="entity-admins-edit__item">
                                <div class="entity-admins-edit__avatar">
                                    <mc-avatar :entity="org" size="medium"></mc-avatar>
                                </div>
                                <div class="entity-admins-edit__content">
                                    <a class="entity-admins-edit__name" :href="org.singleUrl">{{ org.name }}</a>
                                    <p v-if="org.type?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de organização:') ?></span>
                                        {{ org.type.name }}
                                    </p>
                                    <p v-if="areas(org).length" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label">
                                            <?php i::_e('Áreas de atuação') ?> ({{ areas(org).length }}):
                                        </span>
                                        <span
                                            v-for="area in areas(org)"
                                            :key="area"
                                            class="entity-admins-edit__area">
                                            {{ String(area).toUpperCase() }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(org.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização criada em:') ?>
                                            {{ formatDate(org.createTimestamp) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="entity-admins-edit__actions">
                                    <mc-link :entity="org" route="edit" class="button button--primary-outline button--icon button--sm entity-admins-edit__edit">
                                        <mc-icon name="edit"></mc-icon>
                                        <?php i::_e('editar organização') ?>
                                    </mc-link>
                                </div>
                            </li>
                        </ul>
                    </template>
                    <template #empty>
                        <p class="entity-admins-edit__empty"><?php i::_e('Você ainda não criou nenhuma organização.') ?></p>
                    </template>
                </mc-entities>

                <div v-if="editable" class="entity-admins-edit__add">
                    <create-agent
                        :editable="true"
                        :initial-type="2"
                        lock-type
                        :parent="entity"
                        button-label="<?php i::esc_attr_e('criar organização') ?>"
                        @create="onCreateOrg">
                    </create-agent>
                </div>
            </div>
        </mc-tab>

        <mc-tab
            label="<?= i::esc_attr_e('Administro') ?>"
            slug="orgs-admin"
            :meta="{ count: adminCount }">
            <div class="edit-1__admin-card">
                <div class="edit-1__admin-alert">
                    <mc-icon name="exclamation"></mc-icon>
                    <p>
                        <?php i::_e('Essas são organizações criadas por outros agentes e que você administra. Você pode fazer edições e excluir/adicionar novos membros nessas organizações, mas você não pode excluí-las.') ?>
                    </p>
                </div>

                <mc-entities
                    type="agent"
                    name="edit-orgs-admin"
                    :select="selectFields"
                    :query="adminQuery"
                    watch-query
                    @fetch="onAdminLoaded">
                    <template #default="{entities}">
                        <ul v-if="entities.length" class="entity-admins-edit__items">
                            <li v-for="org in entities" :key="org.id" class="entity-admins-edit__item">
                                <div class="entity-admins-edit__avatar">
                                    <mc-avatar :entity="org" size="medium"></mc-avatar>
                                </div>
                                <div class="entity-admins-edit__content">
                                    <a class="entity-admins-edit__name" :href="org.singleUrl">{{ org.name }}</a>
                                    <p v-if="org.parent?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Dono(a) da organização:') ?></span>
                                        <a v-if="org.parent.singleUrl" class="entity-admins-edit__name entity-admins-edit__name--inline" :href="org.parent.singleUrl">{{ org.parent.name }}</a>
                                        <span v-else>{{ org.parent.name }}</span>
                                    </p>
                                    <p v-if="org.type?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de organização:') ?></span>
                                        {{ org.type.name }}
                                    </p>
                                    <p v-if="areas(org).length" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label">
                                            <?php i::_e('Áreas de atuação') ?> ({{ areas(org).length }}):
                                        </span>
                                        <span
                                            v-for="area in areas(org)"
                                            :key="area"
                                            class="entity-admins-edit__area">
                                            {{ String(area).toUpperCase() }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(org.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização criada em:') ?>
                                            {{ formatDate(org.createTimestamp) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="entity-admins-edit__actions">
                                    <mc-link :entity="org" route="edit" class="button button--primary-outline button--icon button--sm entity-admins-edit__edit">
                                        <mc-icon name="edit"></mc-icon>
                                        <?php i::_e('editar organização') ?>
                                    </mc-link>
                                </div>
                            </li>
                        </ul>
                    </template>
                    <template #empty>
                        <p class="entity-admins-edit__empty"><?php i::_e('Você não administra nenhuma organização.') ?></p>
                    </template>
                </mc-entities>
            </div>
        </mc-tab>

        <mc-tab
            label="<?= i::esc_attr_e('Colaboro') ?>"
            slug="orgs-collab"
            :meta="{ count: collabCount }">
            <div class="edit-1__admin-card">
                <div class="edit-1__admin-alert">
                    <mc-icon name="exclamation"></mc-icon>
                    <p><?php i::_e('Essas são organizações criadas por outros agentes e que você participa.') ?></p>
                </div>

                <mc-entities
                    v-if="collabIds.length"
                    type="agent"
                    name="edit-orgs-collab"
                    :select="selectFields"
                    :ids="collabIds"
                    order="name ASC">
                    <template #default="{entities}">
                        <ul class="entity-admins-edit__items">
                            <li v-for="org in entities" :key="org.id" class="entity-admins-edit__item">
                                <div class="entity-admins-edit__avatar">
                                    <mc-avatar :entity="org" size="medium"></mc-avatar>
                                </div>
                                <div class="entity-admins-edit__content">
                                    <a class="entity-admins-edit__name" :href="org.singleUrl">{{ org.name }}</a>
                                    <p v-if="org.parent?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Dono(a) da organização:') ?></span>
                                        <a v-if="org.parent.singleUrl" class="entity-admins-edit__name entity-admins-edit__name--inline" :href="org.parent.singleUrl">{{ org.parent.name }}</a>
                                        <span v-else>{{ org.parent.name }}</span>
                                    </p>
                                    <p v-if="org.type?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de organização:') ?></span>
                                        {{ org.type.name }}
                                    </p>
                                    <p v-if="areas(org).length" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label">
                                            <?php i::_e('Áreas de atuação') ?> ({{ areas(org).length }}):
                                        </span>
                                        <span
                                            v-for="area in areas(org)"
                                            :key="area"
                                            class="entity-admins-edit__area">
                                            {{ String(area).toUpperCase() }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(org.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização criada em:') ?>
                                            {{ formatDate(org.createTimestamp) }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(collabRelationFor(org.id)?.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Colaboro desde:') ?>
                                            {{ formatDate(collabRelationFor(org.id)?.createTimestamp) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="entity-admins-edit__actions">
                                    <mc-confirm-button @confirm="leaveOrganization(org)">
                                        <template #button="modal">
                                            <button type="button" class="button button--icon button--sm entity-admins-edit__delete" @click="modal.open()">
                                                <mc-icon name="close"></mc-icon>
                                                <?php i::_e('sair da organização') ?>
                                            </button>
                                        </template>
                                        <template #message="message">
                                            {{ text('leaveConfirm') }}
                                        </template>
                                    </mc-confirm-button>
                                    <a :href="org.singleUrl" class="button button--primary-outline button--icon button--sm entity-admins-edit__edit">
                                        <?php i::_e('ver detalhes') ?>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </template>
                    <template #empty>
                        <p class="entity-admins-edit__empty"><?php i::_e('Você não colabora em nenhuma organização.') ?></p>
                    </template>
                </mc-entities>
                <p v-else class="entity-admins-edit__empty"><?php i::_e('Você não colabora em nenhuma organização.') ?></p>
            </div>
        </mc-tab>

        <mc-tab
            label="<?= i::esc_attr_e('Pendentes') ?>"
            slug="orgs-pending"
            :meta="{ count: pendingCount, danger: true }">
            <div class="edit-1__admin-card">
                <div class="edit-1__admin-alert">
                    <mc-icon name="exclamation"></mc-icon>
                    <p><?php i::_e('Essas são organizações para as quais você foi convidado para participar ou administrar.') ?></p>
                </div>

                <mc-entities
                    v-if="pendingIds.length"
                    type="agent"
                    name="edit-orgs-pending"
                    :select="selectFields"
                    :ids="pendingIds"
                    order="name ASC">
                    <template #default="{entities}">
                        <ul v-if="entities.length" class="entity-admins-edit__items">
                            <li v-for="org in entities" :key="org.id" class="entity-admins-edit__item">
                                <div class="entity-admins-edit__avatar">
                                    <mc-avatar :entity="org" size="medium"></mc-avatar>
                                </div>
                                <div class="entity-admins-edit__content">
                                    <a class="entity-admins-edit__name" :href="org.singleUrl">{{ org.name }}</a>
                                    <p class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Você foi convidado para ser:') ?></span>
                                        <strong>{{ pendingRoleLabel(org.id) }}</strong>
                                    </p>
                                    <p v-if="org.parent?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Dono(a) da organização:') ?></span>
                                        <a v-if="org.parent.singleUrl" class="entity-admins-edit__name entity-admins-edit__name--inline" :href="org.parent.singleUrl">{{ org.parent.name }}</a>
                                        <span v-else>{{ org.parent.name }}</span>
                                    </p>
                                    <p v-if="org.type?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de organização:') ?></span>
                                        {{ org.type.name }}
                                    </p>
                                    <p v-if="areas(org).length" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label">
                                            <?php i::_e('Áreas de atuação') ?> ({{ areas(org).length }}):
                                        </span>
                                        <span
                                            v-for="area in areas(org)"
                                            :key="area"
                                            class="entity-admins-edit__area">
                                            {{ String(area).toUpperCase() }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(org.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização criada em:') ?>
                                            {{ formatDate(org.createTimestamp) }}
                                        </span>
                                    </p>
                                </div>
                            </li>
                        </ul>
                        <p v-else class="entity-admins-edit__empty"><?php i::_e('Nenhum convite pendente.') ?></p>
                    </template>
                </mc-entities>
                <p v-else class="entity-admins-edit__empty">
                    <?php i::_e('Nenhum convite pendente. Os convites também podem ser aceitos ou recusados pelas notificações da plataforma.') ?>
                </p>
            </div>
        </mc-tab>

        <mc-tab
            label="<?= i::esc_attr_e('Transferi') ?>"
            slug="orgs-transferred"
            :meta="{ count: transferredCount }">
            <div class="edit-1__admin-card">
                <div class="edit-1__admin-alert">
                    <mc-icon name="exclamation"></mc-icon>
                    <p><?php i::_e('Essas são organizações criadas por você que foram transferidas para outros agentes.') ?></p>
                </div>

                <mc-entities
                    v-if="transferredIds.length"
                    type="agent"
                    name="edit-orgs-transferred"
                    :select="selectFields"
                    :ids="transferredIds"
                    order="name ASC">
                    <template #default="{entities}">
                        <ul class="entity-admins-edit__items">
                            <li v-for="org in entities" :key="org.id" class="entity-admins-edit__item">
                                <div class="entity-admins-edit__avatar">
                                    <mc-avatar :entity="org" size="medium"></mc-avatar>
                                </div>
                                <div class="entity-admins-edit__content">
                                    <a class="entity-admins-edit__name" :href="org.singleUrl">{{ org.name }}</a>
                                    <p v-if="org.parent?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Organização transferida para:') ?></span>
                                        <a v-if="org.parent.singleUrl" class="entity-admins-edit__name entity-admins-edit__name--inline" :href="org.parent.singleUrl">{{ org.parent.name }}</a>
                                        <span v-else>{{ org.parent.name }}</span>
                                    </p>
                                    <p v-if="org.type?.name" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de organização:') ?></span>
                                        {{ org.type.name }}
                                    </p>
                                    <p v-if="areas(org).length" class="entity-admins-edit__meta">
                                        <span class="entity-admins-edit__meta-label">
                                            <?php i::_e('Áreas de atuação') ?> ({{ areas(org).length }}):
                                        </span>
                                        <span
                                            v-for="area in areas(org)"
                                            :key="area"
                                            class="entity-admins-edit__area">
                                            {{ String(area).toUpperCase() }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(org.createTimestamp)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização criada em:') ?>
                                            {{ formatDate(org.createTimestamp) }}
                                        </span>
                                    </p>
                                    <p v-if="formatDate(transferredFor(org.id)?.transferredAt)" class="entity-admins-edit__date">
                                        <mc-icon name="date"></mc-icon>
                                        <span>
                                            <?php i::_e('Organização transferida em:') ?>
                                            {{ formatDate(transferredFor(org.id)?.transferredAt) }}
                                        </span>
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </template>
                    <template #empty>
                        <p class="entity-admins-edit__empty"><?php i::_e('Você ainda não transferiu nenhuma organização.') ?></p>
                    </template>
                </mc-entities>
                <p v-else class="entity-admins-edit__empty"><?php i::_e('Você ainda não transferiu nenhuma organização.') ?></p>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
