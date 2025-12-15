<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="registration-print">
    <div class="registration-print__actions">
        <button class="registration-print__button bold" @click="print()">
            <div class="button button--primary button--icon button--sm">
                <mc-icon name="print"></mc-icon> <?= i::__('Imprimir') ?>
            </div>
        </button>
        
        <button class="registration-print__button bold" @click="exportPDF()">
            <div class="button button--primary button--icon button--sm">
                <mc-icon name="download"></mc-icon> <?= i::__('Exportar Ficha Completa (PDF)') ?>
            </div>
        </button>
        
        <button class="registration-print__button bold" @click="downloadZip()">
            <div class="button button--secondary button--icon button--sm">
                <mc-icon name="download"></mc-icon> <?= i::__('Baixar Anexos (ZIP)') ?>
            </div>
        </button>
    </div>

    <iframe ref="printIframe" class="registration-print__printOnly"></iframe>
    <mc-loading class="registration-print__loading" :condition="loading"></mc-loading>
</div>
