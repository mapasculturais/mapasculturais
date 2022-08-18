<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :api="api" :position="position" :query="query">
    <form>
        <div class="field">
            <label><input v-model="query['@verified']" type="checkbox"> <?php i::_e('Somente entidades verificadas') ?> </label>
        </div>  

        <div class="field">
            <label> <?php i::_e('Tipo') ?>
                <select v-model="query['type']">
                    <option :value="undefined"> <? i::_e('Todos')?> </option>
                    <option value="1"> <?php i::_e('Agente Individual') ?> </option>
                    <option value="2"> <?php i::_e('Agente Coletivo') ?> </option>
                </select>
            </label>
        </div>

        <div class="field">
            <label> <?php i::_e('Área de atuação') ?>
                <select v-model="query['term:area']" placeholder="<? i::_e('Selecione as áreas')?>">
                    <option :value="undefined"> <? i::_e('Todos')?> </option>
                    <option v-for="term in terms" :key="term"> {{term}} </option>
                </select>
            </label>
        </div>
    </form>
</search-filter>