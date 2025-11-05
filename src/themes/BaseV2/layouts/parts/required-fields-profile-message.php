<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
');
?>

<div class="container grid-12">
    <mc-alert type="warning" class="col-12">
        <p><?= i::__("Por favor, preencha todos os campos obrigatórios.") ?></p>
    </mc-alert>
</div>