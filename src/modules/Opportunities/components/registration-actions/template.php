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
        <div v-if="hasErrors" class="registration-actions__errors">
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

        <mc-confirm-button v-if="canSubmit" @confirm="send()" yes="<?= i::esc_attr__('Enviar agora') ?>" no="<?= i::esc_attr__('Cancelar') ?>" title="<?= i::esc_attr__('Quer enviar sua inscrição?') ?>">
            <template #button="modal">
                <button @click="modal.open()" class="button button--large button--xbg button--primary button--icon">
                    <?= i::__("Enviar formulário") ?>
                    <mc-icon name="send"></mc-icon>
                </button>
            </template>
            <template #message="message">
                <?php i::_e('Ao enviar sua inscrição você já estará participando da oportunidade.') ?>
            </template>
        </mc-confirm-button>

        <div class="registration-actions__validation" v-if="!isValidated">
            <mc-alert type="warning">
                <span><?= i::__("Para enviar sua inscrição, você precisa <strong>validá-la</strong> primeiro. Clique no botão <strong>Validar inscrição</strong> abaixo para verificar se todas as informações estão corretas.") ?></span>
            </mc-alert>
            <button class="button button--large button--primary-outline" @click="validate()" v-if="isLastStep"><?= i::__('Validar inscrição') ?></button>
            <button class="button button--large button--primary-outline" @click="validateStep(step._id)" v-else><?= i::__('Validar etapa') ?></button>
        </div>
        <!-- <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button> -->
    </div>

    <div class="registration-actions__secondary">
        <button @click="nextStep()" class="button button--bg button--large button--secondary button--icon" v-if="stepIndex < steps.length - 1">
            <?= i::__("Próxima etapa") ?>
            <mc-icon name="arrow-right"></mc-icon>
        </button>

        <button @click="previousStep()" class="button button--md button--large button--secondary-outline button--icon" v-if="stepIndex > 0">
            <mc-icon name="arrow-left"></mc-icon>
            <?= i::__("Etapa anterior") ?>
        </button>
    </div>

    <div class="registration-actions__secondary">
        <button @click="save()" class="button button--large button--primary-outline">
            <?= i::__("Salvar") ?>
        </button>

        <button @click="exit()" class="button button--large button--primary-outline">
            <?= i::__("Salvar e sair") ?>
        </button>
    </div>
</div>