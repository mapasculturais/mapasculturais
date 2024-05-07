<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
	mc-avatar
	mc-icon 
    mc-confirm-button
	mc-title
    mc-modal
    mc-select
    select-entity
');
?>

<div class="opportunity-support-config">
    <div class="opportunity-support-config__header">
        <h2><?php i::_e('Configuração de suporte')?></h2>
        <p><?php  i::_e('Adicione Agentes que darão suporte à essa Oportunidade.')?></p>
    </div>

    <div class="opportunity-support-config__add-agents">
        <select-entity type="agent" @select="addAgent($event)" permissions="" :query="query" select="id,name,files.avatar,terms,type" openside="down-right">
            <template #button="{ toggle }">
                <button class="button button--icon button--primary" @click="toggle()">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e('Adicionar agente') ?>
                </button>
            </template>
        </select-entity>
    </div>
    
    <div class="opportunity-support-config__agents">

        <div v-for="relation in relations" class="opportunity-support-config__agent">
            <div class="opportunity-support-config__agent-info">
                <mc-avatar :entity="relation.agent" size="small"></mc-avatar>
                <mc-title tag="h2" :shortLength="55" :longLength="71" class="bold">{{relation.agent.name}}</mc-title>
            </div>
            <div class="opportunity-support-config__agent-actions">
                <mc-modal title="<?= i::__('Campos que o Agente pode editar') ?>">
                    <template #actions="modal">
                        <button class="button button--primary" @click="send(modal,relation)"><?= i::__('Concluir') ?></button>
                    </template>
                    <div class="opportunity-support-config__modal-select-header">
                        <label class="title__header semibold">
                            <input class="opportunity-support-config__checkbox" type="checkbox" @change="toggleSelectAll($event)"  v-model="selectAll"><?php i::_e("Selecionar todos"); ?>
                        </label>
                        <mc-select :options="permissions" :default-value="allPermissions" small hide-filter @change-option="setAllPerssions($event)">
                        </mc-select>
                    </div>
                    <div class="opportunity-support-config__modal-select" v-for="(field, index) in fields" :key="index">
                        <label class="title__select">
                            <input class="opportunity-support-config__checkbox" v-model="selectedFields[field.ref]" :checked="selectAll" type="checkbox"/>
                            #{{ field.id }} {{ field.title }}
                        </label>
                        <mc-select :options="permissions" :default-value="relation.metadata?.registrationPermissions[field.ref]" small hide-filter @change-option="setPerssion($event,field)">
                        </mc-select>
                    </div>
                    <template #button="modal">
                        <button type="button" @click="modal.open()" class="button button--primarylight button--icon"> <?= i::__("Gerenciar permissões") ?> </button>
                    </template>
                </mc-modal>
                <mc-confirm-button @confirm="removeAgent(relation.agent)">
                    <template #button="modal">
                        <button class="button button--delete button--icon" @click="modal.open()">
                            <mc-icon name="trash"></mc-icon><?= i::__("Excluir") ?>
                        </button>
                    </template>
                    <template #message>
                        <?= i::__("Deseja remover o agente relacionado?"); ?>
                    </template>
                </mc-confirm-button>
            </div>
        </div>
    </div>
</div>