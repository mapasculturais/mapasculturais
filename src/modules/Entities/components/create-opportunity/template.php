<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field 
    entity-terms
    mc-link
    mc-modal 
    select-entity
');
?>
<mc-modal :title="modalTitle" classes="create-modal create-opportunity-modal" button-label="<?php i::_e('Criar Oportunidade')?>" @open="createEntity()" @close="destroyEntity()">
    <template v-if="entity && !entity.id" #default="modal">
        <label><?php i::_e('Crie uma oportunidade com informações básicas') ?><br><?php i::_e('e de forma rápida') ?></label>
        <form @submit.prevent="handleSubmit" class="create-modal__fields">
            <entity-field :entity="entity" hide-required :editable="true" label="<?php i::esc_attr_e("Selecione o tipo da oportunidade") ?>" prop="type"></entity-field>
            <entity-field :entity="entity" hide-required label=<?php i::esc_attr_e("Título") ?> prop="name"></entity-field>

            <div class="create-modal__fields">
                <entity-terms :entity="entity" hide-required :editable="true" title="<?php i::_e('Área de Interesse') ?>" taxonomy="area"></entity-terms>
            </div>

            <div v-if="!entity.ownerEntity" class="select-list">
                <label class="select-list__label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></label>
                <div class="select-list__item">
                    <select-entity type="project" @select="setEntity($event)" openside="down-right">
                        <template #button="{ toggle }">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="project" />
                                    <span><?php i::_e('Projeto') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='project'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </template>
                    </select-entity>
                </div>
                <div class="select-list__item">
                    <select-entity type="event" @select="setEntity($event)" openside="down-right">
                        <template #button="{ toggle }">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="event" />
                                    <span><?php i::_e('Evento') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='event'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </template>
                    </select-entity>
                </div>
                <div class="select-list__item">
                    <select-entity type="space" @select="setEntity($event)" openside="down-right">
                        <template #button="{ toggle }">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="space" />
                                    <span><?php i::_e('Espaço') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='space'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </template>
                    </select-entity>
                </div>
                <div class="select-list__item">
                    <select-entity type="agent" @select="setEntity($event)" openside="down-right">
                        <template #button="{ toggle }">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="agent" />
                                    <span><?php i::_e('Agente') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='agent'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </template>
                    </select-entity>
                </div>
            </div>

            <small v-if="hasObjectTypeErrors()" class="field__error">{{getObjectTypeErrors().join('; ')}}</small>

            <div v-if="entity.ownerEntity" class="create-modal__fields--selected">
                <label class="create-modal__fields--selected-label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></label>
                <div class="entity-selected">
                    <div class="entity-selected__entity" :class="entityColorBorder">
                        <mc-avatar :entity="entity.ownerEntity" size="xsmall"></mc-avatar>
                        <span class="name" :class="entityColorClass"><?php i::_e('{{entity.ownerEntity.name}}') ?></span>
                    </div>
                    <div class="entity-selected__info">
                        <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                            <template #button="{ toggle }">
                                <a class="entity-selected__info--btn" :class="entityColorClass" @click="toggle()">
                                    <mc-icon :class="entityColorClass" name="exchange"></mc-icon>
                                    <h4 :class="entityColorClass"><?php i::_e('Alterar') ?> {{entityType}}</h4>
                                </a>
                            </template>
                        </select-entity>

                        <a class="entity-selected__info--btn helper__color" @click="resetEntity()">
                            <mc-icon class="helper__color" name="exchange"></mc-icon>
                            <h4 class="helper__color"><?php i::_e('Alterar entidade') ?></h4>
                        </a>
                    </div>
                </div>
            </div>

            <entity-field :entity="entity" hide-required v-for="field in fields" :prop="field"></entity-field>
        </form>
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
        <button class="button button--text button--text-del" @click="modal.close(); destroyEntity()"><?php i::_e('Cancelar') ?></button>
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
</mc-modal>