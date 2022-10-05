 <?php 
use MapasCulturais\i;
 
$this->import('
    entity-field 
    entity-terms
    mc-link
    modal 
'); 
?>

<modal :title="modalTitle" classes="create-modal" button-label="Criar Agente" @open="createEntity()" @close="destroyEntity()">
    <template v-if="entity && !entity.id" #default>
        <label><?php i::_e('Crie um agente com informações básicas')?><br><?php i::_e('e de forma rápida')?></label>
        <div class="create-modal__fields">
            <entity-field :entity="entity" hide-required  :editable="true" label="<?php i::esc_attr_e("Selecione o tipo do agente")?>" prop="type"></entity-field>
            <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Nome ou título")?>  prop="name"></entity-field>
            <entity-terms :entity="entity" :editable="true" :classes="areaClasses" taxonomy='area' title="<?php i::esc_attr_e("Área de Atuação") ?>"></entity-terms>
            <small class="field__error" v-if="areaErrors">{{areaErrors.join(', ')}}</small>
            <entity-field :entity="entity" hide-required prop="shortDescription" label="<?php i::esc_attr_e("Adicione uma Descrição curta para o Agente")?>"></entity-field>
            <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
        </div>
    </template>

    <template v-if="entity?.id" #default>
        <label><?php i::_e('Você pode completar as informações do seu agente agora ou pode deixar para depois.  ');?></label>
    </template>
    <template v-if="entity?.id && entity.status==0" #default>
        <label><?php i::_e('Para completar e publicar seu novo agente, acesse a área Rascunhos em Meus Agentes no Painel de Controle.   ');?></label>
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
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações')?></mc-link>
    </template>
    <template v-if="entity?.id && entity.status==0" #actions="modal">
        <mc-link :entity="panel" route='panel/agents' class="button button--primary-outline button--icon"><?php i::_e('Ir para o Painel');?></mc-link>
        <button class="button button--primary button--icon" @click="modal.close()"><?php i::_e('Entendi')?></button>
    </template>
</modal>
