<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de eventos') ?>
        </label>
        <div class="field">
            <label><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Linguagens') ?></label>
            <mc-multiselect :model="pseudoQuery['term:linguagem']" :items="terms" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['term:linguagem'].filter" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as linguagens') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['term:linguagem']" classes="event__background event__color"></mc-tag-list>
        </div>
    </form>
</search-filter>