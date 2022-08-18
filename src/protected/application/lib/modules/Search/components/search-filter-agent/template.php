<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :api="api" :position="position">
    <form>
        <div class="field">
            <label for="EntityState"> Status da Entidade </label> 
            <label><input id="EntityState" type="checkbox"> Entidades oficiais </label>
        </div>  

        <div class="field">
            <label for="EntityType"> Tipo de Entidade </label>
            <select id="EntityType" name="Entity-type">
                <option value="" disabled selected> <? i::_e('Selecione o tipo')?> </option>
                <option value="1"> Agente Individual </option>
                <option value="2"> Agente Coletivo </option>
            </select>
        </div>

        <div class="field">
            <label for="entityArea"> Área de Atuação </label>
            <select id="entityArea" name="entity-area">
                <option value="" disabled selected> <? i::_e('Selecione as áreas')?> </option>
                <option value="1"> Danca </option>
                <option value="2"> Musica </option>
            </select>
        </div>
    </form>
</search-filter>