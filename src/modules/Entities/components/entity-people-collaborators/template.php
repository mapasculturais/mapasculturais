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
    mc-popover
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-people-collaborators', 'before'); ?>

<div :class="['entity-people-collaborators', classes, { 'entity-people-collaborators--preview': preview, 'entity-people-collaborators--editable': editable }]">
    <?php $this->applyTemplateHook('entity-people-collaborators', 'begin'); ?>

    <template v-if="preview">
        <div class="single-1__people-preview">
            <div class="single-1__people-preview-card">
                <div class="single-1__people-preview-header">
                    <h3 class="single-1__people-preview-title">
                        {{ text('people') }} <template v-if="totalCount">({{ totalCount }})</template>
                    </h3>
                        <a class="single-1__people-preview-see-all" href="#pessoas">
                            {{ manage ? text('managePeople') : text('seeAll') }}
                        <mc-icon name="arrow-right"></mc-icon>
                    </a>
                </div>

                <p v-if="!hasItems" class="single-1__people-preview-empty">
                    {{ emptyMessage || text('empty') }}
                </p>

                <ul v-else class="single-1__people-preview-list">
                    <li v-for="agent in previewAgents" :key="agent.id" class="single-1__people-preview-item">
                        <a
                            class="single-1__people-preview-link"
                            :href="agent.singleUrl"
                            :title="agent.name">
                            <mc-avatar :entity="agent" size="small"></mc-avatar>
                            <span class="single-1__people-preview-name">{{ agent.name }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </template>

    <template v-else>
        <div v-if="editable" class="entity-people-collaborators__edit-toolbar">
            <mc-popover openside="down-right">
                <template #button="popover">
                    <button type="button" class="button button--primary button--icon" @click="popover.toggle()">
                        <mc-icon name="add"></mc-icon>
                        {{ text('createGroup') }}
                    </button>
                </template>
                <template #default="{close}">
                    <div class="entity-people-collaborators__new-group">
                        <form @submit.prevent="addGroup(newGroupName); close();">
                            <div class="grid-12">
                                <div class="col-12">
                                    <input
                                        v-model="newGroupName"
                                        class="input"
                                        type="text"
                                        name="newGroup"
                                        maxlength="64"
                                        :placeholder="text('groupNamePlaceholder')" />
                                </div>
                                <button class="col-6 button button--text" type="button" @click="close">{{ text('cancel') }}</button>
                                <button class="col-6 button button--primary" type="submit">{{ text('confirm') }}</button>
                            </div>
                        </form>
                    </div>
                </template>
            </mc-popover>
        </div>

        <div v-else class="entity-people-collaborators__toolbar">
            <span class="entity-people-collaborators__toolbar-label"><?php i::_e('Visualizar por:'); ?></span>
            <div class="entity-people-collaborators__toggle" role="group" aria-label="<?php i::esc_attr_e('Modo de listagem'); ?>">
                <button
                    type="button"
                    class="entity-people-collaborators__toggle-btn"
                    :class="{ 'entity-people-collaborators__toggle-btn--active': viewMode === 'group' }"
                    @click="setViewMode('group')">
                    {{ text('group') }}
                </button>
                <button
                    type="button"
                    class="entity-people-collaborators__toggle-btn"
                    :class="{ 'entity-people-collaborators__toggle-btn--active': viewMode === 'people' }"
                    @click="setViewMode('people')">
                    {{ text('people') }}
                </button>
            </div>
        </div>

        <p v-if="!hasGroups && !hasItems" class="entity-people-collaborators__empty">{{ emptyMessage || text('empty') }}</p>

        <template v-else-if="editable || viewMode === 'group'">
            <section
                v-for="group in groups"
                :key="group.name"
                class="entity-people-collaborators__section">
                <div class="entity-people-collaborators__group-header">
                    <h3 class="entity-people-collaborators__group-title">
                        {{ group.name }}
                        <template v-if="editable">({{ group.agents.length }})</template>
                    </h3>

                    <mc-confirm-button v-if="editable" @confirm="removeGroup(group.name)">
                        <template #button="modal">
                            <button type="button" class="button button--text-danger button--sm" @click="modal.open()">
                                {{ text('deleteGroup') }}
                            </button>
                        </template>
                        <template #message="message">
                            {{ text('deleteGroupConfirm') }}
                        </template>
                    </mc-confirm-button>
                </div>

                <div class="entity-people-collaborators__section-card">
                    <ul v-if="group.agents.length" class="entity-connections-list__items">
                        <li v-for="item in group.agents" :key="item.id" class="entity-connections-list__item">
                            <div class="entity-connections-list__avatar">
                                <mc-avatar :entity="item" size="medium"></mc-avatar>
                            </div>
                            <div class="entity-connections-list__content">
                                <div class="entity-connections-list__header">
                                    <a :href="item.singleUrl" class="entity-connections-list__name">{{ item.name }}</a>
                                    <div v-if="agentTags(item).length" class="entity-people-collaborators__tags">
                                        <span
                                            v-for="tag in agentTags(item)"
                                            :key="tag"
                                            class="entity-people-collaborators__tag">
                                            {{ tag }}
                                        </span>
                                    </div>
                                </div>
                                <p v-if="item.type?.name" class="entity-connections-list__meta">
                                    <span class="entity-connections-list__meta-label">{{ text('typeAgent') }}</span>
                                    {{ item.type.name }}
                                </p>
                                <p v-if="areas(item).length" class="entity-connections-list__meta">
                                    <span class="entity-connections-list__meta-label">
                                        {{ text('areas') }} ({{ areas(item).length }}):
                                    </span>
                                    <span class="entity-connections-list__areas">{{ areasText(item) }}</span>
                                </p>
                            </div>

                            <div v-if="editable" class="entity-people-collaborators__agent-actions">
                                <mc-confirm-button @confirm="removeAgent(group.name, item)">
                                    <template #button="modal">
                                        <button type="button" class="button button--icon button--sm entity-people-collaborators__delete-agent" @click="modal.open()">
                                            {{ text('deleteAgent') }}
                                        </button>
                                    </template>
                                    <template #message="message">
                                        {{ text('deleteAgentConfirm') }}
                                    </template>
                                </mc-confirm-button>
                            </div>
                        </li>
                    </ul>

                    <div v-if="editable" class="entity-people-collaborators__group-actions">
                        <select-entity
                            type="agent"
                            permissions=""
                            select="id,name,files.avatar,terms,type,singleUrl"
                            :query="queries[group.name]"
                            openside="down-right"
                            @select="addAgent(group.name, $event)">
                            <template #button="{ toggle }">
                                <button type="button" class="button button--primary button--icon" @click="toggle()">
                                    <mc-icon name="add"></mc-icon>
                                    {{ text('addAgent') }}
                                </button>
                            </template>
                        </select-entity>
                    </div>
                </div>
            </section>
        </template>

        <template v-else>
            <div class="entity-people-collaborators__section-card">
                <ul class="entity-connections-list__items">
                    <li v-for="item in sortedFlatAgents" :key="item.id" class="entity-connections-list__item">
                        <div class="entity-connections-list__avatar">
                            <mc-avatar :entity="item" size="medium"></mc-avatar>
                        </div>
                        <div class="entity-connections-list__content">
                            <div class="entity-connections-list__header">
                                <a :href="item.singleUrl" class="entity-connections-list__name">{{ item.name }}</a>
                                <div v-if="agentTags(item).length" class="entity-people-collaborators__tags">
                                    <span
                                        v-for="tag in agentTags(item)"
                                        :key="tag"
                                        class="entity-people-collaborators__tag">
                                        {{ tag }}
                                    </span>
                                </div>
                            </div>
                            <p v-if="item.type?.name" class="entity-connections-list__meta">
                                <span class="entity-connections-list__meta-label">{{ text('typeAgent') }}</span>
                                {{ item.type.name }}
                            </p>
                            <p v-if="areas(item).length" class="entity-connections-list__meta">
                                <span class="entity-connections-list__meta-label">
                                    {{ text('areas') }} ({{ areas(item).length }}):
                                </span>
                                <span class="entity-connections-list__areas">{{ areasText(item) }}</span>
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </template>
    </template>

    <?php $this->applyTemplateHook('entity-people-collaborators', 'end'); ?>
</div>

<?php $this->applyTemplateHook('entity-people-collaborators', 'after'); ?>
