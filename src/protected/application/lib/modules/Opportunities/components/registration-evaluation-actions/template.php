<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('v1-embed-tool')
?>
<div class="registration-evaluation-actions__buttons" v-if="evaluationRegistrationList">
    <div class="grid-12">
        <div class="col-12" v-if="showActions(registration, 'finishEvaluation')">
            <button class="button button--primary" @click="finishEvaluation()"> <?= i::__('Finalizar avaliação') ?> </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'save')">
            <button class="button button--primary" @click="save()"> <?= i::__('Salvar e continuar depois') ?> </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'send')">
            <button class="button button--primary" @click="send(registration)"> <?= i::__('Enviar avaliação') ?> </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'reopen')">
            <button class="button button--primary" @click="reopen(registration)"> <?= i::__('Reabrir avaliação') ?> </button>
        </div>
        <div class="col-6">
            <button v-if="firstRegistration?.registrationid != registration.id" class="button button--primary-outline" @click="previous()"> <?= i::__('Anterior') ?> </button>
        </div>
        <div class="col-6">
            <button v-if="lastRegistration?.registrationid != registration.id" class="button button--primary-outline" @click="next()"> <?= i::__('Próximo') ?> </button>
        </div>
        <div class="col-12">
            <button class="button" disabled @click="send()"> <?= i::__('Enviar avaliação') ?> </button>
        </div>
        <div class="col-12">
            <button class="button button--primary-outline" @click="exit()"> <?= i::__('Sair') ?> </button>
        </div>
    </div>
</div>