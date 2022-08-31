<?php
use MapasCulturais\i;
$this->import('popover');
?>
<div class="mc-multiselect">
    <popover openside="down-right" @close="close()" @open="open()">
        <template #button="popover">
            <slot :popover="popover"></slot>
        </template>

        <template #default="popover">
            <div class="entity-terms__area">
                <input v-if="!hideFilter" type="text" v-model="model.filter" class="entity-terms__area--input" placeholder="<?= i::__('Filtro') ?>">

                <ul v-if="items.length > 0" class="entity-terms__area--list">
                    <li v-for="item in filteredItems">
                        <label class="entity-terms__area--list-item">
                            <input type="checkbox" :checked="model.indexOf(item) >= 0" @change="toggleItem(item, popover)" class="input">
                            <span class="text" v-html="highlightedItem(item)"></span>
                        </label>
                    </li>
                </ul>

                <button v-if="!hideButton" class="button button--solid button--solid-dark" @click="popover.toggle()">
                    <?php i::_e('Confirmar') ?>
                </button>
            </div>
        </template>
    </popover>
</div>