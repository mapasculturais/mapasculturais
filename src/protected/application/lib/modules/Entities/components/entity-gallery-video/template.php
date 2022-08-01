<?php
use MapasCulturais\i;
?>
<div class="entity-gallery">

    <label class="entity-gallery__title"> {{title}} </label>

    <div class="entity-gallery__list">   

        <div v-if="entity.metalists?.videos" v-for="(metalist, index) in videos" class="entity-gallery__list--video">
            <div class="entity-gallery__list--video-img">
                <img @click="openVideo(index); open()" :src="metalist.video.thumbnail" />
            </div>

            <p @click="openVideo(index); open()" class="entity-gallery__list--video-label"> {{metalist.title}} </p>

            <div v-if="editable" class="entity-gallery__list--video-actions">
                <popover @open="metalist.newData = {...metalist}" openside="down-right">
                    <template #button="{ toggle, close }">
                        <a @click="toggle()"> <iconify icon="zondicons:edit-pencil"></iconify> </a>
                    </template>
                    <template #default="{close}">
                        <form @submit="save(metalist).then(close); $event.preventDefault()" class="entity-related-agents__addNew--newGroup">
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="field">
                                        <label><?php i::_e('Título do vídeo') ?></label>
                                        <input v-model="metalist.newData.title" type="text" />
                                    </div>
                                </div>
                            </div>                            

                            <div class="actions">
                                <button class="button button--text" type="reset" @click="close()"> <?php i::_e("Cancelar") ?> </button>
                                <button class="button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </popover>
                
                <confirm-button @confirm="metalist.delete()">
                    <template #button="{open}">
                        <a @click="open()"> <iconify icon="ooui:trash" /> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja remover este vídeo?') ?>
                    </template> 
                </confirm-button>
                
            </div>
        </div>
    </div>

    <div v-if="editable" class="entity-gallery__addNew">
        <popover v-if="editable" openside="right-up">
            <template #button="{ toggle }">
                <slot name="button" :toggle="toggle"> 
                    <a class="button button--primary button--icon button--primary-outline" @click="toggle()">
                        <iconify icon="gridicons:plus"></iconify>
                        <?php i::_e("Adicionar vídeo")?>
                    </a>
                </slot>
            </template>

            <template #default="{ close }">
                <form @submit="create().then(close); $event.preventDefault();">
                    <div class="row">
                        <div class="col-12">
                            <div class="field">
                                <label><?php i::_e('URL do vídeo') ?></label>
                                <input v-model="metalist.value" class="newVideo" type="url" name="newVideo" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="field">
                                <label><?php i::_e('Título do vídeo') ?></label>
                                <input v-model="metalist.title" class="newVideoDesc" type="text" name="newVideoDesc" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button class="button button--text" type="reset" @click="close()"> <?php i::_e("Cancelar") ?> </button>
                        <button class="button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                    </div>
                </form>
            </template>        
        </popover>
    </div>

    <div class="entity-gallery__full" v-if="entity.metalists?.videos" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>

        <div class="entity-gallery__full--video">
            <iframe v-if="actualVideo.video?.provider == 'youtube'" :src="'https://www.youtube.com/embed/'+actualVideo.video?.videoID" height="565"></iframe>
            <p v-else> <?php i::_e("Sem vimeo por enquanto.")?> </p>
            <div class="description">{{actualVideo.title}}</div>
        </div>

        <div class="entity-gallery__full--buttons">
            <div @click="prev" class="btnPrev"> <iconify icon="ooui:previous-ltr" /> </div>
            <div @click="next" class="btnNext"> <iconify icon="ooui:previous-rtl" /> </div>
        </div>
    </div>

</div>