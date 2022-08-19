<?php
use MapasCulturais\i;
$this->import('entities mapas-card entity-card');
?>

<div class="grid-12 search-list">
    <entities :type="type" :select="select" :query="query" :limit="limit" watch-query>
        <template #header="{entities}">
            <div class="col-3 search-list__filter">
                <slot name="filter"></slot>
            </div>
        </template>

        <template #default="{entities}">
            <div class="col-9" v-for="entity in entities" :key="entity.__objectId">
                <entity-card :entity="entity"></entity-card> 
            </div>
        </template>
    </entities>
</div>