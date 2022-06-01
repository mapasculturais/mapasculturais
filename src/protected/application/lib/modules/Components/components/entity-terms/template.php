<div v-if="entity.terms?.[taxonomy]" class="entity-terms">

    <h4 v-if="title == ''"> {{taxonomy}} </h4>
    <h4 v-else> {{title}} </h4>
    
    <ul class="entity-terms__terms">
        <li class="entity-terms__terms--term" v-for="term in entity.terms[taxonomy]"> 
            <a> {{term}} </a> 
            
            <a v-if="editable"><iconify icon="codicon:chrome-close"/></a>
             
        </li>
        <li class="entity-terms__terms--addNew" v-if="editable">
            Adicionar nova
            <span><iconify icon="fluent:add-12-regular"/></span>
        </li>
    </ul>

</div>
