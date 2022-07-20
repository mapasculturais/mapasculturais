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
            <div class="entity-terms__tags">
                <form class="entity-terms__tags--form" @submit="addTerm(filter, popover)">
                    <input type="text" v-model="filter" class="entity-terms__tags--form-input" placeholder="<?= i::__('Adicione uma nova tag') ?>">
                    <button class="button button--primary button--icon entity-terms__tags--form-addBtn" type="submit">
                        <iconify icon="gridicons:plus"></iconify>
                    </button>
                    <!-- <button class="entity-terms__popover--button"><iconify icon="ic:baseline-search"></iconify></button> -->
                </form>
                <ul class="entity-terms__tags--list" v-if="filteredTerms.length > 0">
                    <li class="entity-terms__tags--list-item" @click="addTerm(term, popover)" v-for="term in filteredTerms">
                        <span v-html="highlightedTerm(term)"></span>
                    </li>
                </ul>
            </div>
        </template>

        <!-- Modo Área de Atuação -->
        <template v-if="!allowInsert" #default="popover">
            <div class="entity-terms__area">
                <input type="text" v-model="filter" class="entity-terms__area--input" placeholder="<?= i::__('Filtro') ?>">
                <ul v-if="terms.length > 0" class="entity-terms__area--list">
                    <li v-for="term in filteredTerms">
                        <label class="entity-terms__area--list-item">
                            <input type="checkbox" 
                                :checked="entityTerms.indexOf(term) >= 0"
                                @change="toggleTerm(term)"
                                class="input" > 
                            <span class="text" v-html="highlightedTerm(term)"></span>
                        </label>
                    </li>
                </ul>

                <button class="button button--solid button--solid-dark" @click="popover.toggle()">
                    <?php i::_e('Confirmar')?>
                </button>
            </div>
        </template>
    </popover>

    <ul class="entity-terms__terms">
        <li class="button--solid entity-terms__terms--term" v-for="term in entityTerms"> 
            {{term}}
            <iconify v-if="editable" @click="remove(term)" icon="gg:close"></iconify>
        </li>
    </ul>
</div>