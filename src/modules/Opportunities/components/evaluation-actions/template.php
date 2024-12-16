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

<div class="evaluation-actions" >
    <div class="grid-12">
        <div class="col-12" v-if="showActions('finishEvaluation')">
            <button class="button button--icon button--primary button--large evaluation-actions__buttons__final" @click="finishEvaluation()">
                <mc-icon name="check"></mc-icon>
                <?= i::__('Concluir avaliação') ?>
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
                    <button class="button button--icon button--text button--text-del evaluation-actions__buttons__saveafter" @click="finishEvaluationSendLater(); modal.close()"><?= i::__('Enviar depois') ?></button>
                    <button class="button button--icon button--primary evaluation-actions__buttons__finalcontinue" @click="finishEvaluationSend(); modal.close()"><?= i::__('Enviar agora') ?></button>
                </template>
        
                <template #button="modal">
                    <button class="button button--icon button--primary button--icon button--large evaluation-actions__buttons__finalcontinue" @click="modal.open()">
                        <mc-icon name="send" class="send-icon"></mc-icon>
                        <span v-if="lastRegistration?.registrationid != entity.id"><?= i::__('Enviar avaliação') ?></span>
                        <span v-if="lastRegistration?.registrationid == entity.id"><?= i::__('Finalizar e enviar') ?></span>
                    </button>
                </template>
            </mc-modal>
        </div>

        <div class="col-12" v-if="showActions('reopen')">
            <button class="button button--icon button--primary button--large button--large evaluation-actions__buttons__reopen" @click="reopen()"> <mc-icon name="clock"></mc-icon><?= i::__('Reabrir avaliação') ?> </button>
        </div>

        <div class="col-12" v-if="showActions('send')">
            <button class="button button--icon button--primary button--icon button--large evaluation-actions__buttons__send" @click="finishEvaluationSend()">
                <mc-icon name="send" class="send-icon"></mc-icon>
                <?= i::__('Enviar avaliação') ?>
            </button>
        </div>
        
        <div class="col-12" v-if="showActions('save')">
            <button class="button button--icon button--primary button--large evaluation-actions__buttons__saveafter" @click="saveEvaluation()">
                <mc-icon name="clock"></mc-icon>
                <?php i::_e('Salvar e continuar depois') ?>
            </button>
        </div>

        <div v-if="evaluationRegistrationList" class="col-6">
            <button class="button button--primary-outline button--icon button--large" :class="{'btn disabled' : !buttonActionsActive('firstRegistration')}" @click="previous()">
                <mc-icon name="arrow-left-ios"></mc-icon>
                <?= i::__('Anterior') ?>
            </button>
        </div>
        
        <div v-if="evaluationRegistrationList" class="col-6">
            <button class="button button--primary-outline button--icon button--large" :class="{'btn disabled' : !buttonActionsActive('lastRegistration')}" @click="next()">
                <?= i::__('Próximo') ?>
                <mc-icon name="arrow-right-ios"></mc-icon>
            </button>
        </div>
    </div>
</div>