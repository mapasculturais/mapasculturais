<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon 
    mc-multiselect 
    mc-tag-list
    search-filter 
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form ref="form" class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de oportunidades') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status das oportunidades') ?> </label>
            <label><input @click="openForRegistrations()" ref="open" type="radio" name="registrationType"> <?php i::_e('Inscrições abertas') ?> </label>
            <label><input @click="closedForRegistrations()" ref="closed" type="radio" name="registrationType"> <?php i::_e('Inscrições encerradas') ?> </label>
            <label><input @click="futureRegistrations()" ref="future" type="radio" name="registrationType"> <?php i::_e('Inscrições futuras') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" :true-value="true" :false-value="undefined" type="checkbox"> <?php i::_e('Editais oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Tipo de oportunidade') ?></label>
            <mc-multiselect :model="pseudoQuery['type']" :items="types" hide-filter hide-button>
                <template #default="{popover, setFilter, filter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="opportunity__background opportunity__color"></mc-tag-list>

        </div>
        <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>

    </form>
</search-filter>