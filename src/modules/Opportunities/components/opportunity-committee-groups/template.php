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
    opportunity-registration-filter-configuration
');
?>

<div class="opportunity-committee-groups">
    
    
    <mc-tabs>
        <template #after-tablist>
            <button v-if="hasTwoOrMoreGroups" class="button button--icon button--primary button--sm" @click="addGroup(minervaGroup, true);">
                <mc-icon name="add"></mc-icon>
                <?php i::_e('Voto de minerva') ?>
            </button>
            
            <mc-popover openside="down-right">
                <template #button="popover">
                    <button @click="popover.toggle()" class="button button--primary-outline button--sm button--icon">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Comissão") ?>
                    </button>
                </template>

                <template #default="{close}">
                    <form @submit="addGroup(newGroupName); $event.preventDefault(); close();">
                        <div class="grid-12">
                            <div class="related-input col-12">
                                <input v-model="newGroupName" class="input" type="text" name="newGroup" placeholder="<?php i::esc_attr_e('Digite o nome da comissão') ?>" maxlength="64" />
                            </div>
                            <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                            <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                        </div>
                    </form>
                </template>
            </mc-popover>
        </template>

        <mc-tab v-for="(relations, groupName) in groups" :key="groupName" :label="groupName" :slug="groupName">
            <div class="opportunity-committee-groups__group">

                <div class="opportunity-committee-groups__edit-group">
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

                <div class="opportunity-committee-groups__multiple-evaluators">
                    <div class="field">
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" v-model="entity.enableRegistrationFilterConfig"/>
                                <?= i::__('Configuração filtro de inscrição para avaliadores/comissão') ?>
                            </label>
                        </div>
                    </div>

                    <opportunity-registration-filter-configuration 
                        v-if="entity?.enableRegistrationFilterConfig" 
                        :entity="entity" 
                        :group-name="groupName"
                        :excludeFields="globalExcludeFields"
                        @updateExcludeFields="updateExcludedFields('global', $event)"
                    >
                    </opportunity-registration-filter-configuration>

                    <div class="field">
                        <div class="field__group">
                            <label class="field__checkbox">
                                <input type="checkbox" :checked="localSubmissionEvaluatorCount[groupName] > 0" @click="changeMultipleEvaluators($event, groupName)" />
                                <?= i::__('Permitir múltiplos avaliadores por inscrição') ?>
                            </label>
                        </div>
                    </div>
    
                    <div v-if="localSubmissionEvaluatorCount[groupName]" class="field">
                        <label> <?php i::_e("Quantidade de avaliadores por inscrição:") ?> </label>
                        <input v-model="localSubmissionEvaluatorCount[groupName]" type="number" @change="autoSave()"/>
                    </div>
                </div>
                
                <div class="opportunity-committee-groups__evaluators">
                    <opportunity-evaluation-committee :entity="entity" :group="groupName" :excludeFields="individualExcludeFields" @updateExcludeFields="updateExcludedFields('individual', $event)"></opportunity-evaluation-committee>
                </div>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
