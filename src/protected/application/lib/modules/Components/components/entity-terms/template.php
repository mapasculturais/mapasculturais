<div v-if="entity.terms?.[taxonomy]" class="entity-terms">

    <h4 v-if="title == ''"> {{taxonomy}} </h4>
    <h4 v-else> {{title}} </h4>
    
    <ul class="terms">
        <li v-for="term in entity.terms[taxonomy]"> 
            <a> {{term}} </a> 
            
            <a v-if="editable"><iconify icon="codicon:chrome-close"/></a>
             
        </li>
        <li class="addNew" v-if="editable">
            Adicionar nova
            <span><iconify icon="ant-design:plus-outlined"/></span>
        </li>
    </ul>

</div>
