<?php

use MapasCulturais\i;

$this->import('entities mapas-card entity-card');
?>

<div class="grid-12 search-list">
    <entities :type="type" :select="select" :query="query" :limit="limit" watch-query>
        <template #header="{entities}">
            <div class="col-3 search-list__filter">
                <div class="search-list__filter--filter">
                    <slot name="filter"></slot>
                </div>
            </div>
        </template>

        <template #default="{entities}">
            <div class="col-9 ">
                <div  class="grid-12">
                    <entity-card :entity="entity" v-for="entity in entities" :key="entity.__objectId" class="col-12">
                        <template #type> <span>{{typeText}} <span :class="['upper', entity.__objectType+'__color']">{{entity.type.name}}</span></span></template>
                    </entity-card>
                </div  >
            </div>
        </template>
    </entities>
</div>