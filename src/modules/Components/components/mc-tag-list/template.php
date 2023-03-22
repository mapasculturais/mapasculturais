<?php
use MapasCulturais\i;
?>

<div class="mc-tag-list">
    <ul class="mc-tag-list__tagList">
        <li :class="[classes, 'mc-tag-list__tagList--tag']" v-for="tag in tags"> 
            {{this.labels ? this.labels[tag] : tag}}
            <mc-icon v-if="editable" name="delete" @click="remove(tag)"></mc-icon>             
        </li>
    </ul>
</div>
