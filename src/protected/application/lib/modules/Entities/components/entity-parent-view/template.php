<?php

use MapasCulturais\i;

$this->import('
mc-icon
select-entity
');
?>
<div v-if="parent">
    
    <a :href="parent.singleUrl" :title="parent.shortDescription">
        <div>
            <img v-if="parent.files?.avatar" :src="parent.files?.avatar?.url">
            <div v-if="!parent.files?.avatar">
                <mc-icon :name="this.entity.__objectType"></mc-icon>
            </div>
        </div>
    </a>
    <div>
        <strong>{{label}}</strong>
        <span>{{parent.name}}</span>
    </div>
</div>