<?php $this->import('entities popover') ?>

    <popover openside="right-down"> 
        <template #btn="{ toggle }">
            <slot name="btn" :toggle="toggle"> 
                <a class="openPopever" :class="type + '__color'" > Abrir select-Entity </a>
            </slot>
        </template>

        <template #content="{ close }">

            <div class="select-entity">

                <entities :type="type" :select="select" :query="query" :limit="limit" :scope="scope">

                    <template #header1="{entities}">
                        <div class="select-entity__form">
                            <input v-model="query['@keyword']" type="text" class="select-entity__form--input" name="search" :placeholder="placeholder" />
                            <button @click="entities.refresh()" type="button" class="select-entity__form--button">
                                <iconify icon="ant-design:search-outlined" />
                            </button>
                            <input type="checkbox" v-model="entities.loading" />
                        </div>
                    </template>

                    <template #default="{entities}">
                        <p class="select-entity__description"> {{itensText}} </p>
                        
                        <ul class="select-entity__results">
                            <li v-for="entity in entities" class="select-entity__results--item" :class="type" @click="selectEntity(entity, close)">
                                <span class="icon">
                                    <img v-if="entity.files" :src="entity.files?.avatar?.transformations?.avatarSmall?.url" />
                                    <iconify v-else :icon="icon" />
                                </span>
                                <span class="label"> {{entity.name}} </span>
                            </li>
                        </ul>

                    </template>

                </entities>

                <div v-if="createNew" class="select-entity__add">
                    <p> ou </p>
                    <a href="" class="select-entity__add--button">
                        {{buttonText}}
                    </a>
                </div>

            </div>

        </template>

    </popover>

