<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-multiselect 
    mc-tag-list 
    search-filter 
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <label class="form__label">
        <?= i::_e('Filtros de agente') ?>
    </label>
    <form class="form" @submit="$event.preventDefault()">
        <?php $this->applyTemplateHook('search-filter-agent', 'begin') ?>
        <div class="field">
            <label> <?php i::_e('Status do agente') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Agentes oficiais') ?> </label>
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
            <mc-multiselect :model="pseudoQuery['term:area']" title="<?php i::_e('Selecione as áreas de atuação') ?>" :items="terms" hide-filter hide-button>
                <template #default="{setFilter, popover}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']" classes="agent__background agent__color"></mc-tag-list>
        </div>
        <?php $this->applyTemplateHook('search-filter-agent', 'end') ?>
    </form>
    <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
</search-filter>