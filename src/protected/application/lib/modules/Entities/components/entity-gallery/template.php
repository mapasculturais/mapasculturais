<?php 
use MapasCulturais\i;

$this->import('confirm-button popover modal');
?>

<div class="entity-gallery">

    <label class="entity-gallery__title"> {{title}} </label>

    <div class="entity-gallery__list" v-if="images">   

        <div class="entity-gallery__list--image" v-for="(img, index) in images">
            <div @click="open" class="entity-gallery__list--image-img" >
                <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
            </div>

            <div v-if="editable" class="entity-gallery__list--image-actions">
                <popover @open="img.newDescription = img.description" openside="down-right">
                    <template #button="{ toggle }">
                        <a @click="toggle()"> <iconify icon="zondicons:edit-pencil"></iconify> </a>
                    </template>
                    <template #default="popover">
                        <form @submit="rename(img, popover); $event.preventDefault()" class="entity-related-agents__addNew--newGroup">
                            <div class="field">
                                <input v-model="img.newDescription" type="text" placeholder="<?php i::esc_attr_e("Informe a descrição da imagem") ?>"/>
                            </div>
                            
                            <div class="actions">
                                <a class="button button--text"  @click="popover.close()"> <?php i::_e("Cancelar") ?> </a>
                                <button class="button button--primary"> <?php i::_e("Confirmar") ?> </button>
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
        <modal title="Adicionar uma nova imagem" button-label="Adicionar uma nova imagem" >
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
                    <?php i::_e("Adicionar imagem")?>
                </a>
            </template>
            <template #actions="modal">
                <button class="button button--primary"><?php i::_e('Adicionar')?></button> <!-- @click="createPublic(modal)" -->
                <button class="button button--text button--text-del"><?php i::_e('Cancelar')?></button> <!--  @click="cancel(modal)" -->
            </template>
        </modal>            
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



