<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-icon
    mc-popover
    mc-tab
    mc-tabs
    opportunity-evaluation-committee
');
?>

<div class="entity-related-agents" v-if="hasGroups()">
    
    <mc-tabs v-if="Object.keys(groups).length > 0" class="entity-related-agents__addNew">
        <template v-if="hasTwoOrMoreGroups" #after-tablist>
            <button class="button button--icon button--primary" @click="addGroup(minervaGroup, true);">
                <mc-icon name="add"></mc-icon>
                <?php i::_e('Adicionar voto de minerva') ?>
            </button>
        </template>

        <mc-tab v-for="(relations, groupName) in groups" :key="groupName" :label="groupName" :slug="groupName">
            <div class="entity-related-agent__group">
                
            </div>
            <div class="entity-related-agents__edit-group">
                <mc-popover openside="down-right">
                     <template #button="popover">
                         <slot name="button">
                             <a class="button button--icon button--primary" @click="popover.toggle()"> <mc-icon name="edit"></mc-icon> <?= i::__('Editar') ?> {{groupName}} </a>
                         </slot>
                     </template>
                     <template #default="{popover, close}">
                         <form @submit="renameGroup(groupName, relations.newGroupName, popover); $event.preventDefault(); close()" class="entity-related-agents__addNew--newGroup">
                             <div class="grid-12">
                                 <div class="related-popover col-12">
                                     <input v-model="relations.newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o novo nome do grupo') ?>" />
                                 </div>
     
                                 <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                                 <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                             </div>
                         </form>
                     </template>
                 </mc-popover>
     
                 <mc-confirm-button @confirm="removeGroup(groupName)">
                     <template #button="modal">
                         <a class="button button--icon button--delete" @click="modal.open()">
                             <mc-icon name="trash"></mc-icon>
                             <?= i::__('Excluir') ?> {{groupName}}
                         </a>
                     </template>
                     <template #message="message">
                         <?php i::_e('Remover comissão de avaliadores?') ?>
                     </template>
                 </mc-confirm-button>
            </div>
        
            <div class="field">
                <label> <?php i::_e("Quantidade de avaliadores por inscrição:") ?> </label>
                <input v-model="localSubmissionEvaluatorCount[groupName]" type="number" @change="autoSave()"/>
            </div>

            <opportunity-evaluation-committee :entity="entity" :group="groupName"></opportunity-evaluation-committee>
        </mc-tab>
    </mc-tabs>

    <div class="entity-related-agents__addNew">
        <div class="add-agent">
            <?php i::_e("Adicionar novo grupo de Avaliadores") ?>
        </div>

            <mc-popover openside="down-right">
                <template #button="popover">
                    <button @click="popover.toggle()" class="button button--primary-outline button--icon">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Adicionar grupo") ?>
                    </button>
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

<button v-if="!hasGroups()" class="button button--icon button--primary" @click="changeGroupFlag">
    <mc-icon name="add"></mc-icon>
    <?php i::_e('Adicionar comissão de avaliação') ?>
</button>