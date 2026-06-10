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
    <label class="entity-gallery__title"> {{title}} </label>

    <div v-if="images" class="entity-gallery__list">   
        <div class="entity-gallery__list__image" v-for="(img, index) in images">
            <div>
                <div @click="open" class="entity-gallery__list__image-img" >
                    <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
                </div>    
                <p @click="openImg(index); open()" class="entity-gallery__list__image-label"> {{img.description}} </p>
            </div>
            <div v-if="editable" class="entity-gallery__list__image-actions">
                <mc-popover @open="img.newDescription = img.description" openside="down-right">
                    <template #button="popover">
                        <a @click="popover.toggle()"> <mc-icon name="edit"></mc-icon> </a>
                    </template>
                    <template #default="{popover, close}">
                        <form @submit="rename(img, popover); $event.preventDefault()" class="entity-gallery__addNew__newGroup">
                            <div class="grid-12">
                                <div class="col-12">
                                    <div class="field">
                                        <input v-model="img.newDescription" type="text" placeholder="<?php i::esc_attr_e("Informe a descrição da imagem") ?>"/>
                                    </div>
                                </div>
                                <button class="col-6 button button--text" type="reset" @click="close"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit" @click="close"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
                <mc-confirm-button @confirm="img.delete()">
                    <template #button="modal">
                        <a @click="modal.open()"> <mc-icon name="trash"></mc-icon> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja excluir essa imagem?') ?>
                    </template> 
                </mc-confirm-button>
            </div>
        </div>    
    </div>   

    <div v-if="editable" class="entity-gallery__addNew">
        <mc-image-uploader :useDescription="true" :entity="entity" group="gallery" :circular="false" >
            <template #default='uploader'>
                <a class="button button--primary button--icon button--primary-outline">
                    <mc-icon name="add"></mc-icon>
                    <?php i::_e("Adicionar imagem")?>
                </a>
            </template>
        </mc-image-uploader>
    </div>
    <div class="entity-gallery__full" v-if="images" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full__overlay"> </div>
        <div class="entity-gallery__full__image">
            <img v-if="actualImg" :src="actualImg?.url" :imgId="actualImg?.id" :title="actualImg?.description"/>
            <mc-icon v-if="!actualImg" name="loading"></mc-icon>
            <div class="entity-gallery__full__image-description">{{actualImg?.description}}</div>
            <div @click="prev" class="entity-gallery__btn-prev"> <mc-icon name="previous"></mc-icon> </div>
            <div @click="next" class="entity-gallery__btn-next"> <mc-icon name="next"></mc-icon> </div>
            <div @click="close" class="entity-gallery__btn-close"> <mc-icon name="close"></mc-icon> </div>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-gallery','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-gallery','after'); ?>



