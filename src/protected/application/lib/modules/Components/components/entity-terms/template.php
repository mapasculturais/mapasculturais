<div v-if="entity.terms?.[taxonomy]" class="entity-terms">

    <h4 v-if="title == ''"> tax: {{taxonomy}} </h4>
    <h4 v-else> title: {{title}} </h4>
    
    <ul class="terms">
        <li v-for="term in entity.terms[taxonomy]"> 
            <a> {{term}} </a> 
        </li>
    </ul>

</div>
