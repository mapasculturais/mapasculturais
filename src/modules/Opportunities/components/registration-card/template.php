<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div :class="['registration-card', {'border': hasBorder}, {'picture': pictureCard}]">
    <div class="registration-card__content">    

        <div v-if="pictureCard" class="left">
            <div class="registerImage">
                <img v-if="entity.opportunity?.files?.avatar" :src="entity.opportunity?.files?.avatar?.transformations?.avatarMedium?.url" />
                <mc-icon name="image" v-if="!entity.opportunity?.files?.avatar"></mc-icon>
            </div>
        </div>

        <div class="right">            
            <div class="header">
                <div v-if="pictureCard" class="title"> <strong>{{entity.opportunity?.name}}</strong> </div>
                <div v-if="!pictureCard" class="title"> <?= i::__('Número de inscrição:') ?> <strong>{{entity.number}}</strong> </div>
                <div class="actions"></div>
            </div>

            <div class="content">
                <div v-if="pictureCard" class="registerData">
                    <p class="title"> <?= i::__("Inscrição") ?> </p>
                    <p class="data"> {{entity.number}} </p>
                </div>

                <div v-if="!pictureCard && entity.category" class="registerData">
                    <p class="title"> <?= i::__("Categoria") ?> </p>
                    <p class="data"> {{entity.category}} </p>
                </div>

                <div class="registerData">
                    <p class="title"> <?= i::__("Data de inscrição") ?> </p>
                    <p class="data"> {{registerDate(entity.createTimestamp)}} <?= i::__("às") ?> {{registerHour(entity.createTimestamp)}} </p>
                </div>

                <div class="registerData">
                    <p class="title"> <?= i::__("Agente inscrito") ?> </p>
                    <p class="data"> {{entity.owner?.name}} </p>
                </div>

                <div v-if="pictureCard && entity.category" class="registerData">
                    <p class="title"> <?= i::__("Categoria") ?> </p>
                    <p class="data"> {{entity.category}} </p>
                </div>
            </div>
        </div>
    </div>

    <div class="registration-card__footer">
        <div class="left">
            <div v-if="!pictureCard" class="status">
                {{status}}
            </div>
        </div>
        <div class="right">
            <slot name="button" :entity="entity">
                <a class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Acompanhar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
            </slot>
        </div>
    </div>
</div>