<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
');
?>

<div class="support-actions grid-12">
    <button @click="save();" class="button button--large button--primary button--md col-12">
        <?= i::__("Salvar alterações") ?>
    </button>

    <button @click="exit()" class="button button--large button--primary-outline button--md col-12">
        <?= i::__("Sair") ?>
    </button>
</div>