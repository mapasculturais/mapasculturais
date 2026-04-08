<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    oc-dialog
 ');

?>

<div class="settings-site-name">
    <oc-dialog>
        <template #content>
            <?= i::__('Defina o nome público do site. Ele aparece em títulos de página, e-mails e outros pontos da plataforma.') ?>
            <?= i::__('Se deixar em branco, continua valendo o nome definido no arquivo de configuração ou na variável de ambiente SITE_NAME.') ?>
        </template>
    </oc-dialog>
    <div class="grid-12">
        <entity-field :entity="entity" prop="siteName" classes="col-12"></entity-field>
    </div>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>
