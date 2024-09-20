<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import(" 
    mc-modal
    entity-field
");
?>
<div>
    <mc-modal title="<?= i::__('Título do edital') ?>">
        <template #default>
            <div>
                <div class="field">
                    <label><?= i::__('Defina um título para o Edital que deseja criar') ?><span class="required">*</span></label>
                    <input type="text" v-model="formData.name">
                </div><br>
            </div>
        </template>

        <template v-if="!sendSuccess"  #actions="modal">
            <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('cancelar') ?></button>
            <button class="button button--primary" @click="save(modal)"><?= i::__('Começar') ?></button>
        </template>

        <template #button="modal">
            <button type="button" @click="modal.open();" class="button button--primary button--icon"><?= i::__('Usar modelo') ?></button>
        </template>
    </mc-modal>
</div>