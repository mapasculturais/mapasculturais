<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-occurrence-list
    entity-field 
    entity-terms
    mc-link
    mc-modal 
');
?>
<mc-modal :title="modalTitle" classes="create-modal create-event-modal" button-label="<?php i::_e('Criar Evento')?>"  @open="createEntity()" @close="destroyEntity()">
     <template v-if="entity && !entity.id" #default>
         <label id="title"><?php i::_e('Crie um evento com informações básicas') ?><br><?php i::_e('e de forma rápida') ?></label>
         <div class="create-modal__fields">
             <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Nome ou título") ?> prop="name"></entity-field>
             <entity-terms :entity="entity" hide-required :editable="true" :classes="linguagemClasses" taxonomy='linguagem' title="<?php i::esc_attr_e("Linguagem cultural") ?>"></entity-terms>
             <entity-field :entity="entity" hide-required prop="shortDescription" label="<?php i::esc_attr_e("Adicione uma Descrição curta para o Evento") ?>"></entity-field>
             <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
         </div>
     </template>

     <template v-if="entity?.id" #default>
        <div>
            <label><?php i::_e('Você pode completar as informações do seu evento agora ou pode deixar para depois. '); ?> </label><br><br>
            <label><?php i::_e('Para completar e publicar seu novo evento, acesse a área <b>Rascunhos</b> em <b>Meus Eventos</b> no <b>Painel de Controle</b>.  ');?></label>
        </div>
        <hr><br>
         <entity-occurrence-list :entity="entity" editable create-event></entity-occurrence-list>
     </template>

     <template #button="modal">
         <slot :modal="modal"></slot>
     </template>

     <template v-if="!entity?.id" #actions="modal">
         <button href="#title" class="button button--primary" @click="createPublic(modal)"><?php i::_e('Criar e Publicar') ?></button>
         <button href="#title" class="button button--solid-dark" @click="createDraft(modal)"><?php i::_e('Criar em Rascunho') ?></button>
         <button class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
     </template>
     <template v-if="entity?.id && entity.status==1" #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Evento');?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações')?></mc-link>
    </template>
     <template v-if="entity?.id && entity.status==0" #actions="modal">
        <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Evento');?></mc-link>
        <button class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
        <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações')?></mc-link>
    </template>
 </mc-modal>