<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import(" 
    mc-modal
    entity-field
    select-entity
");
?>
<div>
    <mc-modal classes="create-modal create-opportunity-modal" title="<?= i::__('Título do edital') ?>" @open="createEntity()">
        <template #default>
            <div class="create-modal__fields">
                <div class="field">
                    <label><?= i::__('Defina um título para o Edital que deseja criar') ?><span class="required">*</span></label>
                    <input type="text" v-model="formData.name">
                </div><br>

                <div v-if="!entity.ownerEntity" class="select-list">
                    <label class="select-list__label"><?php i::_e('Vincule o edital a uma entidade: ') ?><br></label>
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
                    <label class="create-modal__fields--selected-label"><?php i::_e('Vincule o edital a uma entidade: ') ?><span class="required">*</span><br></label>
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
            </div>
        </template>

        <template v-if="!sendSuccess"  #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('cancelar') ?></button>
            <button class="button button--primary" @click="save(modal)"><?= i::__('Começar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="button button--primary button--icon"><?= i::__('Usar modelo') ?></button>
        </template>
    </mc-modal>
</div>