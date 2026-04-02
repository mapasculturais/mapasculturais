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

<div class="gird-12">
    <entity-field :entity="entity" prop="entityProjectDescription" classes="col-12" :maxLength="600"></entity-field>
</div>

<div class="btn-entity-actions__complement">
    <oc-actions :entity="entity" editable></oc-actions>
</div>