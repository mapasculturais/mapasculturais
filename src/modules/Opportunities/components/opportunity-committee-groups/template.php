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
    mc-toggle
    opportunity-evaluation-committee
    opportunity-registration-filter-configuration
');
?>

<div class="opportunity-committee-groups">
    <div class="opportunity-committee-groups__description">
       <p><?php i::_e('Defina os agentes que farão parte das comissões de avaliação desta fase.') ?></p>
    </div>

    <mc-tabs ref="tabs">
        <template #after-tablist>
            <div class="opportunity-committee-groups__actions">
                <button v-if="hasTwoOrMoreGroups && entity.useCommitteeGroups" class="button button--icon button--primary button--sm" @click="addGroup(minervaGroup, true);">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Voto de minerva') ?>
                </button>
                
                <mc-popover openside="down-right">
                    <template #button="popover">
                        <button @click="popover.toggle()" class="button button--primary-outline button--sm button--icon">
                            <mc-icon name="add"></mc-icon>
                            <?php i::_e("Adicionar comissão") ?>
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
            </div>
        </template>
        
        <mc-tab v-for="([groupName, relations], index) in Object.entries(entity.relatedAgents)" :key="index" :label="groupName" :slug="String(index)">
            <div class="opportunity-committee-groups__group">
                <div class="opportunity-committee-groups__edit-group field">
                    <label for="newGroupName"><?= i::__('Título da comissão') ?></label>

                    <div class="opportunity-committee-groups__edit-group--field">
                        <input :disabled="groupName == '@tiebreaker'" id="newGroupName" v-model="groupName" class="input" type="text" @input="renameTab($event, index)" placeholder="<?= i::esc_attr__('Digite o novo nome do grupo') ?>" />
                        <mc-confirm-button @confirm="removeGroup(groupName)">
                            <template #button="modal">
                                <a class="button button--delete button--icon button--sm" @click="modal.open()">
                                    <mc-icon name="trash"></mc-icon>
                                    <?= i::__('Excluir comissão') ?> 
                                </a>
                            </template>
                            <template #message="message">
                                <?php i::_e('Remover comissão de avaliadores?') ?>
                            </template>
                        </mc-confirm-button>
                    </div> 
                </div>

                <div class="opportunity-committee-groups__multiple-evaluators">
                    <div class="field">
                        <mc-toggle
                            :modelValue="localValuersPerRegistration[groupName] != null" 
                            @update:modelValue="changeMultipleEvaluators($event, groupName)"
                            label="<?= i::__('Limitar número de avaliadores por inscrição') ?>"
                        />
                        <input v-if="localValuersPerRegistration[groupName] != null" v-model="localValuersPerRegistration[groupName]" type="number" @change="autoSave()"/>
                    </div>
    
                    <div class="field">
                        <mc-toggle
                            :modelValue="entity?.fetchFields[groupName] && Object.keys(entity.fetchFields[groupName]).length > 0" 
                            @update:modelValue="enableRegisterFilterConf($event, groupName)"
                            label="<?= i::__('Configuração filtro de inscrição para avaliadores/comissão') ?>"
                        />
                        <opportunity-registration-filter-configuration 
                            v-if="entity.fetchFields[groupName]" 
                            :entity="entity"
                            v-model:default-value="entity.fetchFields[groupName]"
                            :excludeFields="globalExcludeFields"
                            @updateExcludeFields="updateExcludedFields('global', $event)"
                            useDistributionField
                            is-global
                        >
                        </opportunity-registration-filter-configuration>
                    </div>
                </div>
                
                <div class="opportunity-committee-groups__evaluators">
                    <opportunity-evaluation-committee :entity="entity" :group="groupName" :excludeFields="individualExcludeFields" @updateExcludeFields="updateExcludedFields('individual', $event)"></opportunity-evaluation-committee>
                </div>
            </div>
        </mc-tab>
    </mc-tabs>
</div>
