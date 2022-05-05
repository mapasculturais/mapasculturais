<div v-if="entity.terms?.[taxonomy]" class="entity-terms">

    <h4 v-if="title == ''"> tax: {{taxonomy}} </h4>
    <h4 v-else> title: {{title}} </h4>
    
    <ul class="terms">
        <li v-for="term in entity.terms[taxonomy]"> 
            <a> {{term}} </a> 
        </li>
    </ul>

    <!-- v-for="val in filterTerms" -->
    <!-- <a href="" style="text-decoration: none; color: black; padding: 10px 25px; margin: 5px; background-color: #EFEFEF; border-radius: 15px; font-size: 13px; line-height: 13px;" 
              class="term" 
              v-for="term in terms">
            {{term}}
        </a> -->
</div>
