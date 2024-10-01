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
    <!-- <h4 class="bold"><?php i::_e("Avaliadores relacionados") ?></h4> -->
    <mc-tabs class="entity-related-agents__addNew">
        <template v-if="hasTwoOrMoreGroups" #after-tablist>
            <button class="button button--icon button--primary" @click="addGroup(minervaGroup, true);">
                <mc-icon name="add"></mc-icon>
                <?php i::_e('Adicionar voto de minerva') ?>
            </button>
        </template>

        <mc-tab v-for="(relations, groupName) in groups" :key="groupName" :label="groupName" :slug="groupName">
            <label>
                <?php i::_e("Quantidade de avaliadores por inscrição:") ?>
                <input v-model="localSubmissionEvaluatorCount[groupName]" type="number" @change="autoSave()"/>
            </label>

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
                    <a @click="modal.open()">
                        <mc-icon name="trash"></mc-icon>
                    </a>
                </template>
                <template #message="message">
                    <?php i::_e('Remover comissão de avaliadores?') ?>
                </template>
            </mc-confirm-button>

            <opportunity-evaluation-committee :entity="entity" :group="groupName"></opportunity-evaluation-committee>
        </mc-tab>
    </mc-tabs>

    <div lass="entity-related-agents__addNew">
        <mc-popover openside="down-right">
            <template #button="popover">

                <slot name="button">
                    <div class="add-agent">
                        <?php i::_e("Adicionar novo grupo de Avaliadores") ?>
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

<button v-if="!hasGroups()" class="button button--icon button--primary" @click="changeGroupFlag">
    <mc-icon name="add"></mc-icon>
    <?php i::_e('Adicionar comissão de avaliação') ?>
</button>