<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    mc-icon 
    mc-popover 
    mc-tag-list
');
?>
<div class="opportunity-category-list">
    <h5 class="semibold"><?= i::__("Categorias") ?></h5>

    <mc-tag-list v-if="entity.registrationCategories.length" classes="primary__background" @click="autoSave()" :tags="entity.registrationCategories" editable></mc-tag-list>

    <div class="opportunity-category-list__add-category">
        <div class="field">
            <input v-model="category" type="text" name="AddNewCategory" @keyup.enter="addCategory();autoSave()" />
        </div>

        <button @click="addCategory();autoSave()" class="button button--primary button--icon button--primary-outline">
            <mc-icon name="add"></mc-icon><label><?php i::_e("Adicionar") ?></label>
        </button>
    </div>
</div>