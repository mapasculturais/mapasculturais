<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon
');
?>
<?php $this->applyTemplateHook('entity-people-collaborators', 'before'); ?>

<div :class="['entity-people-collaborators', classes, { 'entity-people-collaborators--preview': preview }]">
    <?php $this->applyTemplateHook('entity-people-collaborators', 'begin'); ?>

    <template v-if="preview">
        <div class="single-1__people-preview">
            <div class="single-1__people-preview-card">
                <div class="single-1__people-preview-header">
                    <h3 class="single-1__people-preview-title">
                        {{ text('people') }} <template v-if="totalCount">({{ totalCount }})</template>
                    </h3>
                    <a class="single-1__people-preview-see-all" href="#colaboradores">
                        {{ text('seeAll') }}
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
        <p v-if="!hasItems" class="entity-people-collaborators__empty">{{ emptyMessage || text('empty') }}</p>

        <template v-else>
            <div class="entity-people-collaborators__toolbar">
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

            <template v-if="viewMode === 'group'">
                <section
                    v-for="group in groups"
                    :key="group.name"
                    class="entity-people-collaborators__section">
                    <h3 class="entity-people-collaborators__group-title">{{ group.name }}</h3>
                    <div class="entity-people-collaborators__section-card">
                        <ul class="entity-connections-list__items">
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
                            </li>
                        </ul>
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
    </template>

    <?php $this->applyTemplateHook('entity-people-collaborators', 'end'); ?>
</div>

<?php $this->applyTemplateHook('entity-people-collaborators', 'after'); ?>
