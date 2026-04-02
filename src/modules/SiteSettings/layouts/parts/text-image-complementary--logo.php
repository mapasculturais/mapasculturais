<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
');
?>

<div class="logo">

    <oc-dialog>
        <template #content>
            <?= i::__('Aqui é possível configurar o logotipo do ambiente. Você pode carregar a imagem do seu logotipo com as dimensões de 380x143 (proporção de 382:143)') ?>
            <?= i::__('ou utilizar o logotipo padrão, ajustando o texto e as cores conforme sua preferência.') ?>
        </template>
    </oc-dialog>


    <div class="actions">
        <label for=""> <?= i::__('Defina aqui como deseja utilizar o logotipo do ambiente.') ?></label>
        <entity-field :entity="entity" prop="typeLogoDefinition" hide-label @change="setTypeLogo()" :autosave="500" @save="reload()"></entity-field>
    </div>

    <div class="tabs">
        <div v-if="typeLogoDefinition == 'default'" class="default">
            <?php $this->part('text-image-complementary--logo--defaut') ?>
        </div>
        <div v-if="typeLogoDefinition == 'image'" class="image">
            <mc-alert type="warning">
                <?= i::__('Aqui é possível configurar o logotipo do ambiente. Você pode carregar a imagem do seu logotipo com as dimensões de 380x143 (proporção de 382:143) ou utilizar o logotipo padrão, ajustando o texto e as cores conforme sua preferência.') ?>
            </mc-alert>

            <oc-upload :entity="entity" prop="logo-image" dir="assets/img/home" :imageSize="[800, 320]"></oc-upload>
        </div>
    </div>
</div>