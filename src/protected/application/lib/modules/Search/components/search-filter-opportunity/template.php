<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery" >
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de oportunidades') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Status das oportunidades') ?> </label>
            
            <label><input @click="openForRegistrations()" type="radio" name="registrationType"> <?php i::_e('Inscrições abertas') ?> </label>
            <label><input @click="closedForRegistrations()" type="radio" name="registrationType"> <?php i::_e('Inscrições encerradas') ?> </label>
            <label><input @click="futureRegistrations()" type="radio" name="registrationType"> <?php i::_e('Inscrições futuras') ?> </label>

            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Editais oficiais') ?> </label>
        </div>  
        <div class="field">
            <label> <?php i::_e('Tipo de oportunidade') ?></label>

            <mc-multiselect :model="selectedTypes" :items="types" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['type']" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as áreas') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="selectedTypes" classes="opportunity__background opportunity__color"></mc-tag-list>

        </div>
    </form>
</search-filter>