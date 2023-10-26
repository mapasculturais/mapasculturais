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

    <mc-tag-list v-if="entity.registrationCategories.length" classes="primary__background" :tags="entity.registrationCategories" editable></mc-tag-list>

    <div class="opportunity-category-list__add-category">
        <div class="field">
            <input v-model="category" type="text" name="AddNewCategory" />
        </div>

        <button @click="addCategory()" class="button button--primary button--icon button--primary-outline">
            <mc-icon name="add"></mc-icon><label><?php i::_e("Adicionar") ?></label>
        </button>
    </div>

    <!-- <mc-popover class="popover-tag" openside="down-right" title="<?php i::esc_attr_e('Adicionar categoria') ?>">
        <template #button="popover">
            <button @click="popover.toggle()" class="button button--primary button--icon button--primary-outline">
                <mc-icon name="add"></mc-icon><label><?php i::_e("Adicionar") ?></label>
            </button>
        </template>

        <template #default="{close}">
            <form @submit="addCategory(); $event.preventDefault(); close();" class="entity-links__newLink">
                <div class="grid-12">
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Descrição da categoria') ?></label>
                            <input v-model="category" type="text" name="AddNewCategory" />
                        </div>
                    </div>

                    <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </mc-popover> -->
</div>