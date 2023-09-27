<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="registration-print">
    <button class="registration-print__button bold" @click="print()">
        <mc-icon name="print"></mc-icon> <?= i::__('Imprimir') ?>
    </button>

    <iframe ref="printIframe" class="registration-print__printOnly"></iframe>
    <mc-loading class="registration-print__loading" :condition="loading"></mc-loading>
</div>
