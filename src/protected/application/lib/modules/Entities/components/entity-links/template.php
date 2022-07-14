<?php 
use MapasCulturais\i;
?>
<div class="entity-links">
    <h4 class="entity-links__title"> {{title}} </h4>
  
    <button class="button button-primary" @click="success()">Sucesso</button>
    <button class="button button-primary" @click="error()">Erro</button>
    <button class="button button-primary" @click="warning()">Aviso</button>
    
    <ul class="entity-links__links">

        <li class="entity-links__links--item" v-for="link in entity.metalists.links">
            <a class="link" :href="link.value" target="_blank" >
                <iconify icon="eva:link-outline" /> 
                {{link.title}}
            </a>            
            <div v-if="editable" class="edit">
                <a> <iconify icon="zondicons:edit-pencil" /> </a>
                <a> <iconify icon="ooui:trash" /> </a>
            </div>
        </li>

        <li v-if="editable" class="button--primary entity-links__links--addNew">
            <?php i::_e("Adicionar novo link")?>
            <span> <iconify icon="fluent:add-20-filled" /> </span>
        </li>

    </ul>
</div>