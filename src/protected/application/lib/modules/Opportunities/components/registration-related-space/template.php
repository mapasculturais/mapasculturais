<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 * 
 * @todo renomear componente
 */

use MapasCulturais\i;
?>

<mapas-card v-for="relation in spaceRelation">
    <template #title>
        <div class="card__title"> 
            {{relation.label}}
        </div>
        <div class="card__subtitle">
            {{relation.description}}
        </div>
    </template>
    <template #content>
        <!-- v-if="hasRelations()" -->
        <div class="registration-select-entity">
            <div class="registration-select-entity__entity">
                <div class="image">
                    <!-- <img v-if="registration.relatedAgents[relation.agentRelationGroupName][0].files.avatar" :src="registration.relatedAgents[relation.agentRelationGroupName][0].files?.avatar?.transformations?.avatarMedium.url" /> -->
                    <mc-icon name="image"></mc-icon> <!-- v-if="!registration.relatedAgents[relation.agentRelationGroupName][0].files.avatar" -->
                </div>
                <div class="name">
                    <!-- {{registration.relatedAgents[relation.agentRelationGroupName][0].name}} -->
                </div>
            </div>
            <div class="registration-select-entity__actions">
                <select-entity type="agent" :query="{'type': `EQ(${relation.type})`}" @select="selectAgent()">
                    <template #button="{toggle}">
                        <button class="button button--text button--icon button--sm change" @click="toggle()"> 
                            <mc-icon name="exchange"></mc-icon> <?= i::__('Trocar') ?> 
                        </button>
                    </template>
                </select-entity>
                <button class="button button--text button--icon button--sm delete" @click="removeAgent()"> 
                    <mc-icon name="trash"></mc-icon> <?= i::__('Excluir') ?> 
                </button>
            </div>
        </div>
        <!-- v-if="!hasRelations()" -->
        <select-entity  type="agent" :query="{'type': `EQ(${relation.type})`}" @select="selectAgent($event, relation)">
            <template #button="{toggle}">
                <button class="button button--primary-outline button--icon button--md" @click="toggle()"> 
                    <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?> 
                </button>
            </template>
        </select-entity>
    </template>
</mapas-card>