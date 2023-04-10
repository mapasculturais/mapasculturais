<?php
use MapasCulturais\i;

$this->import('
    entity-field 
    entity-terms
    mc-link
    modal 
    select-entity
    entity-owner
');
?>
 <modal :title="modalTitle" classes="create-modal" button-label="Criar Oportunidade" @open="createEntity()" @close="destroyEntity()">
     <template v-if="entity && !entity.id" #default>
         <label><?php i::_e('Crie uma oportunidade com informações básicas') ?><br><?php i::_e('e de forma rápida') ?></label>
         <div class="create-modal__fields">
             <entity-field :entity="entity" hide-required :editable="true" label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
             <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Título") ?> prop="name"></entity-field>
             <small class="field__error" v-if="areaErrors">{{areaErrors.join(', ')}}</small>
             <div v-if="!entity.ownerEntity" class="create-modal__fields--choice">
                 <label class="label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></label>
                 <div class="list">
                     <select-entity type="project" @select="setEntity($event)" openside="down-right" class="list-box">
                         <template #button="{ toggle }">
                             <div class="input">
                                 <label class="list-box--label">
                                 <input v-model="entityTypeSelected" type="radio" id="btnAgent" name="inputName" value="project" />
                                     <?php i::_e('Projeto') ?>
                                 </label>
                             </div>

                             <a :class="{'disabled': entityTypeSelected!='project'}" class="list-box-button" @click="toggle()"><?php i::_e('Selecionar') ?> </a>
                         </template>
                     </select-entity>
                 </div>


                 <div class="list">
                     <select-entity type="event" @select="setEntity($event)" openside="down-right" class="list-box">
                         <template #button="{ toggle }">
                             <div class="input">
                                 <label class="list-box--label">
                                 <input v-model="entityTypeSelected" type="radio" id="btnAgent" name="inputName" value="event" />
                                     <?php i::_e('Evento') ?>
                                 </label>
                             </div>

                             <a :class="{'disabled': entityTypeSelected!='event'}" class="list-box-button" @click="toggle()"><?php i::_e('Selecionar') ?> </a>
                         </template>
                     </select-entity>
                 </div>

                 <div class="list">
                     <select-entity type="space" @select="setEntity($event)" openside="down-right" class="list-box">
                         <template #button="{ toggle }">
                             <div class="input">
                                 <label class="list-box--label">
                                 <input v-model="entityTypeSelected" type="radio" id="btnAgent" name="inputName" value="space" />
                                     <?php i::_e('Espaço') ?>
                                 </label>
                             </div>

                             <a :class="{'disabled': entityTypeSelected!='space'}" class="list-box-button" @click="toggle()"><?php i::_e('Selecionar') ?> </a>
                         </template>
                     </select-entity>
                 </div>


                 <div class="list">
                     <select-entity type="agent" @select="setEntity($event)" openside="down-right" class="list-box">
                         <template #button="{ toggle }">
                             <div class="input">
                                 <label class="list-box--label">
                                 <input v-model="entityTypeSelected" type="radio" id="btnAgent" name="inputName" value="agent" />
                                     <?php i::_e('Agente') ?>
                                 </label>
                             </div>

                             <a :class="{'disabled': entityTypeSelected!='agent'}" class="list-box-button" @click="toggle()"><?php i::_e('Selecionar') ?> </a>
                         </template>
                     </select-entity>
                 </div>
             </div>
             <div v-if="entity.ownerEntity" class="create-modal__fields--selected">
                 <label class="create-modal__fields--selected-label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></label>

                 <div class="entity-selected">
                     <div class="entity-selected__entity" :class="[entityTypeSelected + '__border']">
                         <img v-if="entity.ownerEntity.files?.avatar" :src="entity.ownerEntity.files?.avatar?.transformations.avatarSmall.url" class="img" />
                         <div v-if="!entity.ownerEntity.files?.avatar" class="img-fake">
                             <mc-icon :entity="entity.ownerEntity"></mc-icon>

                         </div>

                         <span class="name" :class="[entityTypeSelected + '__color']"><?php i::_e('{{entity.ownerEntity.name}}') ?></span>
                     </div>
                     <div class="entity-selected__info">
                         <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                             <template #button="{ toggle }">
                                 <a class="entity-selected__info--btn" :class="entityTypeSelected + '__color'" @click="toggle()">
                                     <mc-icon :class="[entityTypeSelected + '__color']" name="exchange"></mc-icon>
                                     <h4 :class="[entityTypeSelected + '__color']"><?php i::_e('Alterar') ?></h4>
                                 </a>
                             </template>
                         </select-entity>
                     </div>
                 </div>


             </div>

             <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
         </div>
     </template>

     <template v-if="entity?.id" #default>
         <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.  '); ?></label>
     </template>

     <template v-if="entity?.id && entity.status==0" #default>

         <!-- #rascunho -->
         <label><?php i::_e('Você pode completar as informações da sua oportunidade agora ou pode deixar para depois.'); ?></label><br><br>
         <label><?php i::_e('Para completar e publicar sua oportunidade, acesse a área <b>Rascunhos</b> em <b>Minhas Oportunidades</b> no <b>Painel de Controle</b>.  '); ?></label>
     </template>

     <template #button="modal">
         <slot :modal="modal"></slot>
     </template>
     <template v-if="!entity?.id" #actions="modal">
         <!-- #Criado em Rascunho -->
         <button class="button button--primary button--icon " @click="createDraft(modal)"><?php i::_e('Criar') ?></button>
         <mc-link route="panel/index" class="button button--text button--text-del" @click="modal.close()"><?php i::_e('Cancelar') ?></mc-link>
     </template>

     <template v-if="entity?.id" #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
         <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
     </template>
     <template v-if="entity?.id " #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Oportunidade'); ?></mc-link>
         <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois') ?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações') ?></mc-link>
     </template>
 </modal>