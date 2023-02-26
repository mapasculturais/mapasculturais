<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>

<div class="registration-actions">
    <div class="registration-actions__primary">
        <div v-if="Object.keys(registration.__validationErrors).length > 0" class="errors">
            <span class="errors__title"> <?= i::__('Ops! Alguns erros foram identificados.') ?> </span>
            <span class="errors__subtitle"> <?= i::__('Para continuar, corrija os campos com os erros listados abaixo:') ?> </span>
    
            <div v-for="(error, index) in registration.__validationErrors" class="errors__error">
                <div class="errors__error--text">
                    <strong>{{fieldName(index)}}:</strong> <p v-for="text in error">{{text}}</p>
                </div>    
            </div>
        </div>
        <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button>
    </div>
    <div class="registration-actions__secondary">
        <button class="button button--large button--primary-outline" @click="save()"> <?= i::__('Salvar para depois') ?> </button>
        <button class="button button--large button--primary-outline" @click="exit()"> <?= i::__('Sair') ?> </button>
    </div>
</div>