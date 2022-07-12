<?php
use MapasCulturais\i; 
?>

<div class="entity-cover">
    <image-uploader :entity="entity" group="header" :aspect-ratio="425/96" :circular="false">
        <template #default="modal">
            <div class="entity-cover__cover">                
                <div v-if="entity.files.header" class="entity-cover__cover--img">
                    <img :src="entity.files.header?.transformations?.header?.url" class="img" />
                    <!-- <label class="label"> <?php i::_e("Alterar Imagem de Capa")?> </label> -->
                </div>

                <div v-else class="entity-cover__cover--newImg">
                    <iconify icon="bi:image-fill" />
                    <label class="label"> <?php i::_e("Adicionar Imagem de Capa")?> </label>
                </div>
            </div>
        </template>
    </image-uploader>
</div>