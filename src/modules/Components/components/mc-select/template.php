<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div :class="['mc-select', {'mc-select--sm' : small }]" :id="uniqueID">
    <VDropdown :triggers="[]" :shown="open" :autoHide="false" popperClass="mc-select__popper" ref="dropdown" eager-mount :positioning-disabled="$media('max-width: 500px')">
        <div ref="selected" :class="['mc-select__selected-option', {'mc-select__selected-option--open' : open }]" @click="toggleSelect();">
        </div>

        <template #popper ref="popperr">
            <div ref="options" class="mc-select__options" :class="[{'mc-select__options--groups' : hasGroups}]" @click="selectOption($event)" :id="uniqueID">
                <slot>
                    <div v-for="option in selectOptions" :class="option.classes" :value="option.value"> {{option.label}} </div>
                </slot>
            </div>
        </template>
    </VDropdown>
</div>