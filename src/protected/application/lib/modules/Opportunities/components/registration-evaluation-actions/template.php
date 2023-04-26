<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('v1-embed-tool')
?>

<div class="registration-evaluation-actions">
    <div class="registration-evaluation-actions__primary">
        <div v-if="Object.keys(registration.__validationErrors).length > 0" class="errors">
            <span class="errors__title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="errors__subtitle"> <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?> </span>
    
            <div v-for="(error, index) in registration.__validationErrors" class="errors__error">
                <div class="errors__error--text">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>    
            </div>
        </div>
    </div>

    <div class="registration-evaluation-actions__buttons">
        <div class="grid-12">
            <div class="col-12">
                <button class="button button--primary" @click="finishEvaluation()"> <?= i::__('Finalizar avaliação') ?> </button>
            </div>
            <div class="col-12">
                <button class="button button--primary" @click="saveAndContinue()"> <?= i::__('Salvar e continuar depois') ?> </button>
            </div>
            <div class="col-6">
                <button class="button button--primary-outline" @click="previous()"> <?= i::__('Anterior') ?> </button>
            </div>
            <div class="col-6">
                <button class="button button--primary-outline" @click="next()"> <?= i::__('Próximo') ?> </button>
            </div>
            <div class="col-12">
                <button class="button" disabled @click="send()"> <?= i::__('Enviar avaliação') ?> </button>
            </div>
            <div class="col-12">
                <button class="button button--primary-outline" @click="exit()"> <?= i::__('Sair') ?> </button>
            </div>
        </div>

    </div>
</div>