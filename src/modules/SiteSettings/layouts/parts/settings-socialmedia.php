<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-socialmedia
');
?>

<div class="settings-socialmedia">
    <oc-dialog>
        <template #content>
            <?= i::__('Configure aqui as redes sociais em que o Mapas Culturais está presente. Após a configuração, os ícones serão exibidos no rodapé.') ?>
        </template>
    </oc-dialog>
    <oc-socialmedia :entity="entity"></oc-socialmedia>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>