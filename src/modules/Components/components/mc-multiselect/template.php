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
<div :class="['mc-multiselect', {'mc-multiselect--disabled' : disabled}]" :id="uniqueID">
    <VDropdown :triggers="[]" :shown="open" :autoHide="false" popperClass="mc-multiselect__popper" ref="dropdown" eager-mount :positioning-disabled="$media('max-width: 500px')">
        <slot :toggleMultiselect="toggleMultiselect" :setFilter="setFilter">
            <div class="field">
                <input ref="selected" :class="['mc-multiselect__input', {'mc-multiselect__input--open' : open}]" @keyup="setFilter($event.target.value)" @focus="openMultiselect()" :placeholder="placeholder">
            </div>
        </slot>

        <template #popper ref="popperr">
            <div ref="options" :id="uniqueID" class="mc-multiselect__content scrollbar">
                <div v-if="!$media('max-width: 500px') && !hideFilter" class="mc-multiselect__filter" ref="filter">
                    <input class="mc-multiselect__filter-input" v-model="filter" type="text" placeholder="<?= i::esc_attr__('Filtro') ?>" />
                    <span class="mc-multiselect__close" @click="toggleMultiselect()">
                        <mc-icon name="close"></mc-icon>
                    </span>
                </div>

                <div v-if="maxOptions && maxOptions > 0" class="mc-multiselect__count field">
                    <label><?php i::_e('Você selecionou') ?>
                    {{ model.length }}/{{ maxOptions }}
                    <?php i::_e('opções') ?></label>
                </div>

                <ul v-if="items.length > 0 || Object.keys(items).length > 0" class="mc-multiselect__options">
                    <li v-for="item in filteredItems">
                        <label class="mc-multiselect__option">
                            <input type="checkbox" :checked="model.indexOf(item.value) >= 0" @change="toggleItem(item.value)" class="input" :disabled="!canSelectMore && model.indexOf(item.value) === -1">
                            <span class="text" v-html="highlightedItem(item.label)"></span>
                        </label>
                    </li>
                </ul>

                <span v-if="$media('max-width: 500px')" class="mc-multiselect__finish-action" @click="toggleMultiselect()">
                    <?= i::__("Concluir") ?> <mc-icon name="close"></mc-icon>
                </span>
                
                <div v-if="!hideButton" class="mc-multiselect__confirm-button">
                    <button class="button button--primary button--large button--sm" @click="toggleMultiselect()">
                        <?php i::_e('Confirmar') ?>
                    </button>
                </div>
            </div>
        </template>
    </VDropdown>
</div>