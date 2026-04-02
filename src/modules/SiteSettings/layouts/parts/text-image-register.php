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

<div class="text-image-register w100">

    <oc-text-image :entity="entity" slug="register">
        <template #register-text="{tab, entity}">
            <div>
                <oc-dialog>
                    <template #content>
                        <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
                        <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
                    </template>
                </oc-dialog>

                <div class="grid-12">
                    <entity-field :entity="entity" prop="registerTitle" classes="col-12" :maxLength="65"></entity-field>
                    <entity-field :entity="entity" prop="registerDescription" classes="col-12" :maxLength="300"></entity-field>
                </div>
            </div>

            <div class="btn-entity-actions">
                <oc-actions :entity="entity" editable></oc-actions>
            </div>
        </template>
        <template #register-image="{tab, entity}">
            <div class="upload-area">
                <oc-dialog>
                    <template #content>
                        <?= i::__('Configure aqui os textos e imagens exibidos na página inicial do Mapas. A configuração está organizada por seções, facilitando a identificação de onde cada elemento será exibido.') ?>
                        <?= i::__('Cada seção contém áreas específicas para ajustar o texto e, quando aplicável, a imagem correspondente.') ?>
                    </template>
                </oc-dialog>

                <mc-alert type="warning">
                    <?= i::__('Configure aqui a imagem da seção Cadastre-se na Home. Lembre-se de que a imagem deve ter as dimensões de ') ?><span class="color-red"><?= i::__('1920x386') ?></span><?= i::__(', respeitando a proporção de 4.97:1') ?>
                </mc-alert>

                <oc-upload :entity="entity" prop="home-register" dir="assets/img/home" :imageSize="[1920, 386]"></oc-upload>
            </div>
        </template>
    </oc-text-image>
</div>