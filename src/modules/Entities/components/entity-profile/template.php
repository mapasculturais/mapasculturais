<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\DateTime;
use MapasCulturais\i;

$this->import('
    mc-image-uploader
');
?>

<div class="entity-profile" :class="{error: entity.__validationErrors['file:avatar']}">
    <mc-image-uploader :entity="entity" group="avatar" :aspect-ratio="1" :circular="true">
        <template #default="modal">
            <div class="entity-profile__profile">
                <div class="entity-profile__profile--img">
                    <mc-icon v-if="!entity.files.avatar" name="image"></mc-icon>
                    <img v-if="entity.files.avatar" :src="entity.files.avatar?.transformations?.avatarMedium?.url" class="select-profileImg__img--img" />
                </div>
                <label class="entity-profile__profile--label" for="avatar<?= DateTime::date('Ymd') ?>"> <?php i::_e("Adicionar imagem de perfil"); ?> </label>
            </div>
        </template>
    </mc-image-uploader>
    <div v-if="entity.__validationErrors['file:avatar']" class="field__error">
        {{entity.__validationErrors['file:avatar'].join(', ')}}
    </div>
</div>