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
    select-entity
');
?>

<div>
    <div>
        <h2>Configuração de suporte</h2>
        <p>Adicione Agentes que darão suporte à essa Oportunidade.</p>
    </div>

    <select-entity type="agent" @select="addAgent($event)" permissions="" select="id,name,files.avatar,terms,type"  openside="down-right">
        <!-- :query="queries[groupName]" -->
        <template #button="{ toggle }">
            <button class="button button--icon button--primary" @click="toggle()">
                <mc-icon name="add"></mc-icon>
                <?php i::_e('Adicionar agente') ?>
            </button>
        </template>
    </select-entity>

    <div class="opportunity-support-config__agents">

        <div v-for="relation in relations" class="opportunity-support-config__agent">
            <div class="opportunity-support-config__agent-info">
                <mc-avatar :entity="relation.agent" size="small"></mc-avatar>
                <mc-title tag="h2" :shortLength="55" :longLength="71" class="bold">{{relation.agent.name}}</mc-title>
            </div>
            <div class="opportunity-support-config__agent-actions">
                <button class="button button--primary button--icon"> <?= i::__("Gerenciar permissões") ?> </button>
                
                <mc-confirm-button @confirm="removeAgent(relation.agent)">
                    <template #button="modal">
                        <button class="button button--delete" @click="modal.open()">
                            <mc-icon name="trash"></mc-icon><?= i::__("Excluir") ?> 
                        </button>
                    </template>
                    <template #message>
                        <?= i::__("Deseja remover o agente relacionado?");?>
                    </template>
                </mc-confirm-button>
            </div>
        </div>
    </div>
</div>