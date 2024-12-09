<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    v1-embed-tool
    mc-icon
    mc-modal
');
?>
<div class="registration-evaluation-actions__buttons" v-if="evaluationRegistrationList">
    <div class="grid-12">
        <div class="col-12" v-if="showActions(registration, 'finishEvaluation')">
            <button class="button button--icon button--primary button--large registration-evaluation-actions__buttons__final" @click="finishEvaluation(registration)">
                <mc-icon name="check"></mc-icon>
                <?= i::__('Concluir avaliação') ?>
            </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'finishEvaluation')">
            <mc-modal button-label="Finalizar e Avançar" title="<?= i::__('Avaliação feita!') ?>">
                <template #default>
                    <div class="finish-send-evaluation__text">
                        <span class="finish-send-evaluation__span"><?= i::__('Agora é necessário enviar essa avaliação para a pessoa gestora.') ?></span>
                        <span><?= i::__('Você pode enviar uma por uma ou todas de uma só vez.') ?></span>
                    </div>
                </template>

                <template #actions="modal">
                    <button class="button button--icon button--text button--text-del registration-evaluation-actions__buttons__saveafter" @click="saveNext(registration); modal.close()"><?= i::__('Enviar Depois') ?></button>
                    <button class="button button--icon button--primary registration-evaluation-actions__buttons__finalcontinue" @click="finishEvaluationNext(registration); modal.close()"><?= i::__('Enviar agora') ?></button>
                </template>
                <template #button="modal">
                    <button class="button button--primary button--icon button--large registration-evaluation-actions__buttons__finalcontinue" @click="modal.open()">
                    <mc-icon name="send" class="send-icon"></mc-icon>
                        <span v-if="lastRegistration?.registrationid != registration.id"><?= i::__('Enviar avaliação') ?></span>
                        <span v-if="lastRegistration?.registrationid == registration.id"><?= i::__('Finalizar e enviar') ?></span>
                        <mc-icon name="arrow-right-ios" class="arrow-icon"></mc-icon>
                    </button>
                </template>
            </mc-modal>
        </div>
        <div class="col-12" v-if="showActions(registration, 'save')">
            <button class="button button--primary button--large registration-evaluation-actions__buttons__saveafter" @click="saveReload()"> <mc-icon name="clock"></mc-icon><?= i::__('Salvar e continuar depois') ?> </button>
        </div>

        <div class="col-12" v-if="showActions(registration, 'reopen')">
            <button class="button button--icon button--primary button--large button--large registration-evaluation-actions__buttons__reopen" @click="reopen(registration)"> 
                <mc-icon name="clock"></mc-icon>
                <?= i::__('Reabrir avaliação') ?> 
            </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'send')">
            <button class="button button--primary button--icon button--large registration-evaluation-actions__buttons__send" @click="send(registration)">
                <mc-icon name="send" class="send-icon"></mc-icon>
                <?= i::__('Enviar avaliação') ?>
            </button>
        </div>

        <div class="col-6">
            <button v-if="firstRegistration?.registrationid != registration.id" class="button button--primary-outline button--icon button--large registration-evaluation-actions__buttons__direction" @click="previous()">
                <mc-icon name="arrow-left-ios"></mc-icon>
                <?= i::__('Anterior') ?>
            </button>
        </div>
        <div class="col-6">
            <button v-if="lastRegistration?.registrationid != registration.id" class="button button--primary-outline button--icon button--large registration-evaluation-actions__buttons__direction" @click="next()">
                <?= i::__('Próximo') ?>
                <mc-icon name="arrow-right-ios"></mc-icon>
            </button>
        </div>
    </div>
</div>