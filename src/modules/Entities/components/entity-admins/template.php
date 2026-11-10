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
    mc-relation-card
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-related-agents', 'before'); ?>

<template v-if="variant === 'list'">
    <div :class="classes" class="entity-admins-list" v-show="group.length > 0">
        <?php $this->applyTemplateHook('entity-related-agents', 'begin'); ?>
        <ul class="entity-admins-list__items">
            <li v-for="relation in group" :key="relation.id" class="entity-admins-list__item">
                <a class="entity-admins-list__link" :href="relation.agent.singleUrl" :title="relation.agent.name">
                    <mc-avatar :entity="relation.agent" size="small"></mc-avatar>
                    <div class="entity-admins-list__info">
                        <span class="entity-admins-list__name">{{ relation.agent.name }}</span>
                        <span v-if="formatRelationDate(relation)" class="entity-admins-list__since">
                            <?php i::_e('desde') ?> {{ formatRelationDate(relation) }}
                        </span>
                    </div>
                </a>
            </li>
        </ul>
        <?php $this->applyTemplateHook('entity-related-agents', 'end'); ?>
    </div>
</template>

<template v-else-if="variant === 'edit'">
    <div :class="['entity-admins-edit', classes]">
        <?php $this->applyTemplateHook('entity-related-agents', 'begin'); ?>

        <ul v-if="activeRelations.length" class="entity-admins-edit__items">
            <li v-for="relation in activeRelations" :key="relation.id" class="entity-admins-edit__item">
                <div class="entity-admins-edit__avatar">
                    <mc-avatar :entity="relation.agent" size="medium"></mc-avatar>
                </div>

                <div class="entity-admins-edit__content">
                    <a class="entity-admins-edit__name" :href="relation.agent.singleUrl">{{ relation.agent.name }}</a>

                    <p v-if="relation.agent.type?.name" class="entity-admins-edit__meta">
                        <span class="entity-admins-edit__meta-label"><?php i::_e('Tipo de agente:') ?></span>
                        {{ relation.agent.type.name }}
                    </p>

                    <p v-if="areas(relation.agent).length" class="entity-admins-edit__meta">
                        <span class="entity-admins-edit__meta-label">
                            <?php i::_e('Áreas de atuação') ?> ({{ areas(relation.agent).length }}):
                        </span>
                        <span
                            v-for="area in areas(relation.agent)"
                            :key="area"
                            class="entity-admins-edit__area">
                            {{ String(area).toUpperCase() }}
                        </span>
                    </p>

                    <p v-if="formatRelationDate(relation)" class="entity-admins-edit__date">
                        <mc-icon name="date"></mc-icon>
                        <span>
                            <?php i::_e('Administra meu perfil desde:') ?>
                            {{ formatRelationDate(relation) }}
                        </span>
                    </p>
                </div>

                <div v-if="isEditable" class="entity-admins-edit__actions">
                    <mc-confirm-button @confirm="removeAgent(relation.agent)">
                        <template #button="modal">
                            <button type="button" class="button button--icon button--sm entity-admins-edit__delete" @click="modal.open()">
                                <mc-icon name="trash"></mc-icon>
                                <?php i::_e('excluir administrador') ?>
                            </button>
                        </template>
                        <template #message="message">
                            <?php i::_e('Remover administrador?') ?>
                        </template>
                    </mc-confirm-button>
                </div>
            </li>
        </ul>

        <p v-else class="entity-admins-edit__empty"><?php i::_e('Nenhum administrador adicionado.') ?></p>

        <div v-if="isEditable" class="entity-admins-edit__add">
            <select-entity type="agent" permissions="" select="id,name,type,files.avatar,terms,singleUrl" @select="addAgent($event)" :query="query" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--primary button--icon edit-1__portfolio-cta" @click="toggle()">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e('Adicionar novo') ?>
                    </button>
                </template>
            </select-entity>
        </div>

        <?php $this->applyTemplateHook('entity-related-agents', 'end'); ?>
    </div>
</template>

<template v-else>
    <div :class="classes" class="entity-related-agents" v-if="isEditable || group.length > 0">
        <?php $this->applyTemplateHook('entity-related-agents', 'begin'); ?>
        <h4 class="bold" v-if="group">{{title}}
            <?php if($this->isEditable()): ?>
                <?php $this->info('cadastro -> configuracoes-entidades -> administradores-entidade') ?>
            <?php endif; ?>
        </h4>
        <div class="entity-related-agents__group">
            <div class="entity-related-agents__group--agents">
                <div v-for="relation in group" class="agent">
                    <mc-relation-card :relation="relation">
                        <template #default="{open, close, toggle}">
                            <a class="agent__img" @click="$event.preventDefault(); toggle()">
                               <mc-avatar :entity="relation.agent" size="small"></mc-avatar>
                            </a>
                        </template>
                    </mc-relation-card>
                    <!-- remover agente -->
                    <div v-if="isEditable" class="agent__delete">
                        <mc-confirm-button @confirm="removeAgent(relation.agent)">
                            <template #button="modal">
                                <mc-icon @click="modal.open()" name="delete"></mc-icon>
                            </template>
                            <template #message="message">
                                <?php i::_e('Remover agente relacionado?') ?>
                            </template>
                        </mc-confirm-button>
                    </div>
                    <!-- relação de agente pendente -->
                    <div v-if="relation.status == -5" class="agent__pending"></div>
                </div>
            </div>
            <div class="entity-related-agents__group--actions">
                <select-entity v-if="isEditable" type="agent" permissions="" select="id,name,files.avatar,terms,type" @select="addAgent($event)" :query="query" openside="down-right">
                    <template #button="{ toggle }">
                        <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()">
                            <?php i::_e('Adicionar administrador') ?>
                            <mc-icon name="add"></mc-icon>
                        </button>
                    </template>
                </select-entity>
            </div>
        </div>
        <?php $this->applyTemplateHook('entity-related-agents', 'end'); ?>
    </div>
</template>
<?php $this->applyTemplateHook('entity-related-agents', 'after'); ?>
