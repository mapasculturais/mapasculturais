<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery" >
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de oportunidades') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status das oportunidades') ?> </label>
            <label><input v-on:click="openForRegistrations($event)" type="checkbox"> <?php i::_e('Inscrições abertas') ?> </label>
            <label><input v-model="pseudoQuery['@']" type="checkbox"> <?php i::_e('Inscrições encerradas') ?> </label>
            <label><input v-model="pseudoQuery['@']" type="checkbox"> <?php i::_e('Inscrições futuras') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Editais oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipo de oportunidade') ?></label>
            <select v-model="pseudoQuery['type']">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option value="1"> <?php i::_e('Agente Individual') ?> </option>
                <option value="2"> <?php i::_e('Agente Coletivo') ?> </option>
            </select>
        </div>
        <div class="field">
            <label> <?php i::_e('Área de interesse') ?></label>

            <select v-model="pseudoQuery['term:area']" placeholder="<? i::_e('Selecione as áreas')?>">
                <option :value="undefined"> <? i::_e('Todos')?> </option>
                <option v-for="term in terms" :key="term"> {{term}} </option>
            </select>
        </div>
    </form>
</search-filter>