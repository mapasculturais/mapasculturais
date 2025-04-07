<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-terms
    mc-link
    mc-avatar
    mc-popover 
    select-entity
    mc-icon
');
?>

<div class="link-opportunity">
    <entity-terms :entity="entity" :editable="true" title="<?php i::_e('Área de Interesse') ?>" taxonomy="area"></entity-terms>
    <label class="link-opportunity__title bold"><?php i::_e('Entidade Vinculada') ?><br></label>
    <div class="link-opportunity__ownerEntity">
        <div class="link-opportunity__header" :class="[entity.ownerEntity.__objectType+'__border', entity.ownerEntity.__objectType+'__color']">
            <mc-avatar :entity="entity.ownerEntity" size="xsmall"></mc-avatar>
            {{entity.ownerEntity.name}}
        </div>

        <div class="link-opportunity__actions">
            <select-entity :type="entityTypeSelected" @select="setEntity($event)" openside="right-down" create-new>
                <template #selected>
                    <label class="link-opportunity__message semibold"><?php i::_e('Selecione um dos ') ?>{{verifySelected(entityTypeSelected)}}</label>

                </template>
                <template #button="{ toggle }">
                    <a class="link-opportunity__change" @click="toggle()">
                        <mc-icon :class="entity.ownerEntity.__objectType+'__color'" name="exchange"></mc-icon>
                        <h4 :class="entity.ownerEntity.__objectType+'__color'"><?php i::_e('Alterar') ?> {{entityType}}</h4>
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
                <label class="link-opportunity__link semibold"><?php i::_e('Vincule a oportunidade a uma entidade:') ?><a @click="toggleSelected()"><mc-icon name="closed" class=""></mc-icon></a></label>
                <div class="link-opportunity__opt">
                    <div class="link-opportunity__option">
                        <select-entity  type="project" @select="setEntity($event); toggleSelected();" openside="right-down" create-new>
                            <template #selected>
                                <label class="link-opportunity__message semibold"><?php i::_e('Selecione um dos projetos') ?></label>

                            </template>
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" @click="toggle()">
                                    <label for="inputProject" class="link-opportunity__inner link-opportunity__selection" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <a class="link-opportunity__itemlabel">
                                            <input v-model="entityTypeSelected" type="radio" id="inputProject" name="inputName" value="project" @click="toggle()"/>
                                            <span><?php i::_e('Projeto') ?> </span>
                                        </a>

                                        <a :class="{'disabled': entityTypeSelected!='project'}" class="selectButton"><?php i::_e('Selecionar') ?> </a>
                                    </label>

                                </a>
                            </template>
                        </select-entity>
                    </div>

                    <div class="link-opportunity__option">
                        <select-entity type="event" @select="setEntity($event); toggleSelected();" openside="right-down" create-new>
                            <template #selected>
                                <label class="link-opportunity__message semibold"><?php i::_e('Selecione um dos eventos') ?></label>
                            </template>
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" @click="toggle()">
                                    <label for="inputEvent" class="link-opportunity__inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="link-opportunity__itemlabel">
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
                        <select-entity type="space" @select="setEntity($event); toggleSelected();" openside="right-down" create-new>
                            <template #selected>
                                <label class="link-opportunity__message semibold"><?php i::_e('Selecione um dos espaços') ?></label>

                            </template>
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" @click="toggle()">
                                    <label for="inputSpace" class="link-opportunity__inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="link-opportunity__itemlabel">
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
                        <select-entity type="agent" @select="setEntity($event); toggleSelected();" openside="right-down" create-new>
                            <template #selected>
                                <label class="link-opportunity__message semibold"><?php i::_e('Selecione um dos agentes') ?></label>

                            </template>
                            <template #button="{ toggle }">
                                <a class="link-opportunity__selection" @click="toggle()">
                                    <label for="inputAgent" class="link-opportunity__inner" :class="{'inner--error': hasObjectTypeErrors()}">
                                        <span class="link-opportunity__itemlabel">
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