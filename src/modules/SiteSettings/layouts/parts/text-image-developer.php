<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-text-image
    oc-upload
    mc-image-uploader
    mc-alert
    entity-field
');
?>

<div class="text-image-feature w100">
    <div>
        <oc-dialog>
            <template #content>
                <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
                <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
            </template>
        </oc-dialog>

        <div class="grid-12">
            <entity-field :entity="entity" prop="developerTitle" classes="col-12" :maxLength="65"></entity-field>
            <entity-field :entity="entity" prop="developerDescription" classes="col-12" :maxLength="250"></entity-field>
        </div>
    </div>

    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>