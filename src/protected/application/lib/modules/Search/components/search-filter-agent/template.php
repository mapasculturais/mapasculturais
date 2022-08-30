<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form">
        <label class="form__label">
            <?= i::_e('Filtros de agente') ?>
        </label>

        <div class="field">
            <label> <?php i::_e('Status do agente') ?> </label>
            <label><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Agentes oficiais') ?> </label>
        </div>  

        <div class="field">
            <label> <?php i::_e('Tipo') ?></label>

            <select v-model="pseudoQuery['type']">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option value="1"> <?php i::_e('Agente Individual') ?> </option>
                <option value="2"> <?php i::_e('Agente Coletivo') ?> </option>
            </select>
        </div>

        <div class="field">
            <label> <?php i::_e('Área de atuação') ?></label>

            <mc-multiselect :model="pseudoQuery['term:area']" :items="terms"></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']"></mc-tag-list>
            <select v-model="pseudoQuery['term:area']" placeholder="<? i::_e('Selecione as áreas')?>">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option v-for="term in terms" :key="term"> {{term}} </option>
            </select>
        </div>
    </form>
</search-filter>