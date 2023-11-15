<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-popover
');
?>
<div class="mc-multiselect">
    <mc-popover :title="title">
        <template #button="popover">
            <slot :popover="popover" :setFilter="setFilter"></slot>
        </template>
        <template #default="{close}">
            <div class="mc-multiselect__content">
                <div class="mc-multiselect__content-form">
                    <input v-if="!hideFilter" type="text" v-model="filter" class="input" placeholder="<?= i::__('Filtro') ?>">
                </div>
                <ul v-if="items.length > 0 || Object.keys(items).length > 0" class="mc-multiselect__content-list">
                    <li v-for="item in filteredItems">
                        <label class="item">
                            <input type="checkbox" :checked="model.indexOf(item.value) >= 0" @change="toggleItem(item.value)" class="input">
                            <span class="text" v-html="highlightedItem(item.label)"></span>
                        </label>
                    </li>
                </ul>
                <div class="mc-multiselect__content-button">
                    <button v-if="!hideButton" class="button button--primary" @click="close">
                        <?php i::_e('Confirmar') ?>
                    </button>
                </div>
            </div>
        </template>
    </mc-popover>
</div>