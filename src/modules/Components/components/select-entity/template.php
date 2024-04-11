<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-entities
    mc-popover
    create-project
    create-event 
    create-space
    create-agent
');
?>
    <mc-popover :openside="openside" :button-label="buttonLabel" :title="itensText" :button-classes="[buttonClasses, type + '__color']" :classes="[classes, 'select-entity__popover']" @close="clearField" @confirm="clearField"> 
        <template #button="{ toggle }">
            <slot name="button" :toggle="toggle"></slot>
        </template>

        <template #default="{ close }">
            <div class="select-entity">
                <mc-entities :type="type" :select="select" :query="query" :limit="limit" :scope="scope" :permissions="permissions" @fetch="fetch($event)" watch-query>
                    <template #header="{entities}">
                        <form class="select-entity__form" @submit="entities.refresh(); $event.preventDefault();">
                            <input ref="searchKeyword" v-model="entities.query['@keyword']" type="text" class="select-entity__form--input" name="searchKeyword" :placeholder="placeholder" @input="entities.refresh(500)"/>
                            <button type="button" class="select-entity__form--button">
                                <mc-icon name="search"></mc-icon>
                            </button>
                        </form>
                    </template>

                    <template #default="{entities}">
                        <slot name="selected">
                            <p v-if="entities.length > 0" class="select-entity__description"> {{itensText}} </p>
                        </slot>
                        <ul class="select-entity__results">
                            <li v-for="entity in entities" class="select-entity__results--item" :class="type" @click="selectEntity(entity, close)">
                                <span class="icon">
                                    <mc-avatar :entity="entity" size="xsmall"></mc-avatar>
                                </span>
                                <span class="label"> {{entity.name}} </span>
                            </li>
                        </ul>
                    </template>
                </mc-entities>
                <div v-if="createNew" class="select-entity__add">
                    <div class="select-entity__footer">
                        <label class="select-entity__other"> <?php i::_e('ou') ?> </label>
                        <create-project v-if="type=='project'">
                            <template #default="{modal}">
                                <button class="button button--primary-outline button--large" @click="modal.open()"><?php i::_e('Criar projeto') ?> </button>
                            </template>
                        </create-project>
                        <create-event v-if="type=='event'">
                            <template #default="{modal}">
                                <button class="button button--primary-outline button--large" @click="modal.open()"><?php i::_e('Criar evento') ?> </button>
                            </template>
                        </create-event>
                        <create-space v-if="type=='space'">
                            <template #default="{modal}">
                                <button class="button button--primary-outline button--large" @click="modal.open()"><?php i::_e('Criar espaço') ?> </button>
                            </template>
                        </create-space>
                        <create-agent v-if="type=='agent'">
                            <template #default="{modal}">
                                <button class="button button--primary-outline button--large" @click="modal.open()"><?php i::_e('Criar agente') ?> </button>
                            </template>
                        </create-agent>
                    </div>
                </div>
            </div>
        </template>
    </mc-popover>