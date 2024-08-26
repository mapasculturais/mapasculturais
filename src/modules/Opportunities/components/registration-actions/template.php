<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-icon
');
?>
<div class="registration-actions">
    <div class="registration-actions__primary">

        <div class="registration-actions__errors">
            <span class="registration-actions__errors-title"> Ops! Alguns erros foram identificados. </span>
            <span class="registration-actions__errors-subtitle"> 
                Para continuar, corrija os campos com os erros listados abaixo: 
                
                <span v-if="hideErrors" class="registration-actions__errors-toggle" @click="toggleErrors()">
                    <?= i::__('Exibir erros') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                </span>
                <span  v-if="!hideErrors" class="registration-actions__errors-toggle" @click="toggleErrors()">
                    <?= i::__('Ocultar erros') ?> <mc-icon name="arrowPoint-up"></mc-icon>
                </span>
            </span>
         
            <div class="registration-actions__errors-list scrollbar" :class="{'registration-actions__errors-list--hide' : hideErrors}">
                <div class="registration-actions__error">
                    <strong>texto:</strong> <p>O campo é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>oto texto:</strong> <p>O campo é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>mais oto texto:</strong> <p>O campo é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>email:</strong> <p>O campo é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>número:</strong> <p>O campo é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>Instituição responsável:</strong> <p>O agente "Instituição responsável" é obrigatório.</p>
                </div>
                <div class="registration-actions__error">
                    <strong>Agente coletivo:</strong> <p>O agente "Coletivo" é obrigatório.</p>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(registration.__validationErrors).length > 0" class="registration-actions__errors">
            <span class="registration-actions__errors-title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="registration-actions__errors-subtitle"> <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?> </span>

            <div class="registration-actions__errors-list scrollbar">
                <div v-for="(error, index) in registration.__validationErrors" class="registration-actions__error">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>
            </div>
        </div>


        <!-- <div v-if="Object.keys(registration.__validationErrors).length > 0" class="errors">
            <span class="errors__title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="errors__subtitle"> <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?> </span>
    
            <div v-for="(error, index) in registration.__validationErrors" class="errors__error">
                <div class="errors__error--text">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>    
            </div>
        </div> -->

        <mc-confirm-button @confirm="send()" yes="<?= i::esc_attr__('Enviar agora') ?>" no="<?= i::esc_attr__('Cancelar') ?>" title="<?= i::esc_attr__('Quer enviar sua inscrição?') ?>">
            <template #button="modal">
                <button @click="modal.open()" class="button button--large button--xbg button--primary">
                    <?= i::__("Enviar") ?>
                </button>
            </template> 
            <template #message="message">
                <?php i::_e('Ao enviar sua inscrição você já estará participando da oportunidade.') ?>
            </template>
        </mc-confirm-button> 

        <!-- <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button> -->
    </div>
    <div class="registration-actions__secondary">
        <button class="button button--large button--primary-outline" @click="validate()"> <?= i::__('Validar inscrição') ?> </button>
        <button @click="save();" class="button button--large button--primary-outline">
            <?= i::__("Salvar") ?>
        </button>
 
        <button @click="exit()" class="button button--large button--primary-outline">
            <?= i::__("Salvar e sair") ?>
        </button>
    </div>
</div>