<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-multiselect
    mc-states-and-cities
    mc-tag-list
    search-filter
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form">
        <?php $this->applyTemplateHook('search-filter-project', 'begin') ?>
        <label class="form__label">
            <?= i::_e('Filtros de projeto') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status do projeto') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Projetos oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipos de projetos') ?></label>
            <mc-multiselect :model="pseudoQuery['type']" :items="types" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>" hide-filter hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="project__background project__color"></mc-tag-list>
        </div>
        <div v-if="statesAndCitiesEnable && (searchTerritorialFilters.showStateFilter || searchTerritorialFilters.showCityFilter)" class="field">
            <label><?php i::_e('Estado e Cidade') ?></label>
            <mc-states-and-cities
                v-model:model-states="pseudoQuery['En_Estado']"
                v-model:model-cities="pseudoQuery['En_Municipio']"
                :hide-states="!searchTerritorialFilters.showStateFilter"
                :hide-cities="!searchTerritorialFilters.showCityFilter"
                :locked-states="searchTerritorialFilters.statesForced">
            </mc-states-and-cities>
        </div>
        <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
        <?php $this->applyTemplateHook('search-filter-project', 'end') ?>
    </form>
</search-filter>