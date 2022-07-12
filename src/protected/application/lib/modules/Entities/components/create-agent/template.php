 <?php 
use MapasCulturais\i;
$this->import('modal mapas-field entity-terms'); 
?>

<modal title="Criar Agente" class="create-modal" button-label="Criar Agente" >
    <template #default>
        <label><?php i::_e('Crie um agente com informações básicas')?><br><?php i::_e('e de forma rápida')?></label>
        <div class="create-modal__fields">
            <mapas-field :entity="entity"  :editable="true" label="Selecione o tipo do agente" prop="type"></mapas-field>
            <mapas-field :entity="entity" label="Nome ou título"  prop="name"></mapas-field>
            <entity-terms :entity="entity" :editable="true" taxonomy='area' title="Área de Atuação"></entity-terms>
            <mapas-field :entity="entity" prop="shortDescription" label="Adicione uma Descrição curta para o Agente"></mapas-field>
            <mapas-field :entity="entity" v-for="field in fields" :prop="field"></mapas-field>
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
