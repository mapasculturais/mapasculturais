<?php
use MapasCulturais\i;
$this->import('popover')
?>
<div v-if="editable || entity.terms?.[taxonomy]" class="entity-terms">

    <h4 class="entity-terms__title" v-if="title == ''"> {{taxonomy}} </h4>
    <h4 class="entity-terms__title" v-else> {{title}} </h4>

    <popover v-if="editable" openside="down-right" @close="this.filter = ''" @open="loadTerms()">
        <template #button="popover">
            <button @click="popover.toggle()" class="button button--rounded button--sm button--icon button--primary" v-if="editable">
                <?php i::_e("Adicionar nova") ?>
                <iconify icon="ps:plus"></iconify>
            </button>
        </template>

        <!-- Modo Tags -->
        <template v-if="allowInsert" #default="popover">
            <div class="entity-terms__popover">
                <form class="entity-terms__popover--form" @submit="addTerm(filter, popover)">
                    <input type="text" v-model="filter" class="entity-terms__popover--input" placeholder="<?= i::__('Busque') ?>">
                    <button class="entity-terms__popover--button"><iconify icon="ic:baseline-search"></iconify></button>
                </form>
                <ul v-if="terms.length > 0">
                    <li @click="addTerm(term, popover)" v-for="term in filteredTerms">
                        <span v-html="highlightedTerm(term)"></span>
                    </li>
                </ul>
            </div>
        </template>

        <!-- Modo Área de Atuação -->
        <template v-if="!allowInsert" #default="popover">
            <div class="entity-terms__popover">
                <input type="text" v-model="filter" class="entity-terms__popover--input" placeholder="<?= i::__('Filtre') ?>">
                <ul v-if="terms.length > 0">
                    <li v-for="term in filteredTerms">
                        <label class="entity-terms__popover--field">
                            <input type="checkbox" 
                                :checked="entityTerms.indexOf(term) >= 0"
                                @change="toggleTerm(term)"
                                class="input" > 
                            <span class="text" v-html="highlightedTerm(term)"></span>
                        </label>
                    </li>
                </ul>
            </div>
        </template>
    </popover>

    <ul class="entity-terms__terms">
        <li class="button--solid entity-terms__terms--term" v-for="term in entityTerms"> 
            <a> {{term}} </a> 
            <a v-if="editable" @click="remove(term)"><iconify icon="gg:close"/></a>             
        </li>
    </ul>
</div>