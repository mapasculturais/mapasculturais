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
    oc-reset-default-values
');
?>

<div class="text-image-banner">

    <oc-text-image :entity="entity" slug="banner">
        <template #banner-text="{tab, entity}">
            <div>
                <oc-dialog>
                    <template #content>
                        <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
                        <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
                    </template>
                </oc-dialog>

                <div class="grid-12">
                    <entity-field :entity="entity" prop="bannerTitle" classes="col-12" :maxLength="30"></entity-field>
                    <entity-field :entity="entity" prop="bannerDescription" classes="col-12" :maxLength="600"></entity-field>
                </div>
            </div>

            <div class="btn-entity-actions">
                <oc-actions :entity="entity" editable></oc-actions>
            </div>
        </template>
        <template #banner-image="{tab, entity}">
            <div class="upload-area">
                <oc-dialog>
                    <template #content>
                        <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
                        <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
                    </template>
                </oc-dialog>

                <mc-alert type="warning">
                    <?= i::__('Configure aqui a imagem do banner na Home. Lembre-se de que o banner deve ter as dimensões de ') ?><span class="color-red"><?= i::__('1170x390') ?></span><?= i::__(', respeitando a proporção de 3:1') ?>
                </mc-alert>

                <oc-upload :entity="entity" prop="home-header" dir="assets/img/home" :imageSize="[1170, 390]"></oc-upload>
            </div>
        </template>
    </oc-text-image>
</div>