<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-side-menu
    mc-summary-evaluate
    v1-embed-tool
');
?>
<mc-summary-evaluate>
    <mc-side-menu :is-open="open" @toggle="toggle" text-button="<?= i::__("Lista de avaliações") ?>">
        <v1-embed-tool></v1-embed-tool>
    </mc-side-menu>
</mc-summary-evaluate>