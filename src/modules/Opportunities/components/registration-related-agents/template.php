<?php
/**
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    select-entity
');
?>
<mc-card v-for="relation in agentRelations" v-if="showCardForRelation">
    <template #title>
        <div class="card__title"> 
            {{relation.label}} 
            <div v-if="relation.required" class="obrigatory"> <?= i::__('* Obrigatório') ?> </div>
        </div>
        <div class="card__subtitle">
            {{relation.description}}
        </div>
    </template>
    <template #content>
        <div v-if="hasRelations(registration.relatedAgents[relation.agentRelationGroupName])" class="registration-related-entity">
            <div class="registration-related-entity__entity">
                <mc-avatar :entity="registration.relatedAgents[relation.agentRelationGroupName][0]" size="small"></mc-avatar>
                <div class="name">
                    {{registration.relatedAgents[relation.agentRelationGroupName][0].name}}
                </div>
            </div>
            <div class="registration-related-entity__actions">
                <select-entity type="agent" :query="{'type': `EQ(${relation.type})`}" @select="selectAgent($event, relation)" permissions="">
                    <template #button="{toggle}">
                        <button class="button button--text button--icon button--sm change" @click="toggle()"> 
                            <mc-icon name="exchange"></mc-icon> <?= i::__('Trocar') ?> 
                        </button>
                    </template>
                </select-entity>
                <button class="button button--text button--icon button--sm delete" @click="removeAgent(registration.relatedAgents[relation.agentRelationGroupName][0], relation)"> 
                    <mc-icon name="trash"></mc-icon> <?= i::__('Excluir') ?> 
                </button>
            </div>
            <div v-if="registration.relatedAgents[relation.agentRelationGroupName][0].relationStatus == -5" class="registration-related-entity__status">
                <mc-icon name="exclamation"></mc-icon>
                <?= i::__('A solicitação está pendente') ?>
            </div>
        </div>
        
        <select-entity v-if="!hasRelations(registration.relatedAgents[relation.agentRelationGroupName])" type="agent" :query="{'type': `EQ(${relation.type})`}" @select="selectAgent($event, relation)" permissions="">
            <template #button="{toggle}">
                <button class="button button--primary-outline button--icon button--md" @click="toggle()"> 
                    <mc-icon name="add"></mc-icon> <?= i::__('Adicionar') ?> 
                </button>
            </template>
        </select-entity>
        
        <div v-if="registration.__validationErrors[`agent_${relation.agentRelationGroupName}`]" class="errors">
            <span>{{registration.__validationErrors[`agent_${relation.agentRelationGroupName}`].join('; ')}}</span>
        </div>
    </template>
</mc-card>