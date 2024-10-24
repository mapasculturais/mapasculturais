<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button 
    mc-icon
');
?>
<div class="entity-field-links">
    <div v-for="(link, index) in links" :key="index" class="link-item grid-12">
        <label v-if="showTitle" class="col-4">
            <?php i::_e('Título') ?>
            <input type="text" v-model="link.title" placeholder="<?php i::esc_attr_e("Título") ?>"/>
        </label>

        <label class="col-4">
            <?php i::_e('URL') ?>
            <input type="url" v-model="link.value" placeholder="https://" />
        </label>

        <mc-confirm-button @confirm="removeLink(index)">
            <template #button="{open}">
                <div class="field__trash">
                    <mc-icon class="danger__color" name="trash" @click="open()"></mc-icon>
                </div>
            </template>
            <template #message="message">
                <?= $this->text('confirm-deletion', i::__('Deseja remover o link?')) ?>
            </template>
        </mc-confirm-button>
    </div>

    <button type="button" class="button button--primary button--icon" @click="addLink">
        <mc-icon name="add"></mc-icon>
        <?php i::_e('Adicionar') ?>
    </button>
</div>