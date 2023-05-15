<?php

use MapasCulturais\i;

$this->import('
mc-icon
select-entity
');
?>
<div v-if="parent">
    <div>
        <strong><?= i::_e("Supra projeto: "); ?></strong>
        <span>{{parent.name}}</span>
    </div>
    <a :href="parent.singleUrl" :title="parent.shortDescription">
        <div>
            <img v-if="parent.files?.avatar" :src="parent.files?.avatar?.url">
            <div v-if="!parent.files?.avatar">
                <mc-icon name="agent-1"></mc-icon>
            </div>
        </div>
    </a>
</div>