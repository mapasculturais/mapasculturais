<?php
use MapasCulturais\i;

$this->import('image-uploader');
?>

<div class="profileImg">
    <image-uploader :entity="entity" group="avatar" :aspect-ratio="1" :circular="true">
        <template #default="modal">
            <div class="profileImg__img">
                <iconify icon="bi:image-fill" />
                <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarMedium?.url" class="select-profileImg__img--img" />
            </div>
            <label> <?php i::_e("Selecionar imagem de perfil"); ?> </label>
        </template>
    </image-uploader>
</div>