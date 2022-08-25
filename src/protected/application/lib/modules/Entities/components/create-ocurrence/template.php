<?php 
use MapasCulturais\i;
$this->import('modal entity-field entity-terms'); 
?>

<modal title="Inserir ocorrência no evento" classes="create-modal" button-label="Adicionar nova ocorrência" >
    <template #default>
        
    </template>
    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>
    <template #actions="modal">
        <button class="button button--primary" @click="createPublic(modal)"><?php i::_e('Criar e Publicar')?></button>
        <button class="button button--solid-dark" @click="createDraft(modal)"><?php i::_e('Criar em Rascunho')?></button>
        <button class="button button--text button--text-del " @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
    </template>
</modal>
