 <?php

    use MapasCulturais\i;

    $this->import('
    entity-field 
    entity-terms
    mc-link
    modal 
    select-entity
');
    ?>

 <modal :title="modalTitle" classes="create-modal" button-label="Criar Oportunidade" @open="createEntity()" @close="destroyEntity()">
     <template v-if="entity && !entity.id" #default>
         <label><?php i::_e('Crie uma oportunidade com informações básicas') ?><br><?php i::_e('e de forma rápida') ?></label>
         <div class="create-modal__fields">
             <entity-field :entity="entity" hide-required :editable="true" label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
             <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Título") ?> prop="name"></entity-field>
             <small class="field__error" v-if="areaErrors">{{areaErrors.join(', ')}}</small>
             <div class="create-modal__fields--choice">
                 <label class="create-modal__fields--choice-label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></label>

                 <div class="create-modal__fields--choice-list">
                     <select-entity type="project" @select="setEntity($event)" openside="down-right" class="create-modal__fields--choice-list-box">

                         <template #button="{ toggle }">
                             <input type="radio" id="btnAgent" name="inputName" value="valorPadrao"> </input>

                             <label class="create-modal__fields--choice-list-box-label" @click="toggle()">
                                 <?php i::_e('Projeto') ?>
                             </label>
                             <label class="create-modal__fields--choice-list-box-selection"><?php i::_e('Selecionar') ?> </label>

                         </template>
                     </select-entity>
                 </div>

                 <div class="create-modal__fields--choice-list">
                     <select-entity type="event" @select="setEntity($event)" openside="down-right" class="create-modal__fields--choice-list-box">
                         <template #button="{ toggle }">

                             <input type="radio" id="btnAgent" name="inputName" value="valorPadrao"> </input>
                             <label class="create-modal__fields--choice-list-box-label" @click="toggle()">
                                 <?php i::_e('Evento') ?>
                             </label>

                             <label class="create-modal__fields--choice-list-box-selection"><?php i::_e('Selecionar') ?> </label>

                         </template>
                     </select-entity>
                 </div>
                 <div class="create-modal__fields--choice-list">

                     <select-entity type="space" @select="setEntity($event)" openside="down-right" class="create-modal__fields--choice-list-box">
                         <template #button="{ toggle }">
                             <input type="radio" id="btnAgent" name="inputName" value="valorPadrao"> </input>
                             <label class="create-modal__fields--choice-list-box-label" @click="toggle()">
                                 <?php i::_e('Espaço') ?>
                             </label>
                             <label class="create-modal__fields--choice-list-box-selection"><?php i::_e('Selecionar') ?> </label>

                         </template>
                     </select-entity>
                 </div>
                 <div class="create-modal__fields--choice-list">

                     <select-entity type="agent" @select="setEntity($event)" openside="down-right" class="create-modal__fields--choice-list-box">
                         <template #button="{ toggle }">
                             <input type="radio" id="btnAgent" name="inputName" value="valorPadrao">
                             <label for="btnAgent" class="create-modal__fields--choice-list-box-label" @click="toggle()">
                                 <?php i::_e('Agente') ?>
                             </label>
                             <label class="create-modal__fields--choice-list-box-selection"><?php i::_e('Selecionar') ?> </label>

                         </template>
                     </select-entity>
                 </div>
             </div>

             <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
         </div>
     </template>
     <!-- <template v-if="entity?.id" #default> -->

     <template v-if="!entity?.id" #default>
         <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.  '); ?></label>
     </template>
     <!-- <template v-if="entity?.id && entity.status==0" #default> -->

     <template v-if="!entity?.id " #default>
         <!-- #rascunho -->
         <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.'); ?></label><br><br>
         <label><?php i::_e('Para completar e publicar sua oportunidade, acesse a área <b>Rascunhos</b> em <b>Minhas Oportunidades</b> no <b>Painel de Controle</b>.  '); ?></label>
     </template>

     <template #button="modal">
         <slot :modal="modal"></slot>
     </template>
     <template v-if="!entity?.id" #actions="modal">
        <!-- #Criado em Rascunho -->
         <button  class="button button--text button--text-del" @click="modal.close()"><?php i::_e('Ir para o painel') ?></button>
         <button class="button button--primary button--icon " @click="modal.close()"><?php i::_e('Entendi') ?></button>
     </template>

     <template v-if="entity?.id && entity.status==1" #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
         <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
     </template>
     <template v-if="entity?.id && entity.status==0" #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
         <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
     </template>
 </modal>