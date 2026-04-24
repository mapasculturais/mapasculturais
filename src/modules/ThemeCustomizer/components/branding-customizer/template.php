<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-image-uploader
');

?>

<fieldset class="branding-customizer">
    <legend class="branding-customizer__legend">
        <h3> <?= i::__('Identidade do site') ?> </h3>
    </legend>

    <p class="branding-customizer__hint">
        <?= i::__('Estes valores substituem o nome e a descrição definidos por variáveis de ambiente neste subsite. Deixe em branco para usar o padrão da instalação.') ?>
    </p>

    <div class="branding-customizer__fields grid-12">
        <entity-field :classes="'col-12'" :entity="subsite" prop="site_name" :autosave="300"></entity-field>
        <entity-field :classes="'col-12'" :entity="subsite" prop="site_description" :autosave="300"></entity-field>
    </div>

    <div class="branding-customizer__images grid-12">
        <div class="branding-customizer__image col-12">
            <h4 class="branding-customizer__image-title"> <?= i::__('Imagem de compartilhamento (Open Graph / redes sociais)') ?> </h4>
            <p class="branding-customizer__image-desc"> <?= i::__('Sugestão: proporção próxima de 1200×630 pixels. Substitui as imagens configuradas em sharing.php para este subsite.') ?> </p>
            <mc-image-uploader class="branding-customizer__uploader" :entity="subsite" group="share" :aspect-ratio="1200/630" deleteFile>
                <template #default="modal">
                    <div class="branding-customizer__uploader-wrapper">
                        <div class="branding-customizer__uploader-content">
                            <img v-if="subsite.files.share" :src="subsite.files.share?.url" class="branding-customizer__preview-img" alt="" />
                            <mc-icon v-if="!subsite.files.share" name="image"></mc-icon>
                        </div>
                    </div>
                </template>
            </mc-image-uploader>
        </div>

        <div class="branding-customizer__image col-12">
            <h4 class="branding-customizer__image-title"> <?= i::__('Imagem do cabeçalho dos e-mails') ?> </h4>
            <p class="branding-customizer__image-desc"> <?= i::__('Exibida nos e-mails transacionais deste subsite (ex.: notificações de inscrição em oportunidades).') ?> </p>
            <mc-image-uploader class="branding-customizer__uploader" :entity="subsite" group="mailImage" :aspect-ratio="3/1" deleteFile>
                <template #default="modal">
                    <div class="branding-customizer__uploader-wrapper">
                        <div class="branding-customizer__uploader-content">
                            <img v-if="subsite.files.mailImage" :src="subsite.files.mailImage?.url" class="branding-customizer__preview-img" alt="" />
                            <mc-icon v-if="!subsite.files.mailImage" name="image"></mc-icon>
                        </div>
                    </div>
                </template>
            </mc-image-uploader>
        </div>
    </div>
</fieldset>
