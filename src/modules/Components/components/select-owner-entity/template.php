<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    select-entity
');
?>
<div v-if="!entity.ownerEntity" class="select-list">
    <div class="select-list__label">{{ title }}<br></div>
    <div class="select-list__item">
        <select-entity type="project" openside="down-right" v-if="types.includes('project')" @select="setEntity($event)">
            <template #button="{ toggle }">
                <label class="inner" :class="{ 'inner--error': hasObjectTypeErrors() }">
                    <span class="itemLabel">
                        <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="project" />
                        <span><?php i::_e('Projeto') ?> </span>
                    </span>

                    <a :class="{ 'disabled': entityTypeSelected !== 'project' }" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                </label>
            </template>
        </select-entity>
    </div>
    <div class="select-list__item">
        <select-entity type="event" openside="down-right" v-if="types.includes('event')" @select="setEntity($event)">
            <template #button="{ toggle }">
                <label class="inner" :class="{ 'inner--error': hasObjectTypeErrors() }">
                    <span class="itemLabel">
                        <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="event" />
                        <span><?php i::_e('Evento') ?> </span>
                    </span>

                    <a :class="{ 'disabled': entityTypeSelected !== 'event' }" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                </label>
            </template>
        </select-entity>
    </div>
    <div class="select-list__item">
        <select-entity type="space" openside="down-right" v-if="types.includes('space')" @select="setEntity($event)">
            <template #button="{ toggle }">
                <label class="inner" :class="{ 'inner--error': hasObjectTypeErrors() }">
                    <span class="itemLabel">
                        <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="space" />
                        <span><?php i::_e('Espaço') ?> </span>
                    </span>

                    <a :class="{ 'disabled': entityTypeSelected !== 'space' }" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                </label>
            </template>
        </select-entity>
    </div>
    <div class="select-list__item">
        <select-entity type="agent" openside="down-right" v-if="types.includes('agent')" @select="setEntity($event)">
            <template #button="{ toggle }">
                <label class="inner" :class="{ 'inner--error': hasObjectTypeErrors() }">
                    <span class="itemLabel">
                        <input v-model="entityTypeSelected" @click="toggle()" type="radio" name="inputName" value="agent" />
                        <span><?php i::_e('Agente') ?> </span>
                    </span>

                    <a :class="{ 'disabled': entityTypeSelected !== 'agent' }" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                </label>
            </template>
        </select-entity>
    </div>
</div>

<small v-if="hasObjectTypeErrors()" class="field__error">{{getObjectTypeErrors().join('; ')}}</small>

<div v-if="entity.ownerEntity" class="create-modal__fields--selected">
    <div class="create-modal__fields--selected-label"><?php i::_e('Vincule a oportunidade a uma entidade: ') ?><br></div>
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

            <a class="entity-selected__info--btn helper__color" @click="resetEntity()" v-if="types.length > 0">
                <mc-icon class="helper__color" name="exchange"></mc-icon>
                <h4 class="helper__color"><?php i::_e('Alterar entidade') ?></h4>
            </a>
        </div>
    </div>
</div>