<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form">
        <label class="form__label">
            <?= i::_e('Filtros de eventos') ?>
        </label>

        <div class="field">
            <label><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>

        <div class="field">
            <label> <?php i::_e('Linguagens') ?></label>

            <select v-model="pseudoQuery['term:linguagem']" placeholder="<? i::_e('Selecione as linguagens')?>">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option v-for="term in terms" :key="term"> {{term}} </option>
            </select>
        </div>
    </form>
</search-filter>