<div v-if="entity.terms?.[taxonomy]" class="entity-terms">

    <h4 class="entity-terms__title" v-if="title == ''"> {{taxonomy}} </h4>
    <h4 class="entity-terms__title" v-else> {{title}} </h4>
    
    <ul class="entity-terms__terms">

        <li class="button--solid entity-terms__terms--term" v-for="term in entity.terms[taxonomy]"> 
            <a> {{term}} </a> 
            <a v-if="editable"><iconify icon="gg:close"/></a>             
        </li>

        <li class="button--primary  entity-terms__terms--addNew" v-if="editable">
            Adicionar nova
            <span><iconify icon="fluent:add-20-filled"/></span>
        </li>

    </ul>

</div>
