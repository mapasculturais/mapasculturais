<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
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
            <mc-multiselect :model="pseudoQuery['type']" :items="types" title="<?= i::esc_attr__('Selecione os tipos: ') ?>" hide-filter hide-button>
                <template #default="{popover, setFilter, filter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="project__background project__color"></mc-tag-list>
        </div>
        <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
        <?php $this->applyTemplateHook('search-filter-project', 'end') ?>
    </form>
</search-filter>