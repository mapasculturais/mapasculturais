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
    <div v-for="(link, index) in links" :key="index" class="entity-field-links__link">
        <div class="entity-field-links__link-fields grid-12">
            <div class="field col-6 sm:col-12">
                <label> <?php i::_e('Título') ?> </label>
                <input v-if="editable" type="url" v-model="link.title" placeholder="<?php i::esc_attr_e("Título") ?>" />
                <span v-if="!editable">{{link.title}}</span>
            </div>

            <div class="field col-6 sm:col-12">
                <label> <?php i::_e('URL') ?> </label>
                <input v-if="editable" type="url" v-model="link.value" placeholder="https://" />
                <a v-if="!editable" :href="link.value" target="_blank">{{link.value}}</a>
            </div>
        </div>

        <mc-confirm-button v-if="editable" @confirm="removeLink(index)">
            <template #button="{open}">
                <div class="field__trash">
                    <button type="button" class="button button--icon button--sm button--text-danger" @click="open()">
                        <mc-icon class="danger__color" name="trash"></mc-icon>
                        <?= i::__("Remover link") ?>
                    </button>
                </div>
            </template>
            <template #message="message">
                <?= $this->text('confirm-deletion', i::__('Deseja remover o link?')) ?>
            </template>
        </mc-confirm-button>
    </div>

    <button v-if="editable" type="button" class="button button--sm button--primary button--icon" @click="addLink">
        <mc-icon name="add"></mc-icon>
        <?php i::_e('Adicionar novo link') ?>
    </button>
</div>