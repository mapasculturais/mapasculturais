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
<div :class="['entity-link-project', { 'entity-link-project--empty': !project }, classes]">
    <div v-if="project" class="entity-link-project__linked">
        <h4 class="entity-link-project__title bold">{{ entity.name }} {{ title }}</h4>

        <a class="entity-link-project__project" :href="project.singleUrl" :title="project.shortDescription">
            <div class="entity-link-project__project-img">
                <mc-avatar :entity="project" size="small"></mc-avatar>
            </div>
            <div class="entity-link-project__project-name">
                {{ project.name }}
            </div>
        </a>

        <div class="entity-link-project__actions">
            <select-entity :type="type" @select="changeProject($event)" :query="{ '@permissions': 'createEvents', id: `!EQ(${project.id})` }" openside="right-down">
                <template #button="{ toggle }">
                    <button type="button" class="button button--primary-outline button--icon" @click="toggle()">
                        <mc-icon name="exchange"></mc-icon>
                        <?php i::_e('Trocar projeto vinculado') ?>
                    </button>
                </template>
            </select-entity>

            <button type="button" class="button button--text-danger button--icon" @click="removeProject()">
                <mc-icon name="trash"></mc-icon>
                <?php i::_e('Remover vínculo') ?>
            </button>
        </div>
    </div>

    <div v-else class="entity-link-project__empty-state">
        <p class="entity-link-project__empty-message">{{ emptyMessage }}</p>

        <select-entity :type="type" @select="changeProject($event)" :query="{ '@permissions': 'createEvents' }" openside="right-down">
            <template #button="{ toggle }">
                <button type="button" class="button button--primary button--icon" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    {{ label }}
                </button>
            </template>
        </select-entity>
    </div>
</div>
