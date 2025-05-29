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
?>
<div class="registration-actions">
    <div class="registration-actions__primary">
        <div v-if="Object.keys(registration.__validationErrors).length > 0" class="registration-actions__errors">
            <span class="registration-actions__errors-title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="registration-actions__errors-subtitle"> 
                <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?>
                <span v-if="hideErrors" class="registration-actions__errors-toggle" @click="toggleErrors()">
                    <?= i::__('Exibir erros') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                </span>
                <span  v-if="!hideErrors" class="registration-actions__errors-toggle" @click="toggleErrors()">
                    <?= i::__('Ocultar erros') ?> <mc-icon name="arrowPoint-up"></mc-icon>
                </span>
            </span>

            <div class="registration-actions__errors-list scrollbar" :class="{'registration-actions__errors-list--hide' : hideErrors}">
                <div v-for="(error, index) in registration.__validationErrors" class="registration-actions__error">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>
            </div>
        </div>

        <mc-confirm-button v-if="isValidated" @confirm="send()" yes="<?= i::esc_attr__('Enviar agora') ?>" no="<?= i::esc_attr__('Cancelar') ?>" title="<?= i::esc_attr__('Quer enviar sua inscrição?') ?>">
            <template #button="modal">
                <button v-if="registration.currentUserPermissions.send || registration.currentUserPermissions.sendEditableFields" @click="modal.open()" class="button button--large button--xbg button--primary">
                    <?= i::__("Enviar") ?>
                </button>
            </template> 
            <template #message="message">
                <?php i::_e('Ao enviar sua inscrição você já estará participando da oportunidade.') ?>
            </template>
        </mc-confirm-button> 

        <div>
            <div class="registration-actions__validation" v-if="!isValidated && (registration.currentUserPermissions.modify || registration.currentUserPermissions.sendEditableFields)">
                <mc-alert type="warning">
                    <span><?= i::__("Para enviar sua inscrição, você precisa <strong>validá-la</strong> primeiro. Clique no botão <strong>Validar inscrição</strong> abaixo para verificar se todas as informações estão corretas.") ?></span>
                </mc-alert>
                <button class="button button--large button--primary-outline" @click="validate()"> <?= i::__('Validar inscrição') ?> </button>
            </div>

            <div v-if="registration.opportunity.registrationTo.isPast() && !registration.currentUserPermissions.modify" >
                <mc-alert type="warning">
                    <span><?= i::__("O período para envio desta inscrição terminou em") ?> <strong>{{registration.opportunity.registrationTo.date('numeric year')}} <?= i::__("às") ?> {{registration.opportunity.registrationTo.time('2-digit')}}</strong></span>
                </mc-alert>
            </div>
        </div>
        <!-- <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button> -->
    </div>
    <div class="registration-actions__secondary">
        <button v-if="registration.currentUserPermissions.modify || registration.currentUserPermissions.sendEditableFields" @click="save();" class="button button--large button--primary-outline">
            <?= i::__("Salvar") ?>
        </button>
 
        <button @click="exit()" class="button button--large button--primary-outline">
            <span v-if="!registration.opportunity.registrationTo.isPast() && (registration.currentUserPermissions.modify || registration.currentUserPermissions.sendEditableFields)">
                <?= i::__("Salvar e sair") ?>
            </span>

            <span v-else>
                <?= i::__("Sair") ?>
            </span>
        </button>
    </div>
</div>