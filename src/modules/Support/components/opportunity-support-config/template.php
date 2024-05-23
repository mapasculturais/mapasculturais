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
                    <template #button="modal">
                        <button type="button" @click="modal.open()" class="button button--primarylight button--icon"> <?= i::__("Gerenciar permissões") ?> </button>
                    </template>

                    <div class="opportunity-support-config__modal-content">

                        <div class="opportunity-support-config__content-header">
                            <div class="opportunity-support-config__filter">
                                <div class="opportunity-support-config__filters">
                                    <div v-if="categories" class="field">
                                        <label><?= i::__('Categoria do edital') ?></label>
                                        <mc-select v-model:default-value="categoryFilter" hide-filter :options="categories" ></mc-select>    
                                    </div>
    
                                    <div v-if="proponentTypes" class="field">
                                        <label><?= i::__('Tipo de Proponente') ?></label>
                                        <mc-select v-model:default-value="proponentFilter" hide-filter :options="proponentTypes"></mc-select>    
                                    </div>
    
                                    <div v-if="ranges" class="field">
                                        <label><?= i::__('Tipo de faixa/linha') ?></label>
                                        <mc-select v-model:default-value="rangeFilter" hide-filter :options="ranges"></mc-select>    
                                    </div>
                                </div>    

                                <div v-if="hasSelectedField" class="field">
                                    <label><?= i::__('Aplicar nos selecionados') ?></label>
                                    <mc-select :options="permissions" :default-value="allPermissions" hide-filter @change-option="setAllPerssions($event)"></mc-select>
                                </div>
                            </div>
    
                            <label class="opportunity-support-config__select-all semibold">
                                <input class="opportunity-support-config__checkbox" type="checkbox" @change="toggleSelectAll($event)" v-model="selectAll"><?php i::_e("Selecionar todos"); ?>
                            </label>
                        </div>

                        <div v-if="filteredFields" class="opportunity-support-config__field" v-for="(field, index) in filteredFields" :key="index">
                            <label class="opportunity-support-config__field-content">
                                <input class="opportunity-support-config__field-checkbox" v-model="selectedFields[field.ref]" :checked="selectAll" type="checkbox"/>
                                
                                <span class="opportunity-support-config__field-title">
                                    <span class="opportunity-support-config__field-icon">
                                        <mc-icon :name="field.type"></mc-icon>
                                    </span>

                                    <h4 class="bold"> #{{field.id}} - {{ field.title }} </h4>
                                </span>
                            </label>

                            <mc-select :options="permissions" :default-value="relation.metadata?.registrationPermissions[field.ref]" hide-filter @change-option="setPerssion($event,field)"></mc-select>
                        </div>

                        <div v-if="filteredFields.length == 0" class="opportunity-support-config__field">
                            Nenhum campo foi encontrado
                        </div>
                    
                    </div>

                    <template #actions="modal">
                        <button class="button button--primary" @click="send(modal,relation)"><?= i::__('Concluir') ?></button>
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