<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-avatar
    mc-icon
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-people-owner', 'before'); ?>

<div :class="['entity-people-owner', classes]">
    <?php $this->applyTemplateHook('entity-people-owner', 'begin'); ?>

    <div class="entity-people-owner__intro">
        <p>{{ text('intro') }}</p>
    </div>

    <p v-if="!owner" class="entity-people-owner__empty">{{ emptyMessage || text('empty') }}</p>

    <div v-else class="entity-people-owner__card">
        <div class="entity-people-owner__item">
            <div class="entity-people-owner__avatar">
                <mc-avatar :entity="owner" size="medium"></mc-avatar>
            </div>

            <div class="entity-people-owner__content">
                <div class="entity-people-owner__header">
                    <a :href="owner.singleUrl" class="entity-people-owner__name">{{ owner.name }}</a>
                    <div v-if="ownerTags.length" class="entity-people-owner__tags">
                        <span
                            v-for="tag in ownerTags"
                            :key="tag"
                            class="entity-people-owner__tag">
                            {{ tag }}
                        </span>
                    </div>
                </div>

                <p v-if="owner.type?.name" class="entity-people-owner__meta">
                    <span class="entity-people-owner__meta-label">{{ text('typeAgent') }}</span>
                    {{ owner.type.name }}
                </p>

                <p v-if="areas(owner).length" class="entity-people-owner__meta">
                    <span class="entity-people-owner__meta-label">
                        {{ text('areas') }} ({{ areas(owner).length }}):
                    </span>
                    <span class="entity-people-owner__areas">{{ areasText(owner) }}</span>
                </p>

                <div class="entity-people-owner__footer">
                    <p v-if="formatDate(entity.createTimestamp)" class="entity-people-owner__date">
                        <mc-icon name="date"></mc-icon>
                        <span>
                            <span class="entity-people-owner__meta-label">{{ text('ownerSince') }}</span>
                            {{ formatDate(entity.createTimestamp) }}
                        </span>
                    </p>

                    <div class="entity-people-owner__actions">
                        <select-entity
                            v-if="!hasRequest"
                            type="agent"
                            permissions=""
                            select="id,name,type,files.avatar,terms,singleUrl"
                            :query="query"
                            openside="up-right"
                            @select="changeOwner($event)">
                            <template #button="{ toggle }">
                                <button type="button" class="button button--primary-outline button--icon button--sm entity-people-owner__transfer" @click="toggle()">
                                    <mc-icon name="exchange"></mc-icon>
                                    {{ text('transfer') }}
                                </button>
                            </template>
                        </select-entity>
                    </div>
                </div>
            </div>
        </div>

        <mc-alert v-if="hasRequest" type="warning" class="entity-people-owner__pending">
            <div>
                {{ text('pending') }} <strong>{{ destinationName }}</strong>
            </div>
        </mc-alert>
    </div>

    <?php $this->applyTemplateHook('entity-people-owner', 'end'); ?>
</div>

<?php $this->applyTemplateHook('entity-people-owner', 'after'); ?>
