<?php
use MapasCulturais\i;

$this->import('image-uploader');
?>

<div class="entity-profile">
    <image-uploader :entity="entity" group="avatar" :aspect-ratio="1" :circular="true">
        <template #default="modal">
            <div class="entity-profile__profile">
                <div class="entity-profile__profile--img">
                    <mc-icon name="image"></mc-icon>
                    <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarMedium?.url" class="select-profileImg__img--img" />
                </div>
                <label class="entity-profile__profile--label" for="avatar<?= date('Ymd') ?>"> <?php i::_e("Adicionar imagem de perfil"); ?> </label>
            </div>
        </template>
    </image-uploader>
</div>