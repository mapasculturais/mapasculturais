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
        <div class="col-12" v-if="showActions(registration, 'save')">
            <button class="button button--primary button--large registration-evaluation-actions__buttons__saveafter" @click="saveReload()"> <?= i::__('Salvar e continuar depois') ?> </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'finishEvaluation')">
            <button class="button button--primary button--large registration-evaluation-actions__buttons__final" @click="finishEvaluation(registration)">
                <?= i::__('Finalizar avaliação') ?>
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
                    <button class="button button--text button--text-del" @click="saveNext(registration); modal.close()"><?= i::__('Enviar Depois') ?></button>
                    <button class="button button--primary" @click="finishEvaluationNext(registration); modal.close()"><?= i::__('Enviar agora') ?></button>
                </template>
                <template #button="modal">
                    <button class="button button--primary button--icon button--large registration-evaluation-actions__buttons__finalcontinue" @click="modal.open()">
                        <span v-if="lastRegistration?.registrationid != registration.id"><?= i::__('Finalizar e avançar') ?></span>
                        <span v-if="lastRegistration?.registrationid == registration.id"><?= i::__('Finalizar e enviar') ?></span>
                        <mc-icon name="arrow-right-ios"></mc-icon>
                    </button>
                </template>
            </mc-modal>
        </div>

        <div class="col-12" v-if="showActions(registration, 'reopen')">
            <button class="button button--primary button--large button--large registration-evaluation-actions__buttons__reopen" @click="reopen(registration)"> <?= i::__('Reabrir avaliação') ?> </button>
        </div>
        <div class="col-12" v-if="showActions(registration, 'send')">
            <button class="button button--primary button--icon button--large registration-evaluation-actions__buttons__send" @click="send(registration)">
                <?= i::__('Enviar avaliação') ?>
                <mc-icon name="send"></mc-icon>
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