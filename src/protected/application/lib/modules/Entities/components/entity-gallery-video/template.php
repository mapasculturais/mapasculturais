<?php
use MapasCulturais\i;
?>
<div class="entity-gallery">

<label class="entity-gallery__title"> {{title}} </label>

<div class="entity-gallery__list">   

    <div class="entity-gallery__list--video"> <!-- v-if="images" v-for="(img, index) in images" -->
        <div @click="open" class="entity-gallery__list--video-img" >
            <div style="width: 100%; height: 100%; background-color: green;"></div>
        </div>

        <p class="entity-gallery__list--video-label"> <?php i::_e('Título original do vídeo embedado')?> </p>

        <div v-if="editable" class="entity-gallery__list--video-actions">
            <a> <iconify icon="zondicons:edit-pencil"></iconify> </a>
            <a> <iconify icon="ooui:trash"></iconify> </a>
        </div>
    </div>

</div>

<div v-if="editable" class="entity-gallery__addNew">
    <popover v-if="editable" openside="down-right">
        <template #button="{ toggle }">
            <slot name="button" :toggle="toggle"> 
                <a class="button button--primary button--icon button--primary-outline" @click="toggle()">
                    <iconify icon="gridicons:plus"></iconify>
                    <?php i::_e("Adicionar")?>
                </a>
            </slot>
        </template>

        <template #default="{ close }">
            <div class="entity-gallery__addNew--video">
                <div class="field">
                    <label><?php i::_e('URL do vídeo') ?></label>
                    <input v-model="newVideo" class="newVideo" type="text" name="newVideo" />
                </div>

                <div class="field">
                    <label><?php i::_e('Descrição do vídeo') ?></label>
                    <input v-model="newVideoDesc" class="newVideoDesc" type="text" name="newVideoDesc" />
                </div>
                
                <div class="actions">
                    <button class="button button--text"  @click="close()"> <?php i::_e("Cancelar") ?> </button>
                    <button @click="addLink(newVideoDesc, newVideo)" class="button button--solid"> <?php i::_e("Confirmar") ?> </button>
                </div>
            </div>
        </template>        
    </popover>
</div>

<!-- <div v-if="editable" class="entity-gallery__addNew">
    <a @click="modal.open()" class="button button--primary button--icon button--primary-outline">
        <iconify icon="gridicons:plus"></iconify>
        <?php i::_e("Adicionar")?>
    </a>          
</div> -->

<!-- <div class="entity-gallery__full" v-if="images" :class="{ 'active': galleryOpen }">
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
</div> -->

</div>