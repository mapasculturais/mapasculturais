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
    create-project 
    select-entity
    mc-icon
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

            <a v-if="selected" class="link-opportunity__exchange helper__color" @click="setSelected()">
                <mc-icon class="primary__color" name="exchange"></mc-icon>
                <h4 class="primary__color"><?php i::_e('Alterar entidade') ?></h4>
            </a>
        </div>
        <template v-if="!selected">
            <div class="link-opportunity__selected">
                <label class="link-opportunity__link semibold"><?php i::_e('Vincule a oportunidade a uma entidade:') ?></label>
                <!-- <mc-icon name="closed" class="link-opportunity__closed"></mc-icon> -->
                <div class="link-opportunity__opt">
                    <div class="link-opportunity__option">

                        <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down" class="link-opportunity__teste">
                            <template #button="{ toggle }">
                                <a class="" :class="entityColorClass" @click="setEntity()">
                                    <label for="inputProject" class="inner link-opportunity__selection" :class="{'inner--error': hasObjectTypeErrors()}" @click="resetEntity()">
                                        <a class="itemLabel">
                                            <input v-model="entityTypeSelected" type="radio" id="inputProject" name="inputName" value="project" @click="toggle()" />
                                            <span><?php i::_e('Projeto') ?> </span>
                                        </a>

                                        <a :class="{'disabled': entityTypeSelected!='project'}" class="selecta"><?php i::_e('Selecionar') ?> </a>
                                    </label>

                                    </button>
                            </template>
                            <template #default>
                                <create-project></create-project>
                            </template>
                        </select-entity>
                    </div>
                    <div class="link-opportunity__option">

                        <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" :class="entityColorClass" @click="toggle()">
                                    <label for="inputEvent" class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="itemLabel">
                                            <input v-model="entityTypeSelected" id="inputEvent" type="radio" name="inputName" value="event" @click="toggle()" />
                                            <span><?php i::_e('Evento') ?> </span>
                                        </span>

                                        <a :class="{'disabled': entityTypeSelected!='event'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                                    </label>

                                </a>
                            </template>
                        </select-entity>
                    </div>
                    <div class="link-opportunity__option">

                        <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" :class="entityColorClass" @click="toggle()">
                                    <label for="inputSpace" class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="itemLabel">
                                            <input v-model="entityTypeSelected" type="radio" id="inputSpace" name="inputName" value="space" @click="toggle()" />
                                            <span><?php i::_e('Espaço') ?> </span>
                                        </span>

                                        <a :class="{'disabled': entityTypeSelected!='space'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                                    </label>

                                </a>
                            </template>
                        </select-entity>
                    </div>
                    <div class="link-opportunity__option">

                        <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down">
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" :class="entityColorClass" @click="toggle()">
                                    <label for="inputAgent" class="inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="itemLabel">
                                            <input v-model="entityTypeSelected" type="radio" id="inputAgent" name="inputName" value="agent" @click="toggle()" />
                                            <span><?php i::_e('Agente') ?> </span>
                                        </span>

                                        <a :class="{'disabled': entityTypeSelected!='agent'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                                    </label>
                                </a>
                            </template>
                        </select-entity>
                    </div>
                </div>

            </div>
        </template>

    </div>
</div>