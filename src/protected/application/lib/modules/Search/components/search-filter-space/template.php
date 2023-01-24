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
            <label> <input v-on:click="acessibilidade($event)" type="checkbox"> <?php i::_e('Possui acessibilidade') ?> </label>
            <label class="verified"> <input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Espaços oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipo') ?> </label>
            <select v-model="pseudoQuery['type']">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option value="1"> <?php i::_e('Agente Individual') ?> </option>
                <option value="2"> <?php i::_e('Agente Coletivo') ?> </option>
            </select>
        </div>
        <div class="field">
            <label> <?php i::_e('Área de atuação') ?> </label>
            <mc-multiselect :model="pseudoQuery['term:area']" :items="terms" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['term:area'].filter" @focus="popover.toggle()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']" classes="space__background space__color"></mc-tag-list>
        </div>
        <a @click="clearFilters()">Limpar todos os filtros</a><mc-icon  name="close"></mc-icon>
    </form>
</search-filter>