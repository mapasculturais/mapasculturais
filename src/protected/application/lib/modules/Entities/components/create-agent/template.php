 <?php 
use MapasCulturais\i;

$this->import('modal field'); 
?>

<div class="create-agent">
   <modal title="Criar Agente" @open="createInstance()">
       <label>Crie um agente com informações básicas<br>
           e de forma rápida</label>
        <div class="create-agent__fields">
            
            <field label="Selecione o tipo de agente" :entity="instance"  prop="name"></field>
            <field label="Nome ou título" :entity="instance" prop="name"></field>
            
            <field label="Selecione a área de atuação" :entity="entity"  prop="name"></field>
        </div>
        <template #button="modal">
            <button class="button button--primary" @click="modal.open()">Criar Agente</button>
        </template>
        <template #actions="modal">
            <div class="create-agent__buttons">

                <button class="button button--primary" @click="createPublic(modal)">Criar e Publicar</button>
                <button class="button button--solid" @click="createDraft(modal)">Criar em Rascunho</button>
                <button class="button button--text" @click="cancel(modal)">Cancelar</button>
            </div>
        </template>
   </modal>
</div>
