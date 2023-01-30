<?php
use MapasCulturais\i;
$this->import('search-filter');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form">
        <label class="form__label">
            <?= i::_e('Filtros de projeto') ?>
        </label>

        <div class="field">
            <label> <?php i::_e('Status do projeto') ?> </label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Projetos oficiais') ?> </label>
        </div>  

        <div class="field">
            <label> <?php i::_e('Tipos de projetos') ?></label>

            <mc-multiselect :model="pseudoQuery['type']" :items="types" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['type'].filter" @focus="popover.toggle()" placeholder="<?= i::esc_attr__('Selecione os tipos: ') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['type']" :labels="types" classes="opportunity__background opportunity__color"></mc-tag-list>
        </div>
    </form>
</search-filter>