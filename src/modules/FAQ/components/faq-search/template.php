<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<form :class="['faq-search', {'faq-search--section' : section}]" @submit.prevent>
    <input v-model="global.faqSearch" @input="search()" type="text" placeholder="<?= i::__('Pesquise por palavra-chave') ?>" class="faq-search__input" />
    <button @click="search()" class="faq-search__button">
        <mc-icon name="search"></mc-icon>
    </button>
</form>