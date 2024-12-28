<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-terms
    mc-icon 
    mc-multiselect 
    mc-tag-list
    search-filter 
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <label class="form__label">
        <?= i::_e('Filtros de oportunidades') ?>
    </label>
    <form ref="form" class="form" @submit="$event.preventDefault()">
        <?php $this->applyTemplateHook('search-filter-opportunity', 'begin') ?>
        <div class="field">
            <label> <?php i::_e('Status das oportunidades') ?> </label>
            <label><input @click="openForRegistrations()" ref="open" type="radio" name="registrationType"> <?php i::_e('Inscrições abertas') ?> </label>
            <label><input @click="closedForRegistrations()" ref="closed" type="radio" name="registrationType"> <?php i::_e('Inscrições encerradas') ?> </label>
            <label><input @click="futureRegistrations()" ref="future" type="radio" name="registrationType"> <?php i::_e('Inscrições futuras') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" :true-value="true" :false-value="undefined" type="checkbox"> <?php i::_e('Editais oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Tipo de oportunidade') ?></label>
            <mc-multiselect :model="pseudoQuery['type']" :items="types" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>" hide-filter hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="opportunity__background opportunity__color"></mc-tag-list>

        </div>
        <div class="field">
            <label> <?php i::_e('Área de interesse') ?></label>
            <mc-multiselect :model="pseudoQuery['term:area']" placeholder="<?php i::_e('Selecione as áreas de interesse') ?>" :items="terms" hide-filter hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:area']" classes="agent__background agent__color"></mc-tag-list>
        </div>
        <?php $this->applyTemplateHook('search-filter-opportunity', 'end') ?>
    </form>
    <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
</search-filter>