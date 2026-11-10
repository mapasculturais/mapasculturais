<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-image-uploader
    mc-popover 
');
?>
<?php $this->applyTemplateHook('entity-gallery','before'); ?>
<div :class="classes" v-if="editable || images" class="entity-gallery">
    <?php $this->applyTemplateHook('entity-gallery','begin'); ?>
    <label v-if="!hideTitle" class="entity-gallery__title"> {{title}} </label>

    <div v-if="images" class="entity-gallery__list">   
        <div class="entity-gallery__list--image" v-for="(img, index) in images">
            <div class="entity-gallery__list--image-media">
                <div @click="open" class="entity-gallery__list--image-img" >
                    <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
                </div>
                <div v-if="editable" class="entity-gallery__list--image-actions" :class="{ 'entity-gallery__list--image-actions--labeled': labeledActions }">
                    <mc-popover @open="img.newDescription = img.description" openside="down-right">
                        <template #button="popover">
                            <a class="edit__action edit__action--edit" @click="popover.toggle()">
                                <mc-icon name="edit"></mc-icon>
                                <span v-if="labeledActions"><?php i::_e('Editar') ?></span>
                            </a>
                        </template>
                        <template #default="popover">
                            <form v-if="img.newDescription !== undefined" @submit="rename(img, popover); $event.preventDefault()" class="entity-gallery__addNew--newGroup">
                                <div class="grid-12">
                                    <div class="col-12">
                                        <div class="field">
                                            <input v-model="img.newDescription" type="text" placeholder="<?php i::esc_attr_e("Informe a descrição da imagem") ?>"/>
                                        </div>
                                    </div>
                                    <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                                    <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                                </div>
                            </form>
                        </template>
                    </mc-popover>
                    <mc-confirm-button @confirm="img.delete()">
                        <template #button="modal">
                            <a class="edit__action edit__action--delete" @click="modal.open()">
                                <mc-icon name="trash"></mc-icon>
                                <span v-if="labeledActions"><?php i::_e('Excluir') ?></span>
                            </a>
                        </template> 
                        <template #message="message">
                            <?php i::_e('Deseja excluir essa imagem?') ?>
                        </template> 
                    </mc-confirm-button>
                </div>
            </div>
            <p @click="openImg(index); open()" class="entity-gallery__list--image-label"> {{img.description}} </p>
        </div>    
    </div>   

    <div v-if="editable" class="entity-gallery__addNew">
        <mc-image-uploader :useDescription="true" :entity="entity" group="gallery" :circular="false" >
            <template #default='uploader'>
                <slot name="button">
                    <a :class="['button', 'button--icon', buttonPrimary ? 'button--primary' : 'button--primary button--primary-outline', 'edit-1__portfolio-cta']">
                        <mc-icon name="add"></mc-icon>
                        {{ buttonLabel || '<?= i::__("Adicionar imagem") ?>' }}
                    </a>
                </slot>
            </template>
        </mc-image-uploader>
    </div>
    <div class="entity-gallery__full" v-if="images" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>
        <div class="entity-gallery__full--image">
            <img v-if="actualImg" :src="actualImg?.url" :imgId="actualImg?.id" :title="actualImg?.description"/>
            <mc-icon v-if="!actualImg" name="loading"></mc-icon>
            <div class="description">{{actualImg?.description}}</div>
            <div @click="prev" class="btnPrev"> <mc-icon name="previous"></mc-icon> </div>
            <div @click="next" class="btnNext"> <mc-icon name="next"></mc-icon> </div>
            <div @click="close" class="btnClose"> <mc-icon name="close"></mc-icon> </div>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-gallery','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-gallery','after'); ?>



