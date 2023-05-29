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
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de espaço') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status do espaço') ?> </label>
            <label> <input v-model="pseudoQuery['acessibilidade']" true-value="Sim" :false-value="undefined" type="checkbox"> <?php i::_e('Possui acessibilidade') ?> </label>
            <label class="verified"> <input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Espaços oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipos de espaços') ?></label>

            <mc-multiselect :model="pseudoQuery['type']" :items="types" hide-filter hide-button>
                <template #default="{popover, setFilter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)"  @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="space__background space__color"></mc-tag-list>
        </div>
        <div class="field">
            <label> <?php i::_e('Área de atuação') ?> </label>
            <mc-multiselect :model="pseudoQuery['term:area']" :items="terms" hide-filter hide-button>
                <template #default="{popover, setFilter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)"  @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']" classes="space__background space__color"></mc-tag-list>
        </div>
        <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
    </form>
</search-filter>