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
                <?php if($app->config['app.registrationCardFields']['number']): ?>
                    <div v-if="!pictureCard" class="title"><?= $this->text('my-records-numbers', i::__('Número de inscrição:')) ?> <strong>{{entity.number}}</strong> </div>
                    <div v-if="!pictureCard && entity.opportunity" class="title"> <strong>{{entity.opportunity?.name}}</strong> </div>
                <?php endif ?>
                <div class="actions"></div>
            </div>

            <div class="content">
                <?php if($app->config['app.registrationCardFields']['number']): ?>
                    <div v-if="pictureCard" class="registerData">
                        <p class="title"> <?= $this->text('registration-number-label',i::__('Inscrição')) ?></p>
                        <p class="data"> {{entity.number}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['category']): ?>
                    <div v-if="!pictureCard && entity.category" class="registerData">
                        <p class="title"> <?= $this->text('category-label',i::__('Categoria')) ?></p>
                        <p class="data"> {{entity.category}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['createtimestamp']): ?>
                    <div v-if="entity.createTimestamp" class="registerData">
                        <p class="title"> <?= $this->text('create-timestamp-label',i::__('Data de inscrição')) ?></p>
                        <p class="data"> {{registerDate(entity.createTimestamp)}} <?= i::__("às") ?> {{registerHour(entity.createTimestamp)}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['owner']): ?>
                    <div class="registerData">
                        <p class="title"> <?= $this->text('owner-label',i::__('Agente inscrito')) ?></p>
                        <p class="data"> {{entity.owner?.name}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['category']): ?>
                    <div v-if="pictureCard && entity.category" class="registerData">
                        <p class="title"> <?= $this->text('category-label',i::__('Categoria')) ?> </p>
                        <p class="data"> {{entity.category}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['range']): ?>
                    <div v-if="entity.range" class="registerData">
                        <p class="title"> <?= $this->text('range-label',i::__('Faixa')) ?> </p>
                        <p class="data"> {{entity.range}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['proponentType']): ?>
                    <div v-if="entity.proponentType" class="registerData">
                        <p class="title"> <?= $this->text('proponentType-label',i::__('Proponente')) ?></p>
                        <p class="data"> {{entity.proponentType}} </p>
                    </div>
                <?php endif ?>

                <?php if($app->config['app.registrationCardFields']['coletive']): ?>
                    <div v-if="entity?.agentRelations?.coletivo" class="registerData">
                        <p class="title"> <?= $this->text('coletive-label',i::__('Nome coletivo')) ?></p>
                        <p class="data"> {{entity?.agentRelations?.coletivo[0].agent.nomeCompleto}} </p>
                    </div>
                <?php endif ?>
            </div>

        </div>
    </div>

    <div class="registration-card__footer">
        <div class="left">
            <?php if($app->config['app.registrationCardFields']['status']): ?>
                <div v-if="!pictureCard" class="registerData">
                    <p class="title"> <?= $this->text('status-label',i::__('Status')) ?> </p>
                    <div class="status-point">
                        <div class="point"></div><p class="data"> {{status}} </p>
                    </div>
                </div>
            <?php endif ?>
            <slot name="entity-actions-left" :entity="entity"></slot>
        </div>
        <div class="right">
            <slot name="button" :entity="entity">
                <mc-confirm-button v-if="verifyStatus()" @confirm="deleteRegistration(modal)">
                    <template #button="modal">
                        <button  class="button button--md  delete-registration" @click="modal.open();"> <?php i::_e('Excluir') ?>  </button>
                    </template>
                    <template #message="message">
                        <?= $this->text('notification_deletion', i::__('Você realmente deseja excluir a inscrição')) ?> {{entity.number}}<?php i::_e('?') ?>
                    </template>
                </mc-confirm-button>
                <a v-if="!verifyStatus()" class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Acompanhar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                <a v-if="verifyStatus()" class="button button--md button--primary button--icon" :href="entity.singleUrl"> <?= i::__("Continuar") ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
            </slot>
        </div>
    </div>
</div>