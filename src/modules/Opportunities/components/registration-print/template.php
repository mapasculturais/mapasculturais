<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="registration-print">
    <div class="registration-print__actions">
        <button class="button button--primary button--icon" @click="print()">
            <mc-icon name="print"></mc-icon> <?= i::__('Imprimir') ?>
        </button>
        
        <button class="button button--primary button--icon" @click="exportPDF()">
            <mc-icon name="download"></mc-icon> <?= i::__('Exportar PDF') ?>
        </button>
        
        <button class="button button--primary button--icon" @click="downloadZip()">
            <mc-icon name="archive"></mc-icon> <?= i::__('Baixar Anexos (ZIP)') ?>
        </button>
    </div>

    <iframe ref="printIframe" class="registration-print__printOnly"></iframe>
    <mc-loading class="registration-print__loading" :condition="loading"></mc-loading>
</div>
