<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div :class="['registration-card', {'border': hasBorder}, {'picture': pictureCard}]">
    <div class="registration-card__content">    

        <div v-if="pictureCard" class="left">
            <div class="registerImage">
                <img v-if="entity.owner?.files?.avatar" :src="entity.owner?.files?.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon name="picture" v-if="!entity.owner?.files?.avatar"></mc-icon>
            </div>
        </div>

        <div class="right">            
            <div class="header">
                <div v-if="pictureCard" class="title"> <strong>{{entity.owner?.name}}</strong> </div>
                <div v-if="!pictureCard" class="title"> <?= i::__('Número de inscrição:') ?> <strong>{{entity.number}}</strong> </div>
                <div class="actions"></div>
            </div>

            <div class="content">
                <div v-if="pictureCard" class="registerData">
                    <p class="title"> <?= i::__("Inscrição") ?> </p>
                    <p class="data"> {{entity.number}} </p>
                </div>

                <div v-if="!pictureCard" class="registerData">
                    <p class="title"> <?= i::__("Categoria") ?> </p>
                    <p v-if="entity.category" class="data"> {{entity.category}} </p>
                    <p v-if="!entity.category" class="data"> <?= i::__('Sem categoria') ?> </p>
                </div>

                <div class="registerData">
                    <p class="title"> <?= i::__("Data de inscrição") ?> </p>
                    <p class="data"> {{registerDate(entity.createTimestamp)}} <?= i::__("às") ?> {{registerHour(entity.createTimestamp)}} </p>
                </div>

                <div v-if="pictureCard" class="registerData">
                    <p class="title"> <?= i::__("Categoria") ?> </p>
                    <p v-if="entity.category" class="data"> {{entity.category}} </p>
                    <p v-if="!entity.category" class="data"> <?= i::__('Sem categoria') ?> </p>
                </div>

                <div v-if="!pictureCard" class="registerData">
                    <p class="title"> <?= i::__("Agente inscrito") ?> </p>
                    <p class="data"> {{entity.owner?.name}} </p>
                </div>
            </div>
        </div>
    </div>



    <div class="registration-card__footer">
        <div class="left">
            <div class="status">
                {{status}}
            </div>
        </div>
        <div class="right">
            <a class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Acompanhar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
        </div>
    </div>
</div>