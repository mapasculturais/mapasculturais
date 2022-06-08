<?php 
use MapasCulturais\i;
?>
<div class="entity-links">
    <h4 class="entity-links__title"> {{title}} </h4>

    <ul class="entity-links__links">

        <li class="entity-links__links--item" v-for="link in entity.metalists.links">
            <a class="link" :href="link.value" target="_blank" >
                <iconify icon="eva:link-outline" /> 
                {{link.title}}
            </a>            
            <div class="edit">
                <a> <iconify icon="zondicons:edit-pencil" /> </a>
                <a> <iconify icon="ooui:trash" /> </a>
            </div>
        </li>

        <li class="button--primary entity-links__links--addNew">
            <?php i::_e("Adicionar novo link")?>
            <span> <iconify icon="fluent:add-12-regular" /> </span>
        </li>

    </ul>
</div>