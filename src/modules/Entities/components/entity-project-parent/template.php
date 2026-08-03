<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-icon
    select-entity
');
?>
<div :class="['entity-project-parent', classes]">
    <div v-if="parent" class="entity-project-parent__linked">
        <h4 class="entity-project-parent__title bold">{{ entity.name }} {{ text('linkedTo') }}</h4>

        <a class="entity-project-parent__parent" :href="parent.singleUrl" :title="parent.shortDescription">
            <div class="entity-project-parent__parent-img">
                <mc-avatar :entity="parent" size="small"></mc-avatar>
            </div>
            <div class="entity-project-parent__parent-name">
                {{ parent.name }}
            </div>
        </a>

        <div class="entity-project-parent__actions">
            <select-entity type="project" @select="changeParent($event)" :query="query" openside="right-down">
                <template #button="{ toggle }">
                    <button type="button" class="button button--primary-outline button--icon" @click="toggle()">
                        <mc-icon name="exchange"></mc-icon>
                        {{ text('change') }}
                    </button>
                </template>
            </select-entity>

            <button type="button" class="button button--text-danger button--icon entity-project-parent__remove" @click="removeParent()">
                <mc-icon name="trash"></mc-icon>
                {{ text('remove') }}
            </button>
        </div>
    </div>

    <div v-else class="entity-project-parent__empty">
        <p class="entity-project-parent__empty-message">{{ emptyMessage }}</p>

        <select-entity type="project" @select="changeParent($event)" :query="query" openside="right-down">
            <template #button="{ toggle }">
                <button type="button" class="button button--primary button--icon" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    {{ text('link') }}
                </button>
            </template>
        </select-entity>
    </div>
</div>
