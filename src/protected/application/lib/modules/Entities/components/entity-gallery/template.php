<?php 
use MapasCulturais\i;

$this->import('confirm-button popover modal');
?>

<div class="entity-gallery">

    <label class="entity-gallery__title"> {{title}} </label>

    <div class="entity-gallery__list" v-if="images">   

        <div class="entity-gallery__list--image" v-for="(img, index) in images">
            <div>
                <div @click="open" class="entity-gallery__list--image-img" >
                    <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
                </div>
    
                <p @click="openImg(index); open()" class="entity-gallery__list--image-label"> {{img.description}} </p>
            </div>

            <div v-if="editable" class="entity-gallery__list--image-actions">
                <popover @open="img.newDescription = img.description" openside="down-right">
                    <template #button="{ toggle }">
                        <a @click="toggle()"> <iconify icon="zondicons:edit-pencil"></iconify> </a>
                    </template>
                    <template #default="popover">
                        <form @submit="rename(img, popover); $event.preventDefault()" class="entity-gallery__addNew--newGroup">
                            <div class="row">
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
                </popover>

                <confirm-button @confirm="img.delete()">
                    <template #button="modal">
                        <a @click="modal.open()"> <iconify icon="ooui:trash"></iconify> </a>
                    </template> 
                    <template #message="message">
                        <?php i::_e('Deseja excluir essa imagem?') ?>
                    </template> 
                </confirm-button>
            </div>
        </div>
    
    </div>
    
    <div v-if="editable" class="entity-gallery__addNew">
        <image-uploader :useDescription="true" :entity="entity" group="gallery" :circular="false">
            <template #default='uploader'>
                <a class="button button--primary button--icon button--primary-outline" @click="toggle()">
                    <iconify icon="gridicons:plus"></iconify>
                    <?php i::_e("Adicionar imagem")?>
                </a>
            </template>
        </image-uploader>
    </div>

    <div class="entity-gallery__full" v-if="images" :class="{ 'active': galleryOpen }">
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



