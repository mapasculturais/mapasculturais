<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon 
');
?>
<div class="search-filter" :class="'search-filter--'+position">
    <div class="search-filter__actions">
        <form class="search-filter__actions-form" @submit.prevent>
            <input v-model="pseudoQuery['@keyword']" type="text" class="search-filter__actions-input" />
            <button class="search-filter__actions-button button--icon">
                <mc-icon name="search"></mc-icon>
            </button>
        </form>
        <button @click="toggleFilter()" class="search-filter__actions-form-btn button button--primary button--icon">
            <mc-icon name="filter"></mc-icon>
            <?= i::__('Filtrar'); ?>
        </button>
    </div>

    <div :class="['search-filter__filter', {'show': showMenu}]">
        <div class="search-filter__filter-content">
            <a href="#main-app" class="search-filter__filter-close button button--icon" @click="toggleFilter()"><?= i::_e('Fechar') ?> <mc-icon name="close"></mc-icon></a>
            <slot><?= i::__('Filtrar'); ?></slot>
        </div>
    </div>
</div>