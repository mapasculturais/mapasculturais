<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list mc-icon');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de espaço') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status do espaço') ?> </label>
            <label> <input v-model="pseudoQuery['acessibilidade']" true-value="Sim" :false-value="undefined" type="checkbox"> <?php i::_e('Possui acessibilidade') ?> </label>
            <label class="verified"> <input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Espaços oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipos de espaços') ?></label>

            <mc-multiselect :model="pseudoQuery['type']" :items="types" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['type'].filter" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="space__background space__color"></mc-tag-list>
        </div>
        <div class="field">
            <label> <?php i::_e('Área de atuação') ?> </label>
            <mc-multiselect :model="pseudoQuery['term:area']" :items="terms" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['term:area'].filter" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']" classes="space__background space__color"></mc-tag-list>
        </div>
        <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros')?><mc-icon  name="close"></mc-icon></a>
    </form>
</search-filter>