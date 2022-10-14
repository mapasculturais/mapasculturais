<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form">
        <label class="form__label">
            <?= i::_e('Filtros de projeto') ?>
        </label>

        <div class="field">
            <label> <?php i::_e('Status do projeto') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Projetos oficiais') ?> </label>
        </div>  

        <div class="field">
            <label> <?php i::_e('Tipos de projetos') ?></label>

            <select placeholder="<? i::_e('Selecione as áreas')?>"> <!-- v-model="pseudoQuery['term:area']" -->
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option v-for="term in terms" :key="term"> {{term}} </option>
            </select>
        </div>
    </form>
</search-filter>