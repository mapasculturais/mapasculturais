<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('popover mc-tag-list')

?>

<div>
    <p><?= i::__("Categorias") ?></p>

    <mc-tag-list :tags="categories" editable></mc-tag-list>

    <popover openside="down-right" title="<?php i::esc_attr_e('Adicionar categoria')?>">
        <template #button="popover">
            <a @click="popover.toggle()" class="button button--primary button--icon button--primary-outline">
              <?php i::_e("+ Adicionar categoria")?>
            </a>
        </template>

        <template #default="{close}">
            <form @submit="addCategory(); $event.preventDefault(); close(); clear()" class="entity-links__newLink">
                <div class="grid-12">
                    <div class="col-12">
                        <div class="field">
                            <label><?php i::_e('Descrição da categoria') ?></label>
                            <input v-model="category" class="newCategoryAdded" type="text" name="newCategoryAdded" />
                        </div>
                    </div>

                    <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </form>
        </template>
    </popover>
</div>