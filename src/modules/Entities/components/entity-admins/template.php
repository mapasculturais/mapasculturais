<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
    mc-relation-card
    select-entity
');
?>
<?php $this->applyTemplateHook('entity-related-agents', 'before'); ?>
<div :class="classes" class="entity-related-agents" v-if="editable || group.length > 0">
    <?php $this->applyTemplateHook('entity-related-agents', 'begin'); ?>
    <h4 class="bold" v-if="group"><?php i::_e("Administrado por") ?> <?php $this->info('cadastro -> cadastrando-usuario -> atualizar-perfil') ?></h4>
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
                <div v-if="editable" class="agent__delete">
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
            <select-entity v-if="editable" type="agent" permissions="" select="id,name,files.avatar,terms,type" @select="addAgent($event)" :query="query" openside="down-right">
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
<?php $this->applyTemplateHook('entity-related-agents', 'after'); ?>