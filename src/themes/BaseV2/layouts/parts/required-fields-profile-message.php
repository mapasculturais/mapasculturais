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

<div class="required-fields-profile-message">
    <mc-alert type="warning">
        <p><?= i::__("Por favor, preencha todos os campos obrigatórios.") ?></p>
    </mc-alert>
</div>
