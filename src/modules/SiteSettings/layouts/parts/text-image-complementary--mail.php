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

<div class="imageMail">
    <oc-dialog>
        <template #content>
            <?= i::__('Aqui você pode configurar a imagem que estará presente nos e-mails transacionais do Mapas Culturais. Esses e-mails são enviados quando o usuário cria uma conta, solicita a redefinição de senha ou recebe notificações internas. A imagem deve ter as dimensões de <span class="color-red fbold">1200x480</span> e manter a proporção de 5:2') ?>
        </template>
    </oc-dialog>

    <oc-upload :entity="entity" prop="mail-image" dir="assets/img" imageFinalName="mail-image" :imageSize="[1200,480]"></oc-upload>
</div>