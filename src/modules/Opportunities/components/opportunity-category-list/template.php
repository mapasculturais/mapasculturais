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

    <div class="opportunity-category-list__add-category">
        <div class="field">
            <input v-model="category" type="text" name="AddNewCategory" @keyup.enter="addCategory();autoSave()" placeholder="<?= i::__("Escreva aqui a categoria. Ex: Artes visuais") ?>" />
        </div>

        <button @click="addCategory();autoSave()" class="button button--primary button--icon">
            <mc-icon name="add"></mc-icon><label><?php i::_e("Adicionar categoria") ?></label>
        </button>
    </div>

    <mc-tag-list v-if="entity.registrationCategories.length" classes="opportunity__background" @click="autoSave()" :tags="entity.registrationCategories" editable></mc-tag-list>
</div>