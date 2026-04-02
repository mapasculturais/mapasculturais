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

<div class="faviconShare">
    <oc-dialog>
        <template #content>
            <?= i::__('Aqui você pode configurar a imagem de compartilhamento que será exibida ao compartilhar o link do Mapas Culturais nas redes sociais, como WhatsApp, Instagram, Facebook, entre outras. A imagem deve ter o tamanho de <span class="color-red fbold">1200x630</span> e manter a proporção de 40:21.') ?>
        </template>
    </oc-dialog>

    <oc-upload :entity="entity" prop="share-image" dir="assets/img/home" :imageSize="[1200,630]"></oc-upload>
</div>