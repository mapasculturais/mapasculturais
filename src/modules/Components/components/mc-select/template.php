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
<div :class="['mc-select', {'mc-select--sm' : small }, {'mc-select--disabled' : disabled}]" :id="uniqueID">
    <VDropdown :triggers="[]" :shown="open" :autoHide="false" @apply-show="focus()" popperClass="mc-select__popper" ref="dropdown" eager-mount :positioning-disabled="$media('max-width: 500px')">
        <div ref="selected" :class="['mc-select__selected-option', {'mc-select__selected-option--open' : open }]" @click="toggleSelect();">
        </div>

        <template #popper ref="popperr">
            <div class="mc-select__dropdown">
                <div v-if="showFilter" class="mc-select__filter" ref="filter">
                    <input class="mc-select__filter-input" v-model="filter" type="text" placeholder="<?= i::esc_attr__('Filtro') ?>" @input="filterOptions()" />
                    <span class="mc-select__close" @click="closeSelect()">
                        <mc-icon name="close"></mc-icon>
                    </span>
                </div>

                <div ref="options" class="mc-select__options" :class="[{'mc-select__options--groups' : hasGroups}]" @click="selectOption($event)" :id="uniqueID">
                    <slot>
                        <div v-for="option in selectOptions" :class="option.classes" :value="option.value"> {{option.label}} </div>
                    </slot>
                </div>
            </div>
        </template>
    </VDropdown>
</div>