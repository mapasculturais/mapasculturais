<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="faq__accordion" id="accordionRegister">
    <div class="faq-accordion__item">
        <header @click="toggle()" class="faq-accordion__header">
            <h5 class="bold">
                <?= i::__('Como me inscrever em um edital ?') ?>
            </h5>
            <mc-icon :name="status ? 'arrowPoint-up':'arrowPoint-down'" class="primary__color"></mc-icon>
        </header>
        <div v-if="status" class="faq-accordion__content">
            <div class="accordion-body">
                <?= i::__('qualquer conteudo') ?>
            </div>
        </div>
    </div>
</div>