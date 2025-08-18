<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-icon
    mc-alert
');

$entity = $this->controller->requestedEntity;
$term_url = $app->createUrl('site', 'termoAdesao');
?>

<div class="registration-actions">
    <div class="registration-actions__primary">
        <div v-if="hasErrors" class="registration-actions__errors">
            <span class="registration-actions__errors-title"> <?= i::__('Ops! Encontramos erros no preenchimento da inscrição') ?> </span>
            <span  class="registration-actions__errors-subtitle">
                <?= i::__('Corrija os campos listados antes de enviar o formulário') ?>
            </span>

            <div class="registration-actions__errors-list scrollbar" :class="{'registration-actions__errors-list--hide' : hideErrors}">
                <template v-for="(errors, stepIndex) in sortedValidationErrors" :key="stepIndex">
                    <div class="registration-actions__errors-step" v-if="Object.keys(errors).length > 0">
                        <div class="registration-actions__errors-step-name">{{stepName(stepIndex)}}</div>
                        <div v-for="(error, key) in errors" class="registration-actions__error" tabindex="0" @click="goToField(stepIndex, key)" @keydown.enter="goToField(stepIndex, key)">
                            <strong>{{fieldName(key)}}</strong> <p v-for="text in error">{{text}}</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="registration-actions__validation">
            <template v-if="!isValidated">
                <div v-if="!registration.opportunity.registrationTo.isPast() && registration.status == 0" class="registration-actions__alert">
                    <div  class="registration-actions__alert-header">
                        <mc-icon name="exclamation"></mc-icon>
                        <span class="bold"><?= i::__('Atenção aos campos obrigatórios') ?></span>
                    </div>
                    <div class="registration-actions__alert-content">
                        <span><?= i::__("Só é possível enviar a inscrição após o preenchimento de todos os campos obrigatórios") ?></span>
                    </div>
                </div>

                 <div v-if="!canSeeAction() && registration.opportunity.registrationTo.isPast()" class="registration-actions__alert">
                    <div class="registration-actions__alert-content">
                        <span><?= i::__("O período para envio desta inscrição terminou em") ?> <strong>{{registration.opportunity.registrationTo.date('numeric year')}} <?= i::__("às") ?> {{registration.opportunity.registrationTo.time('2-digit')}}</strong></span>
                    </div>
                </div>

                <div v-if="registration.currentUserPermissions.sendEditableFields" class="registration-actions__alert">
                    <div class="registration-actions__alert-content">
                        <span><?= i::__("O prazo para editar as informações termina em") ?> <strong>{{formatEditableUntil}}</strong></span>
                    </div>
                </div>
            </template>
            <mc-loading v-if="isLastStep" :entity="registration"></mc-loading>
            <mc-confirm-button 
                v-if="isLastStep && !registration.__processing && canSeeAction()" 
                :title="confirmButtonTitle"
                yes="<?= i::esc_attr__('Enviar agora') ?>" 
                no="<?= i::esc_attr__('Cancelar') ?>" 
                @confirm="send($event)"
                dont-close-on-confirm
                :loading="registration.__processing"
            >
                <template #button="modal">
                    <button @click="modal.open()" class="button button--large button--xbg button--primary button--icon registration-actions__send">
                        <?= i::__("Enviar formulário") ?>
                        <mc-icon name="send"></mc-icon>
                    </button>
                </template>
                <template v-if="registration.opportunity.isAppealPhase" #message="message">
                    <?php i::_e('Ao enviá-lo você poderá acompanhá-lo clicando em Minhas Inscrições.') ?>
                </template>
                <template v-else #message="message">
                    <?php i::_e('Ao enviar sua inscrição você já estará participando da oportunidade.') ?>
                </template>
            </mc-confirm-button>
        </div>
    </div>

    <div v-if="stepIndex < steps.length - 1 || stepIndex > 0" class="registration-actions__secondary">
        <button @click="nextStep()" class="button button--bg button--large button--primary  button--icon" v-if="stepIndex < steps.length - 1">
            <?= i::__("Próxima etapa") ?>
            <mc-icon name="arrow-right"></mc-icon>
        </button>

        <button @click="previousStep()" class="button button--md button--large button--primary-outline button--icon" v-if="stepIndex > 0">
            <mc-icon name="arrow-left"></mc-icon>
            <?= i::__("Etapa anterior") ?>
        </button>
    </div>
    
    <mc-loading v-if="!isLastStep" :entity="registration"></mc-loading>

    <div v-show="!registration.__processing" class="registration-actions__save-buttons">
        <button v-if="canSeeAction()" @click="save()" class="button button--sm button--large button--primary">
            <?= i::__("Salvar") ?>
        </button>

        
        <mc-modal classes="rcv-exit-form-modal" button-label="label do botão" title="<?= i::__('Falta tão pouco! Quer mesmo sair?') ?>">
            <template #default="modal">
                <div class="rcv-exit-form-modal modal-options grid-12">
                    <p class="col-12"><?= i::__('Sua inscrição foi salva em rascunho. Para retornar, vá ao <b><u>Painel de Controle</u></b>, acesse 
                        <b><u>Minhas Inscrições</u></b> e encontre a inscrição na aba 
                        <b>Não enviadas</b>.') ?>
                    </p>
                </div>

            </template>

            <template #button="modal">
                <button v-if="canSeeAction()" @click="saveAndExit(modal)" class="button button--sm button--large button--primary">
                    <?= i::__("Salvar e Sair") ?>
                </button>

                <button v-else class="button button--md button--primary" @click="exit(modal)">
                    <?= i::__('Sair') ?>
                </button>
            </template>


            <template #actions="modal">
                <button class="button-cancel button button--md button--primary" @click="modal.close()">
                    <?= i::__('Cancelar') ?>
                </button>

                <button class="button button--md button--primary" @click="exit(modal)">
                    <?= i::__('Sair') ?>
                </button>
            </template>
        </mc-modal>
    </div>
</div>