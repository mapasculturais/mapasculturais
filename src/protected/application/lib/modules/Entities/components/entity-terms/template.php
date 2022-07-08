<?php
$this->import('popover')
?>
<div v-if="editable || entity.terms?.[taxonomy]" class="entity-terms">

    <h4 class="entity-terms__title" v-if="title == ''"> {{taxonomy}} </h4>
    <h4 class="entity-terms__title" v-else> {{title}} </h4>
    
    <ul class="entity-terms__terms">
        <popover openside="down-right" @close="this.filter = ''" @open="loadTerms()">
            <template #button="popover">
                <li @click="popover.toggle()" class="button--primary  entity-terms__terms--addNew" v-if="editable">
                    Adicionar nova
                    <span><iconify icon="fluent:add-20-filled"/></span>
                </li>
            </template>

            <!-- Modo Tags -->
            <template v-if="allowInsert" #default="popover">
                <form @submit="addTerm(filter, popover)">
                    <input type="text" v-model="filter" placeholder="">
                    <button>+</button>
                </form>
                <ul v-if="terms.length > 0" style="max-height:200px; overflow-y:scroll">
                    <li @click="addTerm(term, popover)" v-for="term in filteredTerms">
                        <span v-html="underlinedTerm(term)"></span>
                    </li>
                </ul>
            </template>

            <!-- Modo Área de Atuação -->
            <template v-if="!allowInsert" #default="popover">
                <input type="text" v-model="filter" placeholder="Filtre">
                <ul v-if="terms.length > 0" style="max-height:200px; overflow-y:scroll">
                    <li v-for="term in filteredTerms">
                        <label>
                            <input type="checkbox" 
                                :checked="entityTerms.indexOf(term) >= 0"
                                @change="toggleTerm(term)" > 
                            <span v-html="underlinedTerm(term)"></span>
                        </label>
                    </li>
                </ul>
            </template>
        </popover>

        <li class="button--solid entity-terms__terms--term" v-for="term in entityTerms"> 
            <a> {{term}} </a> 
            <a v-if="editable" @click="remove(term)"><iconify icon="gg:close"/></a>             
        </li>
    </ul>
</div>