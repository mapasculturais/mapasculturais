<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entities
    popover
');
?>
    <popover :openside="openside" :button-label="buttonLabel" :title="itensText" :button-classes="[buttonClasses, type + '__color']" classes="select-entity__popover"> 
        <template #button="{ toggle }">
            <slot name="button" :toggle="toggle"></slot>
        </template>

        <template #default="{ close }">

            <div class="select-entity">

                <entities :type="type" :select="select" :query="query" :limit="limit" :scope="scope" :permissions="permissions" @fetch="fetch($event)" watch-query>
                    <template #header="{entities}">
                        <form class="select-entity__form" @submit="entities.refresh(); $event.preventDefault();">
                            <input v-model="entities.query['@keyword']" type="text" class="select-entity__form--input" name="search" :placeholder="placeholder" @input="entities.refresh(500)"/>
                            <button type="button" class="select-entity__form--button">
                                <mc-icon name="search"></mc-icon>
                            </button>
                        </form>
                    </template>

                    <template #default="{entities}">
                        <p v-if="entities.length > 0" class="select-entity__description"> {{itensText}} </p>
                        <ul class="select-entity__results">
                            <li v-for="entity in entities" class="select-entity__results--item" :class="type" @click="selectEntity(entity, close)">
                                <span class="icon">
                                    <img v-if="entity.files" :src="entity.files?.avatar?.transformations?.avatarSmall?.url" />
                                    <mc-icon v-else :entity="entity"></mc-icon>
                                </span>
                                <span class="label"> {{entity.name}} </span>
                            </li>
                        </ul>

                    </template>

                </entities>

                <div v-if="createNew" class="select-entity__add">
                    <p> <?php i::__('ou') ?> </p>
                    <a href="" class="select-entity__add--button">
                        {{buttonText}}
                    </a>
                </div>

            </div>

        </template>

    </popover>

