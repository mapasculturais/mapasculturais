<?php 
use MapasCulturais\i;
?>

<div class="entity-gallery">

    <h2> {{title}} </h2>

    <div class="entity-gallery__list">   

        <div class="entity-gallery__list--image" v-for="(img, index) in images">
            <div @click="open" class="entity-gallery__list--image-img" >
                <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
            </div>

            <div v-if="editable" class="entity-gallery__list--image-actions">
                <a> <iconify icon="zondicons:edit-pencil"></iconify> </a>
                <a> <iconify icon="ooui:trash"></iconify> </a>
            </div>
        </div>

        <div v-if="editable" class="entity-gallery__list--addNew">

            <modal title="Adicionar uma nova imagem" class="create-modal" button-label="Adicionar uma nova imagem" >
                <template #default>

                    <image-uploader :entity="entity" group="gallery" :circular="false">
                        <template #default="modal">

                            teste

                        </template>
                    </image-uploader>

                </template>
                <template #button="modal">
                    <a @click="modal.open()" class="button button--primary button--icon button--primary-outline">
                        <iconify icon="gridicons:plus"></iconify>
                        <?php i::_e("Adicionar")?>
                    </a>
                </template>
                <template #actions="modal">
                    <div class="create-modal__buttons">
                        <button class="button button--primary" @click="createPublic(modal)"><?php i::_e('Adicionar')?></button>
                        <button class="button button--text button--text-del " @click="cancel(modal)"><?php i::_e('Cancelar')?></button>
                    </div>
                </template>
            </modal>

            
        </div>

    </div>

    <div class="entity-gallery__full" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>

        <div class="entity-gallery__full--image">
            <img v-if="actualImg" :src="actualImg?.url" :imgId="actualImg?.id" :title="actualImg?.description"/>
            <iconify icon="eos-icons:bubble-loading" />
            <div class="description">{{actualImg?.description}}</div>
        </div>

        <div class="entity-gallery__full--buttons">
            <div @click="prev" class="btnPrev"> <iconify icon="ooui:previous-ltr" /> </div>
            <div @click="next" class="btnNext"> <iconify icon="ooui:previous-rtl" /> </div>
        </div>
    </div>

</div>



