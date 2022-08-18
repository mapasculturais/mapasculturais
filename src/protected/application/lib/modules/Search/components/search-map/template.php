<?php
use MapasCulturais\i;
$this->import('mc-map');
?>

<div class="search-map">
    <entities :type="type" :select="select" :limit="limit">
        <template #header="{entities}">
            <div class="search-map__filter">
                <div class="search-map__filter--filter">

                    <slot name="filter"></slot>

                </div>
            </div>
        </template>

        <template  #default="{entities}">
            <mc-map :entities="entities"></mc-map>
        </template>
    </entities>

<!-- <div class="search-map">

    <div class="search-map__filter">
        <div class="search-map__filter--filter">
            <search-filter position="map">
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
        </div>
    </div>   

    <mc-map :entities="entities"></mc-map>
</div> -->