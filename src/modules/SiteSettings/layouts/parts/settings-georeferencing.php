<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-dialog
    oc-georeferencing
 ')
?>

<div class="settings-georeferencing">
    <oc-dialog>
        <template #content>
            <?= i::__('Configure aqui as opções de georreferenciamento da plataforma, como níveis de zoom, latitude e longitude iniciais.') ?>
            <?= i::__('Ajuste também as definições do mapa exibido na página inicial e na tela de buscas, que mostra as localizações dos agentes cadastrados.') ?>
        </template>
    </oc-dialog>
    <oc-georeferencing :entity="entity"></oc-georeferencing>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>