<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
    mc-icon
    mc-tab
    mc-tabs
    select-entity
');
?>
<div class="edit-1__admin edit-1__spaces edit-1__inner-tabs entity-spaces-edit">
    <mc-tabs class="tabs" sync-hash default-tab="vinculados">
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
            label="<?= i::esc_attr_e('Espaços vinculados') ?>"
            slug="vinculados"
            :meta="{ count: linkedCount }">
            <div class="edit-1__admin-card">
                <p class="edit-1__admin-section-text">{{ text('linkedDescription') }}</p>

                <ul v-if="linkedSpaces.length" class="entity-spaces-edit__items">
                    <li v-for="item in linkedSpaces" :key="item.key" class="entity-spaces-edit__item">
                        <div class="entity-spaces-edit__avatar">
                            <mc-avatar :entity="item.space" size="medium"></mc-avatar>
                        </div>
                        <div class="entity-spaces-edit__content">
                            <div class="entity-spaces-edit__header">
                                <a v-if="item.space.singleUrl" class="entity-spaces-edit__name" :href="item.space.singleUrl">{{ item.space.name }}</a>
                                <span v-else class="entity-spaces-edit__name">{{ item.space.name }}</span>
                                <span v-if="item.space.id" class="entity-spaces-edit__id">
                                    {{ text('identifier') }} {{ item.space.id }}
                                </span>
                            </div>
                            <p v-if="item.space.type?.name" class="entity-spaces-edit__meta">
                                <span class="entity-spaces-edit__meta-label">{{ text('type') }}:</span>
                                {{ item.space.type.name }}
                            </p>
                            <p v-if="item.space.shortDescription" class="entity-spaces-edit__description">
                                {{ item.space.shortDescription }}
                            </p>
                            <p v-if="item.space.endereco" class="entity-spaces-edit__address">
                                <mc-icon name="pin"></mc-icon>
                                <span>{{ item.space.endereco }}</span>
                            </p>
                            <p v-if="areas(item.space).length" class="entity-spaces-edit__meta entity-spaces-edit__areas">
                                <span class="entity-spaces-edit__meta-label">
                                    {{ text('areas') }} ({{ areas(item.space).length }}):
                                </span>
                                {{ areasText(item.space) }}
                            </p>
                        </div>
                        <div v-if="editable" class="entity-spaces-edit__actions">
                            <mc-confirm-button @confirm="removeLink(item)">
                                <template #button="modal">
                                    <button type="button" class="button button--icon button--sm entity-spaces-edit__delete" @click="modal.open()">
                                        <mc-icon name="trash"></mc-icon>
                                        {{ text('removeLink') }}
                                    </button>
                                </template>
                                <template #message="message">
                                    <?php i::_e('Excluir este vínculo?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </li>
                </ul>
                <p v-else class="entity-admins-edit__empty">{{ text('emptyLinked') }}</p>

                <div v-if="editable" class="entity-spaces-edit__add">
                    <select-entity type="space" :query="selectQuery" @select="addLink($event)" openside="up-right">
                        <template #button="{ toggle }">
                            <button type="button" class="button button--primary button--icon entity-spaces-edit__add-btn" @click="toggle()">
                                <mc-icon name="add"></mc-icon>
                                {{ text('add') }}
                            </button>
                        </template>
                    </select-entity>
                </div>
            </div>
        </mc-tab>

        <mc-tab
            label="<?= i::esc_attr_e('Espaços com vínculo pendente') ?>"
            slug="pendentes"
            :meta="{ count: pendingCount, danger: pendingCount > 0 }">
            <div class="edit-1__admin-stack">
                <div class="edit-1__admin-card">
                    <h3 class="edit-1__admin-section-title">{{ text('pendingSentTitle') }}</h3>
                    <p class="edit-1__admin-section-text">{{ text('pendingSentDescription') }}</p>

                    <ul v-if="pendingSent.length" class="entity-spaces-edit__items">
                        <li v-for="item in pendingSent" :key="'sent-' + item.requestId" class="entity-spaces-edit__item">
                            <div class="entity-spaces-edit__avatar">
                                <mc-avatar :entity="item.space" size="medium"></mc-avatar>
                            </div>
                            <div class="entity-spaces-edit__content">
                                <a v-if="item.space?.singleUrl" class="entity-spaces-edit__name" :href="item.space.singleUrl">{{ item.space.name }}</a>
                                <span v-else class="entity-spaces-edit__name">{{ item.space?.name }}</span>
                                <p v-if="item.space?.type?.name" class="entity-spaces-edit__meta">
                                    <span class="entity-spaces-edit__meta-label">{{ text('type') }}:</span>
                                    {{ item.space.type.name }}
                                </p>
                            </div>
                            <div class="entity-spaces-edit__actions">
                                <button
                                    v-if="item.notificationId"
                                    type="button"
                                    class="button button--icon button--sm entity-spaces-edit__delete"
                                    @click="rejectRequest(item)">
                                    <mc-icon name="close"></mc-icon>
                                    {{ text('cancelRequest') }}
                                </button>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="entity-admins-edit__empty">{{ text('emptyPendingSent') }}</p>
                </div>

                <div class="edit-1__admin-card">
                    <h3 class="edit-1__admin-section-title">{{ text('pendingReceivedTitle') }}</h3>
                    <p class="edit-1__admin-section-text">{{ text('pendingReceivedDescription') }}</p>

                    <ul v-if="pendingReceived.length" class="entity-spaces-edit__items">
                        <li v-for="item in pendingReceived" :key="'received-' + item.requestId" class="entity-spaces-edit__item">
                            <div class="entity-spaces-edit__avatar">
                                <mc-avatar :entity="item.space" size="medium"></mc-avatar>
                            </div>
                            <div class="entity-spaces-edit__content">
                                <a v-if="item.space?.singleUrl" class="entity-spaces-edit__name" :href="item.space.singleUrl">{{ item.space.name }}</a>
                                <span v-else class="entity-spaces-edit__name">{{ item.space?.name }}</span>
                                <p v-if="item.space?.type?.name" class="entity-spaces-edit__meta">
                                    <span class="entity-spaces-edit__meta-label">{{ text('type') }}:</span>
                                    {{ item.space.type.name }}
                                </p>
                            </div>
                            <div v-if="item.notificationId" class="entity-spaces-edit__actions entity-spaces-edit__actions--pair">
                                <button type="button" class="button button--icon button--sm entity-spaces-edit__delete" @click="rejectRequest(item)">
                                    <mc-icon name="close"></mc-icon>
                                    {{ text('rejectRequest') }}
                                </button>
                                <button type="button" class="button button--primary button--icon button--sm" @click="approveRequest(item)">
                                    <mc-icon name="check"></mc-icon>
                                    {{ text('acceptRequest') }}
                                </button>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="entity-admins-edit__empty">{{ text('emptyPendingReceived') }}</p>
                </div>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
