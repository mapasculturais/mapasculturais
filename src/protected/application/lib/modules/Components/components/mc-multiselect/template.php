<?php
use MapasCulturais\i;
$this->import('popover');
?>
<div class="mc-multiselect">
    <popover :openside="openside" @close="close()" @open="open()">
        <template #button="popover">
            <slot :popover="popover"></slot>
        </template>

        <template #default="popover">
            <div class="mc-multiselect__content">

                <div class="mc-multiselect__content-form">
                    <input v-if="!hideFilter" type="text" v-model="model.filter" class="input" placeholder="<?= i::__('Filtro') ?>">
                </div>

                <ul v-if="items.length > 0" class="mc-multiselect__content-list">
                    <li v-for="item in filteredItems">
                        <label class="item">
                            <input type="checkbox" :checked="model.indexOf(item) >= 0" @change="toggleItem(item, popover)" class="input">
                            <span class="text" v-html="highlightedItem(item)"></span>
                        </label>
                    </li>
                </ul>
                
                <div class="mc-multiselect__content-button">
                    <button v-if="!hideButton" class="button button--primary" @click="popover.toggle()">
                        <?php i::_e('Confirmar') ?>
                    </button>
                </div>
            </div>
        </template>
    </popover>
</div>