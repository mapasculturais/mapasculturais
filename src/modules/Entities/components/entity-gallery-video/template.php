<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-icon
    mc-popover
')
?>
<?php $this->applyTemplateHook('entity-gallery-video','before'); ?>
<div :class="classes" v-if="editable || entity.metalists?.videos" class="entity-gallery">
    <?php $this->applyTemplateHook('entity-gallery-video','begin'); ?>
    <label class="entity-gallery__title"> {{title}} </label>

    <div v-if="entity.metalists?.videos" class="entity-gallery__list">   
        <div v-for="(metalist, index) in videos" class="entity-gallery__list--video">
            <div>
                <div @click="openVideo(index); open()" class="entity-gallery__list--video-img">
                    <img :src="metalist.video.thumbnail" />
                </div>                
                <p @click="openVideo(index); open()" class="entity-gallery__list--video-label"> {{metalist.title}} </p>
            </div>
            <div v-if="editable" class="entity-gallery__list--video-actions">                
                <mc-popover  openside="down-right">
                    <template #button="popover">
                        <a @click="metalist.newData = {...metalist}; popover.toggle()"> <mc-icon name="edit"></mc-icon> </a>
                    </template>
                    <template #default="popover">
                        <form @submit="save(metalist, popover); $event.preventDefault()" class="entity-related-agents__addNew--newGroup">
                            <div class="grid-12">
                                <div class="col-12">
                                    <div class="field">
                                        <label><?php i::_e('Título do vídeo') ?></label>
                                        <input v-model="metalist.newData.title" type="text" />
                                    </div>
                                </div>                                
                                <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                                <button class="col-6 button button--primary" type="submit" > <?php i::_e("Confirmar") ?> </button>
                            </div>
                        </form>
                    </template>
                </mc-popover>
                <mc-confirm-button @confirm="metalist.delete()">
                    <template #button="{open}">
                        <a @click="open()"> <mc-icon name="trash"></mc-icon> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja remover este vídeo?') ?>
                    </template> 
                </mc-confirm-button>
            </div>
        </div>
    </div>

    <div v-if="editable" title="<?php i::_e('Adicionar Vídeo')?>" class="entity-gallery__addNew">
        <mc-popover v-if="editable" title="<?php i::_e('Adicionar Vídeo')?>" openside="right-up">
            <template #button="popover">
                <slot name="button"> 
                    <a @click="popover.toggle()" class="button button--primary button--icon button--primary-outline">
                        <mc-icon name="add"></mc-icon>
                        <?php i::_e("Adicionar vídeo")?>
                    </a>
                </slot>
            </template>
            <template #default="popover">
                <form @submit="create(popover); $event.preventDefault();">
                    <div class="grid-12">
                        <div class="col-12">
                            <div class="field">
                                <label><?php i::_e('URL do vídeo') ?></label>
                                <input v-model="metalist.value" class="newVideo" type="url" name="newVideo" />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="field">
                                <label><?php i::_e('Título do vídeo') ?></label>
                                <input v-model="metalist.title" class="newVideoDesc" type="text" name="newVideoDesc" />
                            </div>
                        </div>
                        <button class="col-6 button button--text" type="reset" @click="popover.close()"> <?php i::_e("Cancelar") ?> </button>
                        <button class="col-6 button button--primary" type="submit"> <?php i::_e("Confirmar") ?> </button>
                    </div>                    
                </form>
            </template>        
        </mc-popover>
    </div>
    <div class="entity-gallery__full" v-if="entity.metalists?.videos" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>
        <div class="entity-gallery__full--video">
            <mc-icon name="loading"></mc-icon>
            <iframe v-if="actualVideo?.video?.provider == 'youtube'" :src="'https://www.youtube.com/embed/'+actualVideo.video?.videoID" height="500"></iframe>
            <iframe v-if="actualVideo?.video?.provider == 'vimeo'" :src="'https://player.vimeo.com/video/'+actualVideo.video?.videoID" height="500" frameborder="0" allow="fullscreen" allowfullscreen></iframe>
            <p v-if="!actualVideo?.video?.provider == 'youtube'"> <?php i::_e("Sem vimeo por enquanto.")?> </p>
            <div class="description">{{actualVideo?.title}}</div>
            <div @click="prev" class="btnPrev"> <mc-icon name="previous"></mc-icon> </div>
            <div @click="next" class="btnNext"> <mc-icon name="next"></mc-icon> </div>
            <div @click="close" class="btnClose"> <mc-icon name="close"></mc-icon> </div>            
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-gallery-video','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-gallery-video','after'); ?>