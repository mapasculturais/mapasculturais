<?php
use MapasCulturais\i;
?>

<div v-if="entity.files.downloads" class="files-list">
    <h2> {{title}} </h2>
    <div class="files">
        <a v-for="file in entity.files.downloads" :download="file.name" :href="file.url">
            <iconify icon="el:download-alt" /> 
            <span v-if="file.description">{{file.description}}</span>
            <span v-else> <? i::_e('Sem descrição') ?> </span>
        </a>
    </div>
</div>