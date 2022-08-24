<?php
use MapasCulturais\i;
?>

<div class="mc-tag-list">
    <ul class="mc-tag-list__tagList">
        <li :class="[entityType+'__background', 'mc-tag-list__tagList--tag']" v-for="tag in tags "> 
            {{tag}}
            <mc-icon v-if="editable"  name="delete"></mc-icon> 
            <!-- @click="remove(term)" -->
        </li>
    </ul>
</div>
