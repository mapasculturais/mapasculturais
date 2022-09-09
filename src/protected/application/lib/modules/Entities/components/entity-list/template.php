<?php

use MapasCulturais\i;

$this->import('entities mc-link');
?>
<div class="entity-list">
    <div class="entity-list__owner grid-12">
        <h4>{{title}}</h4>
        <div class="col-12__owner--title">

        </div>
        <entities select="id,name,files.avatar" order="name ASC" :type="type" :query="query" #default="{entities}">
            <li v-for="entity in entities">
                <mc-link class="col-12 entity-list__owner--img" :entity="entity">
                    {{entity.name}}
                    <img v-if="entity.files.avatar?.transformations?.avatarSmall?.url" class="entity-list__label--title-img" :src="entity.files.avatar?.transformations?.avatarSmall?.url">
                    <div v-else class="placeholder">
                        <div>
                            <mc-icon name="agent-1"></mc-icon>
                        </div>
                    </div>
                </mc-link>
            </li>
        </entities>
    </div>
</div>