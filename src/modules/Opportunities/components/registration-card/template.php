<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-confirm-button
');
?>
<div :class="['registration-card', {'border': hasBorder}, {'picture': pictureCard}]">
    <div class="registration-card__content">    

        <div v-if="pictureCard" class="left">
            <div class="registerImage">
                <mc-avatar :entity="entity.opportunity" size="small"></mc-avatar>
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

                <div v-if="entity.createTimestamp" class="registerData">
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

                <div v-if="entity.range" class="registerData">
                    <p class="title"> <?= i::__("Faixa") ?> </p>
                    <p class="data"> {{entity.range}} </p>
                </div>

                <div v-if="entity.proponentType" class="registerData">
                    <p class="title"> <?= i::__("Proponente") ?> </p>
                    <p class="data"> {{entity.proponentType}} </p>
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
                <mc-confirm-button v-if="verifyStatus()" @confirm="deleteRegistration(modal)">
                    <template #button="modal">
                        <button  class="button button--md  delete-registration" @click="modal.open();"> <?php i::_e('Excluir') ?>  </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você realmente deseja excluir a inscrição') ?> {{entity.number}}<?php i::_e('?') ?>
                    </template>
                </mc-confirm-button>
                <a v-if="!verifyStatus()" class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Acompanhar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                <a v-if="verifyStatus()" class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Continuar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
            </slot>
        </div>
    </div>
</div>