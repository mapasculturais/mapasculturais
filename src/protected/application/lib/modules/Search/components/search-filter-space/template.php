<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">

    <form class="form">

        <label class="form__label">
            <?= i::_e('Filtros dos espaços') ?>
        </label>

        <div class="field">
            <label> <?php i::_e('Status do espaço') ?> </label>
            <label> <input v-model="pseudoQuery['acessibilidade']" type="checkbox"> <?php i::_e('Possui acessibilidade') ?> </label>
            <label> <input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Espaços oficiais') ?> </label>
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

            <select v-model="pseudoQuery['term:area']" placeholder="<? i::_e('Selecione as áreas')?>">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option v-for="term in terms" :key="term"> {{term}} </option>
            </select>
        </div>

    </form>

</search-filter>