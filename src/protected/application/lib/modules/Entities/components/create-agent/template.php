 <?php 
use MapasCulturais\i;

$this->import('modal mapas-field entity-terms'); 
?>

<div class="create-entity">
   <modal title="Criar Agente">
       <label>Crie um agente com informações básicas<br>
           e de forma rápida</label>
        <div class="create-modal__fields">
        
        <mapas-field :entity="entity"  :editable="true" label="Selecione o tipo do agente" prop="type"></mapas-field>
        <mapas-field :entity="entity" label="Nome ou título"  prop="name"></mapas-field>
        <entity-terms :entity="entity" :editable="true" taxonomy="Área de Atuação"></entity-terms>
        <mapas-field :entity="entity" prop="shortDescription" label="Adicione uma Descrição curta para o Agente"></mapas-field>
        <mapas-field :entity="entity" v-for="field in fields" :prop="field"></mapas-field>
        </div>
        <template #button="modal">
            <slot :modal="modal">
                <button class="button button--primary" @click="modal.open()">Criar Agente</button>
            </slot>
        </template>
        <template #actions="modal">
            <div class="create-modal__buttons">

                <button class="button button--primary" @click="createPublic(modal)">Criar e Publicar</button>
                <button class="button button--solid" @click="createDraft(modal)">Criar em Rascunho</button>
                <button class="button button--text" @click="cancel(modal)">Cancelar</button>
            </div>
        </template>
   </modal>
</div>
