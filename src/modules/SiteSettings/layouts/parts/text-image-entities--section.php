<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>

<oc-dialog>
    <template #content>
        <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
        <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
    </template>
</oc-dialog>

<div class="gird-12 form-style">
    <entity-field :entity="entity" prop="entitiesTitle" classes="col-12" :maxLength="65"></entity-field>
    <entity-field :entity="entity" prop="entitiesDescription" classes="col-12" :maxLength="250"></entity-field>
</div>