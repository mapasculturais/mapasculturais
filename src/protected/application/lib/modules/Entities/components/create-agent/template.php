 <?php 
use MapasCulturais\i;

$this->import('modal field'); 
?>

<div class="create-agent">
   <modal @open="createInstance()">
   
   <field :entity="instance" prop="name"></field>
   <field :entity="entity"  prop="name"></field>
        
       <template #button="modal">
           <button class="button btn" @click="modal.open()">Criar Agente</button>
       </template>
        <template #actions="modal">
            <button @click="createPublic(modal)">Criar e Publicar</button>
            <button @click="createDraft(modal)">Criar em Rascunho</button>
            <button @click="cancel(modal)">Cancelar</button>
        </template>
   </modal>
</div>
