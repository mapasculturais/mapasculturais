<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
    mc-popover
    mc-relation-card
');
?>
<div :class="classes" class="entity-related-agents" v-if="editable || hasGroups()">
    <h4 class="bold"><?php i::_e("Agentes relacionados") ?></h4>
    <div v-for="(relations, groupName) in groups" class="entity-related-agents__group">
        <div class="entity-related-agents__group--name">
            <label> {{groupName}} </label>
            <!-- botões de ação do grupo -->
            <div v-if="editable" class="act">
                <!-- renomear grupo -->
                <mc-popover openside="down-right">
                    <template #button="popover">
                        <slot name="button">
                            <a @click="popover.toggle()"> <mc-icon name="edit"></mc-icon> </a>
                        </slot>
                    </template>
                    <template #default="{popover, close}">
                        <form @submit="renameGroup(groupName, relations.newGroupName, popover); $event.preventDefault(); close()" class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="related-popover col-12">
                                    <input v-model="relations.newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" />
                                </div>

                                <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
                <!-- remover grupo -->
                <mc-confirm-button @confirm="removeGroup(groupName)">
                    <template #button="modal">
                        <a @click="modal.open()">
                            <mc-icon name="trash"></mc-icon>
                        </a>
                    </template>
                    <template #message="message">
                        <?php i::_e('Remover grupo de agentes relacionados?') ?>
                    </template>
                </mc-confirm-button>
            </div>
        </div>
        <!-- lista de agentes -->
        <div class="entity-related-agents__group--agents">
            <div v-for="relation in relations" class="agent">
                <mc-relation-card :relation="relation">
                    <template #default="{open, close, toggle}">
                        <a class="agent__img" @click="$event.preventDefault(); toggle()">
                           <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                        </a>
                    </template>
                </mc-relation-card>
                <!-- remover agente -->
                <div v-if="editable" class="agent__delete">
                    <mc-confirm-button @confirm="removeAgent(groupName, relation.agent)">
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
        <div v-if="editable" class="entity-related-agents__group--actions">
            <select-entity type="agent" @select="addAgent(groupName, $event)" permissions="" select="id,name,files.avatar,terms,type" :query="queries[groupName]" openside="down-right">
                <template #button="{ toggle }">
                    <button class="button button--rounded button--sm button--icon button--primary" @click="toggle()">
                        <?php i::_e('Adicionar agente') ?>
                        <mc-icon name="add"></mc-icon>
                    </button>
                </template>
            </select-entity>
        </div>
    </div>
    <div v-if="editable" class="entity-related-agents__addNew">
        <mc-popover openside="down-right">
            <template #button="popover">

                <slot name="button">
                    <div class="add-agent">
                        <?php i::_e("Adicionar novo grupo de Agentes") ?>
                    </div>
                    <button @click="popover.toggle()" class="button button--primary-outline button--icon">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Adicionar grupo") ?>
                    </button>
                </slot>
            </template>
            <template #default="{close}">
                <div class="entity-related-agents__addNew--newGroup">
                    <form @submit="addGroup(newGroupName); $event.preventDefault(); close();">
                        <div class="grid-12">
                            <div class="related-input col-12">
                                <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome do grupo') ?>" maxlength="64" />
                            </div>
                            <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                            <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </div>
            </template>
        </mc-popover>
    </div>
</div>