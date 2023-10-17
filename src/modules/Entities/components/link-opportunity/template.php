<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
    mc-avatar
    mc-popover  
    select-entity
');
?>

<div class="link-opportunity">
    <label class="link-opportunity__title bold"><?php i::_e('Entidade Vinculada') ?><br></label>

    <div class="link-opportunity__ownerEntity">

        <div class="link-opportunity__header" :class="entityColorBorder">
            <mc-avatar :entity="entity.ownerEntity" size="xsmall"></mc-avatar>
            {{entity.ownerEntity.name}}
        </div>

        <div class="link-opportunity__actions">
            <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                <template #button="{ toggle }">
                    <a class="link-opportunity__change" :class="entityColorClass" @click="toggle()">
                        <mc-icon :class="entityColorClass" name="exchange"></mc-icon>
                        <h4 :class="entityColorClass"><?php i::_e('Alterar') ?> {{entityType}}</h4>
                    </a>
                </template>
            </select-entity>

            <mc-popover classes="relation-popover">
                <template #button="{open, close, toggle}">
                    <a class="link-opportunity__exchange helper__color" @click="open()">
                        <mc-icon class="primary__color" name="exchange"></mc-icon>
                        <h4 class="primary__color"><?php i::_e('Alterar entidade') ?></h4>
                    </a>
                </template>

                <template #default="{open, close, toggle}">
                    <div class="select-list">
                        <div class="select-list__item">
                            <label for="inputProject" class="inner" :class="{'inner--error': hasObjectTypeErrors()}"  @click="resetEntity()">
                                <a class="itemLabel">
                                    <input v-model="entityTypeSelected" type="radio" id="inputProject" name="inputName" value="project" />
                                    <span><?php i::_e('Projeto') ?> </span>
                                </a>

                                <a :class="{'disabled': entityTypeSelected!='project'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </div>

                        <div class="select-list__item">
                            <label for="inputEvent" class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="resetEntity()" id="inputEvent" type="radio" name="inputName" value="event" />
                                    <span><?php i::_e('Evento') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='event'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </div>

                        <div class="select-list__item">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="resetEntity()" type="radio" name="inputName" value="space" />
                                    <span><?php i::_e('Espaço') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='space'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </div>

                        <div class="select-list__item">
                            <label class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                <span class="itemLabel">
                                    <input v-model="entityTypeSelected" @click="resetEntity()" type="radio" name="inputName" value="agent" />
                                    <span><?php i::_e('Agente') ?> </span>
                                </span>

                                <a :class="{'disabled': entityTypeSelected!='agent'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                            </label>
                        </div>

                    </div>
                </template>
            </mc-popover>
        </div>
    </div>
</div>