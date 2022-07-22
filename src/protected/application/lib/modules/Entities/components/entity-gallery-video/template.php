<?php
use MapasCulturais\i;
?>
<div class="entity-gallery">

    <label class="entity-gallery__title"> {{title}} </label>

    <div class="entity-gallery__list">   

        <div v-if="entity.metalists?.videos" v-for="(video, index) in videos()" class="entity-gallery__list--video">
            <div class="entity-gallery__list--video-img">
                <img @click="openVideo(index); open()" :src="video.data.thumbnail" />
            </div>

            <p @click="openVideo(index); open()" class="entity-gallery__list--video-label"> {{video.title}} </p>

            <div v-if="editable" class="entity-gallery__list--video-actions">
                <a> <iconify icon="zondicons:edit-pencil"></iconify> </a>

                <confirm-button @confirm="">
                    <template #button="modal">
                        <a @click="modal.open()"> <iconify icon="ooui:trash"></iconify> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja excluir esse vídeo?') ?>
                    </template> 
                </confirm-button>
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
            </template>        
        </popover>
    </div>

    <div class="entity-gallery__full" v-if="entity.metalists?.videos" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>

        <div class="entity-gallery__full--video">
            <iframe v-if="actualVideo.data?.provider == 'youtube'" :src="'http://www.youtube.com/embed/'+actualVideo.data?.videoID" height="565"></iframe>
            <p v-else> <?php i::_e("Sem vimeo por enquanto.")?> </p>
            <div class="description">{{actualVideo.title}}</div>
        </div>

        <div class="entity-gallery__full--buttons">
            <div @click="prev" class="btnPrev"> <iconify icon="ooui:previous-ltr" /> </div>
            <div @click="next" class="btnNext"> <iconify icon="ooui:previous-rtl" /> </div>
        </div>
    </div>

</div>