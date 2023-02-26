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
        <button class="button button--large button--xbg button--primary" @click="send()"> <?= i::__('Enviar') ?> </button>
    </div>
    <div class="registration-actions__secondary">
        <button class="button button--large button--primary-outline" @click="save()"> <?= i::__('Salvar para depois') ?> </button>
        <button class="button button--large button--primary-outline" @click="exit()"> <?= i::__('Sair') ?> </button>
    </div>
</div>