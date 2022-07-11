<?php
use MapasCulturais\i;

$this->import('image-uploader');
?>

<div class="entity-profile">
    <image-uploader :entity="entity" group="avatar" :aspect-ratio="1" :circular="true">
        <template #default="modal">
            <div class="entity-profile__profile">
                <div class="entity-profile__profile--img">
                    <iconify icon="bi:image-fill" />
                    <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarMedium?.url" class="select-profileImg__img--img" />
                </div>
                <label class="entity-profile__profile--label"> <?php i::_e("Selecionar imagem de perfil"); ?> </label>
            </div>
        </template>
    </image-uploader>
</div>