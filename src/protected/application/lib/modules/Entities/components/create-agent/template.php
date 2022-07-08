 <?php 
use MapasCulturais\i;


$this->import('modal mapas-field entity-terms'); 
?>

        
<modal title="Criar Agente" classes="create-entity" button-label="Criar Agente" >
    <template #default>
        <label>Crie um agente com informações básicas<br>e de forma rápida</label>
        <div class="create-modal__fields">
            <mapas-field :entity="entity"  :editable="true" label="Selecione o tipo do agente" prop="type"></mapas-field>
            <mapas-field :entity="entity" label="Nome ou título"  prop="name"></mapas-field>
            <entity-terms :entity="entity" :editable="true" taxonomy='area'></entity-terms>
            <mapas-field :entity="entity" prop="shortDescription" label="Adicione uma Descrição curta para o Agente"></mapas-field>
            <mapas-field :entity="entity" v-for="field in fields" :prop="field"></mapas-field>
        </div>
    </template>
    <template #button="modal">
        <slot :modal="modal"></slot>
    </template>
    <template #actions="modal">
        <div class="create-modal__buttons" ">

            <button class="button button--primary" @click="createPublic(modal)">Criar e Publicar</button>
            <button class="button button--solid" @click="createDraft(modal)">Criar em Rascunho</button>
            <button class="button button--text" @click="cancel(modal)">Cancelar</button>
        </div>
    </template>
</modal>
