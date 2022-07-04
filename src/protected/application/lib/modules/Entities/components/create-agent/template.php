 <?php 
use MapasCulturais\i;

$this->import('modal field'); 
?>

<div class="create-agent">
   <modal @open="createInstance()">
    <div class="create-agent__fields">
        <field :entity="instance" prop="name"></field>
        <field :entity="instance"  prop="name"></field>
        <field :entity="entity"  prop="name"></field>
    </div>
        <template #button="modal">
            <button class="button button--primary" @click="modal.open()">Criar Agente</button>
        </template>
        <template #actions="modal">
            <div class="create-agent__buttons">

                <button class="button button--primary" @click="createPublic(modal)">Criar e Publicar</button>
                <button class="button button--primary" @click="createDraft(modal)">Criar em Rascunho</button>
                <button class="button button--primary" @click="cancel(modal)">Cancelar</button>
            </div>
        </template>
   </modal>
</div>
