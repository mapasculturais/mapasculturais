<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-modal
');
?>

<div class="evaluation-actions" v-if="evaluationRegistrationList">
    <div class="grid-12">
        <div v-if="currentEvaluation && currentEvaluation.status" class="col-12">
            {{currentEvaluation.status}}
        </div>
        <div class="col-12" v-if="showActions('finishEvaluation')">
            <button class="button button--primary button--large" @click="finishEvaluation()">
                <?= i::__('Finalizar avaliação') ?>
            </button>
        </div>

        <div class="col-12" v-if="showActions('finishEvaluation')">
            <mc-modal button-label="Finalizar e Avançar" title="<?= i::__('Avaliação feita!') ?>">
                <template #default>
                    <div class="finish-send-evaluation__text">
                        <span class="finish-send-evaluation__span"><?= i::__('Agora é necessário enviar essa avaliação para a pessoa gestora. Você pode enviar uma por uma ou todas de uma só vez.') ?></span>
                    </div>
                </template>

                <template #actions="modal">
                    <button class="button button--text button--text-del" @click="finishEvaluationSendLater(); modal.close()"><?= i::__('Enviar Depois') ?></button>
                    <button class="button button--primary" @click="finishEvaluationSend(); modal.close()"><?= i::__('Enviar agora') ?></button>
                </template>
        
                <template #button="modal">
                    <button class="button button--primary button--icon button--large" @click="modal.open()">
                        <span v-if="lastRegistration?.registrationid != entity.id"><?= i::__('Finalizar e avançar') ?></span>
                        <span v-if="lastRegistration?.registrationid == entity.id"><?= i::__('Finalizar e enviar') ?></span>
                        <mc-icon name="arrow-right-ios"></mc-icon>
                    </button>
                </template>
            </mc-modal>
        </div>

        <div class="col-12" v-if="showActions('reopen')">
            <button class="button button--primary button--large button--large" @click="reopen()"> <?= i::__('Reabrir avaliação') ?> </button>
        </div>

        <div class="col-12" v-if="showActions('send')">
            <button class="button button--primary button--icon button--large evaluation-actions__buttons__send" @click="finishEvaluationSend()">
                <?= i::__('Enviar avaliação') ?>
                <mc-icon name="send"></mc-icon>
            </button>
        </div>
        
        <div class="col-12" v-if="showActions('save')">
            <button class="button button--primary button--large evaluation-actions__buttons__saveAfter" @click="saveEvaluation()">
                <?php i::_e('Salvar e continuar depois') ?>
            </button>
        </div>

        <div class="col-6">
            <button v-if="firstRegistration?.registrationid != entity.id" class="button button--primary-outline button--icon button--large" @click="previous()">
                <mc-icon name="arrow-left-ios"></mc-icon>
                <?= i::__('Anterior') ?>
            </button>
        </div>
        
        <div class="col-6">
            <button v-if="lastRegistration?.registrationid != entity.id" class="button button--primary-outline button--icon button--large" @click="next()">
                <?= i::__('Próximo') ?>
                <mc-icon name="arrow-right-ios"></mc-icon>
            </button>
        </div>
    </div>
</div>