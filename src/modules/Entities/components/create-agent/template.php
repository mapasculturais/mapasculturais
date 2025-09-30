<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
 
$this->import('
    entity-field 
    entity-terms
    mc-link
    mc-modal 
'); 
?>
<mc-modal :title="modalTitle" classes="create-modal create-agent-modal" button-label="<?php i::_e('Criar Agente')?>"  @open="createEntity()" @close="destroyEntity()">
    <template v-if="entity && !entity.id" #default>
        <label><?php i::_e('Crie um agente com informações básicas')?><br><?php i::_e('e de forma rápida')?></label>
        <div class="create-modal__fields">
            <?php $this->part('entities/create-agent-fields') ?>
        </div>
    </template>

    <template v-if="entity?.id" #default>
        <label><?php i::_e('Você pode completar as informações do seu agente agora ou pode deixar para depois.  ');?></label>
    </template>
    <template v-if="entity?.id && entity.status==0" #default>
        <label><?php i::_e('Você pode completar as informações do seu agente agora ou pode deixar para depois.');?></label><br><br>
        <label><?php i::_e('Para completar e publicar seu novo agente, acesse a área <b>Rascunhos</b> em <b>Meus Agentes</b> no <b>Painel de Controle</b>.  ');?></label>
    </template>

    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>
    <template v-if="!entity?.id" #actions="modal">
        <button class="button button--primary" @click="createPublic(modal)"><?php i::_e('Criar e Publicar')?></button>
        <button class="button button--solid-dark" @click="createDraft(modal)"><?php i::_e('Criar em Rascunho')?></button>
        <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar')?></button>
    </template>

    <template v-if="entity?.id && entity.status==1" #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Agente');?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon button--complete"><?php i::_e('Completar Informações')?></mc-link>
    </template>
    <template v-if="entity?.id && entity.status==0" #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Agente');?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon button--complete"><?php i::_e('Completar Informações')?></mc-link>
    </template>
</mc-modal>
