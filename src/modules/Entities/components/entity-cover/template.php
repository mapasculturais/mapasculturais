<?php
use MapasCulturais\i;
?>

<?php $this->applyTemplateHook('entity-cover', 'before'); ?>
<div :class="classes" class="entity-cover">
    <?php $this->applyTemplateHook('entity-cover', 'begin'); ?>
    <image-uploader :entity="entity" group="header" :aspect-ratio="3/1" :circular="false">
        <template #default="modal">
            <div class="entity-cover__cover">
                <div v-if="entity.files.header" id="header<?= date("Ymd") ?>" class="entity-cover__cover--img" :style="{ '--url': 'url('+entity.files.header?.transformations?.header.url+')' }">
                    <label class="label" for="header<?= date("Ymd") ?>"> <?php i::_e("Alterar Imagem de Capa") ?> </label>
                </div>
                <div v-if="!entity.files.header" class="entity-cover__cover--newImg">
                    <mc-icon name="image"></mc-icon>
                    <label class="label"> <?php i::_e("Adicionar Imagem de Capa") ?> </label>
                </div>
            </div>
        </template>
    </image-uploader>
    <?php $this->applyTemplateHook('entity-cover', 'end'); ?>
</div>
<?php $this->applyTemplateHook('entity-cover', 'after'); ?>