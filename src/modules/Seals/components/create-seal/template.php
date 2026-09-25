<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-occurrence-list
    entity-field 
    mc-link
    mc-modal
    mc-confirm-button
    seal-form-valid-period
');
?>
<mc-modal :title="modalTitle" classes="create-modal" button-label="<?php i::_e('Criar Selo')?>" @open="createEntity()" @close="destroyEntity()">
     <template v-if="entity && !entity.id" #default>
         <p class="create-modal__intro"><?php i::_e('Crie um selo com informações básicas') ?><br><?php i::_e('e de forma rápida') ?></p>
         <div class="create-modal__fields">
             <entity-field :entity="entity" hide-required label="<?php i::esc_attr_e("Nome ou título") ?>" prop="name"></entity-field>
             <entity-field :entity="entity" hide-required prop="shortDescription" :max-length="400" label="<?php i::esc_attr_e("Adicione uma Descrição curta para o Selo") ?>"></entity-field>
             <seal-form-valid-period :entity="entity" />
             
             <template v-for="field in fields" :key="field">
                  <mc-confirm-button v-if="field === 'sensitive'" @confirm="confirmSensitive" @cancel="cancelSensitive">
                      <template #button="{open}">
                          <div class="field">
                              <label class="field__checkbox" :for="dialogUid + '-checkbox'">
                                  <input :id="dialogUid + '-checkbox'" type="checkbox" :checked="entity.sensitive" @click="onSensitiveChange($event, open)" />
                                  <span>{{ sensitiveLabel }}</span>
                              </label>
                          </div>
                      </template>
                      <template #message="{confirm, cancel}">
                          <div role="alertdialog" aria-modal="true" aria-live="assertive" :aria-describedby="dialogUid + '-message'" :aria-labelledby="dialogUid + '-title'">
                              <h4 :id="dialogUid + '-title'" class="bold">{{ text('confirmSensitiveTitle') }}</h4>
                              <p :id="dialogUid + '-message'">{{ sensitiveConfirmMessage() }}</p>
                          </div>
                      </template>
                  </mc-confirm-button>
                 
                 <entity-field v-else :entity="entity" hide-required :prop="field"></entity-field>
             </template>
         </div>
     </template>

     <template v-if="entity?.id" #default>
        <div>
            <p class="create-modal__intro"><?php i::_e('Você pode completar as informações do seu selo agora ou pode deixar para depois. '); ?> </p><br><br>
            <p class="create-modal__intro"><?php i::_e('Para completar e publicar seu novo selo, acesse a área <b>Rascunhos</b> em <b>Meus Selos</b> no <b>Painel de Controle</b>.  ');?></p>

        </div>
     </template>

     <template #button="modal">
         <slot :modal="modal"></slot>
     </template>

      <template v-if="!entity?.id" #actions="modal">
          <button type="button" class="button button--primary" @click="createPublic(modal)"><?php i::_e('Criar e Publicar') ?></button>
          <button type="button" class="button button--solid-dark" @click="createDraft(modal)"><?php i::_e('Criar em Rascunho') ?></button>
          <button type="button" class="button button--text button--text-del " @click="modal.close()"><?php i::_e('Cancelar') ?></button>
      </template>
      <template v-if="entity?.id && entity.status==1" #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Selo');?></mc-link>
         <button type="button" class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações')?></mc-link>
     </template>
      <template v-if="entity?.id && entity.status==0" #actions="modal">
         <mc-link :entity="entity" class="button button--primary-outline button--icon"><?php i::_e('Ver Selo');?></mc-link>
         <button type="button" class="button button--secondarylight button--icon " @click="modal.close()"><?php i::_e('Completar Depois')?></button>
         <mc-link :entity="entity" route='edit' class="button button--primary button--icon"><?php i::_e('Completar Informações')?></mc-link>
     </template>
 </mc-modal>
