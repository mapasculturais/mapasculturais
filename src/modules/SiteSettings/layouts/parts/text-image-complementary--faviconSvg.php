<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-dialog
    oc-upload
')
?>

<div class="faviconSVG">
    <oc-dialog>
        <template #content>
            <?= i::__('Aqui é possível configurar um dos favicons do ambiente. O favicon deve estar no formato SVG e possuir as dimensões de <span class="color-red fbold">180x180</span>, mantendo a proporção de 1:1') ?>
        </template>
    </oc-dialog>

    <oc-upload :entity="entity" prop="favicon-svg" dir="assets/img/home" :imageSize="[180,180]"></oc-upload>
</div>