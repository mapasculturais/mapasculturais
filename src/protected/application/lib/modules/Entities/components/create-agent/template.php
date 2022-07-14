 <?php 
use MapasCulturais\i;
$this->import('modal entity-field entity-terms'); 
?>

<modal title="Criar Agente" class="create-modal" button-label="Criar Agente" >
    <template #default>
        <label><?php i::_e('Crie um agente com informações básicas')?><br><?php i::_e('e de forma rápida')?></label>
        <div class="create-modal__fields">
            <entity-field :entity="entity"  :editable="true" label="<?php i::esc_attr_e("Selecione o tipo do agente")?>" prop="type"></entity-field>
            <entity-field :entity="entity" label=<?php i::esc_attr_e("Nome ou título")?>  prop="name"></entity-field>
            <entity-terms :entity="entity" :editable="true" taxonomy='area' title="Área de Atuação"></entity-terms>
            <entity-field :entity="entity" prop="shortDescription" label="<?php i::esc_attr_e("Adicione uma Descrição curta para o Agente")?>"></entity-field>
            <entity-field :entity="entity" v-for="field in fields" :prop="field"></entity-field>
        </div>
    </template>
    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>
    <template #actions="modal">
        <div class="create-modal__buttons">
            <button class="button button--primary" @click="createPublic(modal)"><?php i::_e('Criar e Publicar')?></button>
            <button class="button button--solid-dark" @click="createDraft(modal)"><?php i::_e('Criar em Rascunho')?></button>
            <button class="button button--text button--text-del " @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
        </div>
    </template>
</modal>
